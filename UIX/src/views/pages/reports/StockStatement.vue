<script setup>
import { ref, onMounted, computed } from 'vue';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const statement = ref([]);
const loading = ref(false);
const startDate = ref(null);
const endDate = ref(null);
const searchKeyWord = ref(null);
const movementType = ref({ label: 'All movements', value: null });

const movementOptions = [
    { label: 'All movements', value: null },
    { label: 'Restocks only', value: 'RESTOCK' },
    { label: 'Sales only', value: 'SALE' }
];

// yyyy-mm-dd from local parts. Not commonService.formatDate (dd/mm/yyyy, which
// PHP's strtotime reads as m/d/Y) and not toISOString (shifts across timezones).
const toDateOnly = (d) => {
    if (!(d instanceof Date)) return d || null;
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

const getStatement = () => {
    loading.value = true;
    let postData = {
        from_date: toDateOnly(startDate.value),
        to_date: toDateOnly(endDate.value),
        searchKey: searchKeyWord.value,
        movementType: movementType.value?.value
    }
    commonService.genericRequest('get-stock-statement', 'post', true, postData).then((response) => {
        loading.value = false;
        if (response.status) {
            statement.value = response.data || [];
        } else {
            commonService.showError(toast, response.message);
        }
    }).catch(() => { loading.value = false; })
}

const resetFilters = () => {
    startDate.value = null;
    endDate.value = null;
    searchKeyWord.value = null;
    movementType.value = movementOptions[0];
    getStatement();
}

const totalIn = computed(() => (statement.value || []).reduce((s, r) => s + (Number(r.in_qty) || 0), 0));
const totalOut = computed(() => (statement.value || []).reduce((s, r) => s + (Number(r.out_qty) || 0), 0));
const totalRestockValue = computed(() =>
    (statement.value || []).filter(r => r.movement_type === 'RESTOCK').reduce((s, r) => s + (Number(r.value) || 0), 0));
const totalSalesValue = computed(() =>
    (statement.value || []).filter(r => r.movement_type === 'SALE').reduce((s, r) => s + (Number(r.value) || 0), 0));
const productCount = computed(() => new Set((statement.value || []).map(r => r.product_id)).size);

// closing balance per product = the balance on that product's last row
const closingBalance = (productId) => {
    const rows = (statement.value || []).filter(r => r.product_id === productId);
    return rows.length ? rows[rows.length - 1].balance : 0;
}

// The ledger only knows movements it recorded. Stock changed by editing a product
// (rather than restocking) never becomes a movement, so flag where the statement
// and the actual stock on hand disagree instead of quietly showing one of them.
const isReconciled = (row) =>
    Math.abs(Number(closingBalance(row.product_id)) - Number(row.stock_on_hand)) < 0.01;

const unreconciledCount = computed(() => {
    const seen = new Map();
    (statement.value || []).forEach(r => seen.set(r.product_id, r));
    return [...seen.values()].filter(r => !isReconciled(r)).length;
});

onMounted(() => {
    getStatement();
});
</script>

<template>
    <div>
        <div class="card">
            <h5>Stock Statement</h5>
            <p class="text-sm mb-3 stmt-note">
                Every restock (in) and sale (out) per product on one timeline, with a running balance.
                Quantities are in each product's base unit. The balance is cumulative over the product's whole
                history, so a date range opens at the correct carried-forward balance.
            </p>
            <Toast />

            <Toolbar class="mb-4">
                <template #start>
                    <span class="p-float-label mr-2">
                        <Calendar id="fromDate" showIcon dateFormat="yy-mm-dd" v-model="startDate"></Calendar>
                        <label for="fromDate">Start Date</label>
                    </span>
                    <span class="p-float-label mr-2">
                        <Calendar id="toDate" showIcon dateFormat="yy-mm-dd" v-model="endDate"></Calendar>
                        <label for="toDate">End Date</label>
                    </span>
                    <span class="p-float-label mr-2">
                        <Dropdown id="movementType" :options="movementOptions" optionLabel="label"
                            v-model="movementType" style="min-width: 12rem" />
                        <label for="movementType">Movement</label>
                    </span>
                    <span class="p-float-label mr-2">
                        <InputText type="text" id="search" v-model="searchKeyWord" @keyup.enter="getStatement" />
                        <label for="search">Product / Receipt No.</label>
                    </span>
                    <Button @click="getStatement" label="SEARCH" icon="pi pi-search" class="mr-2" />
                    <Button @click="resetFilters" label="RESET" icon="pi pi-refresh"
                        class="p-button-outlined p-button-secondary" />
                </template>
            </Toolbar>

            <div class="grid mb-3">
                <div class="col-6 md:col-2"><div class="stmt-tile"><span>Products</span><strong>{{ productCount }}</strong></div></div>
                <div class="col-6 md:col-2"><div class="stmt-tile"><span>Movements</span><strong>{{ statement.length }}</strong></div></div>
                <div class="col-6 md:col-2"><div class="stmt-tile"><span>Total In</span><strong class="in-qty">{{ commonService.commaSeparator(totalIn) }}</strong></div></div>
                <div class="col-6 md:col-2"><div class="stmt-tile"><span>Total Out</span><strong class="out-qty">{{ commonService.commaSeparator(totalOut) }}</strong></div></div>
                <div class="col-6 md:col-2"><div class="stmt-tile"><span>Restock Value</span><strong>{{ commonService.commaSeparator(totalRestockValue) }}</strong></div></div>
                <div class="col-6 md:col-2"><div class="stmt-tile"><span>Sales Value</span><strong>{{ commonService.commaSeparator(totalSalesValue) }}</strong></div></div>
                <div class="col-6 md:col-2" v-if="unreconciledCount"><div class="stmt-tile"><span>Not reconciling</span><strong class="out-qty">{{ unreconciledCount }}</strong></div></div>
            </div>

            <DataTable :value="statement" :loading="loading" :paginator="true" :rows="50"
                :rowsPerPageOptions="[25, 50, 100, 200]" dataKey="movement_type" size="small" stripedRows
                class="p-datatable-gridlines" :rowHover="true" responsiveLayout="scroll"
                rowGroupMode="subheader" groupRowsBy="product_name" sortMode="single" :sortOrder="1"
                sortField="product_name">

                <template #groupheader="{ data }">
                    <span class="font-bold">{{ data.product_name }}</span>
                    <span class="ml-2 text-sm stmt-note">{{ data.product_no }}</span>
                    <span class="ml-3 text-sm">Closing balance:
                        <strong>{{ commonService.commaSeparator(closingBalance(data.product_id)) }} {{ data.unit }}</strong>
                    </span>
                    <span class="ml-3 text-sm stmt-note">Stock on hand:
                        {{ commonService.commaSeparator(data.stock_on_hand) }}
                    </span>
                    <span v-if="!isReconciled(data)" class="ml-2 stmt-tag warn-tag"
                        v-tooltip="'Stock on hand does not match the recorded movements — quantity was changed by editing the product instead of restocking, so it never became a movement.'">
                        does not reconcile
                    </span>
                </template>

                <template #empty>
                    <div class="p-3 text-center">No stock movements for the selected filters.</div>
                </template>

                <Column header="Date" style="min-width: 11rem">
                    <template #body="{ data }">{{ data.tran_date }}</template>
                </Column>
                <Column header="Type" style="min-width: 7rem">
                    <template #body="{ data }">
                        <span :class="data.movement_type === 'RESTOCK' ? 'stmt-tag in-tag' : 'stmt-tag out-tag'">
                            {{ data.movement_type === 'RESTOCK' ? 'Restock' : 'Sale' }}
                        </span>
                    </template>
                </Column>
                <Column header="Reference" style="min-width: 9rem">
                    <template #body="{ data }">{{ data.reference || '—' }}</template>
                </Column>
                <Column header="In" style="min-width: 7rem">
                    <template #body="{ data }">
                        <span v-if="Number(data.in_qty)" class="in-qty">+{{ commonService.commaSeparator(data.in_qty) }}</span>
                    </template>
                </Column>
                <Column header="Out" style="min-width: 7rem">
                    <template #body="{ data }">
                        <span v-if="Number(data.out_qty)" class="out-qty">-{{ commonService.commaSeparator(data.out_qty) }}</span>
                    </template>
                </Column>
                <Column header="Balance" style="min-width: 8rem">
                    <template #body="{ data }">
                        <strong>{{ commonService.commaSeparator(data.balance) }}</strong>
                    </template>
                </Column>
                <Column header="Unit" style="min-width: 6rem">
                    <template #body="{ data }">{{ data.unit }}</template>
                </Column>
                <Column header="Unit Price" style="min-width: 8rem">
                    <template #body="{ data }">{{ commonService.commaSeparator(data.unit_price) }}</template>
                </Column>
                <Column header="Value" style="min-width: 9rem">
                    <template #body="{ data }">{{ commonService.commaSeparator(data.value) }}</template>
                </Column>
                <Column header="Posted By" style="min-width: 10rem">
                    <template #body="{ data }">{{ data.user_name }}</template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<style scoped>
/* --text-color is overridden to #ffffff in this app's themes, so body text on a
   card must use --surface-900 (the inverse-of-card token) to stay readable. */
.stmt-note { color: var(--text-color-secondary); }

.stmt-tile {
    border: 1px solid var(--surface-border);
    border-radius: var(--border-radius);
    padding: 0.6rem 0.8rem;
    background: var(--surface-50);
}
.stmt-tile span { display: block; font-size: 0.75rem; color: var(--text-color-secondary); }
.stmt-tile strong { font-size: 1.15rem; color: var(--surface-900); }

.stmt-tag {
    display: inline-block;
    padding: 0.1rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
}
.in-tag { background: rgba(34, 197, 94, 0.15); color: #15803d; }
.out-tag { background: rgba(239, 68, 68, 0.15); color: #b91c1c; }
.warn-tag { background: rgba(234, 179, 8, 0.18); color: #a16207; }
.in-qty { color: #15803d; font-weight: 600; }
.out-qty { color: #b91c1c; font-weight: 600; }

:deep(.p-datatable) { color: var(--surface-900); }
:deep(.p-rowgroup-header) { background: var(--surface-100) !important; }
:deep(.p-rowgroup-header td) { color: var(--surface-900); }
</style>
