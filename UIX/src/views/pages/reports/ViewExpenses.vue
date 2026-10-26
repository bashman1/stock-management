<script setup>
import { ref, computed, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const expenses = ref([]);
const fromDate = ref(null);
const toDate = ref(null);

const fmtDate = (d) => {
    if (!d) return null;
    const dt = new Date(d);
    return isNaN(dt.getTime()) ? null : commonService.toApiDate(dt);
};

const totalAmount = computed(() => (expenses.value || []).reduce((s, e) => s + (Number(e.amount) || 0), 0));

const getExpenses = () => {
    const postData = { from_date: fmtDate(fromDate.value), to_date: fmtDate(toDate.value) };
    commonService.genericRequest('get-expenses', 'post', true, postData).then((response) => {
        if (response.status) {
            expenses.value = response.data ?? [];
        } else {
            commonService.showError(toast, response.message);
        }
    });
};

const payExpense = (row) => {
    commonService.genericRequest('pay-expense', 'post', true, { id: row.id, payment_method: 'CASH' }).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getExpenses();
        } else {
            commonService.showError(toast, response.message);
        }
    });
};

const exportCsv = () => {
    const head = ['Date', 'Ref', 'Category', 'Payee', 'Description', 'Method', 'Amount', 'Status'];
    const rows = (expenses.value || []).map((e) => [
        e.expense_date ?? '', e.ref_no ?? '', (e.category_name ?? '').replace(/"/g, '""'),
        (e.payee ?? '').replace(/"/g, '""'), (e.description ?? '').replace(/"/g, '""'),
        e.payment_method ?? '', e.amount ?? '', e.payment_status ?? ''
    ]);
    const csv = [head, ...rows].map((r) => r.map((c) => `"${c}"`).join(',')).join('\n');
    const url = window.URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'expenses.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

onMounted(() => {
    getExpenses();
});
</script>

<template>
    <div>
        <div class="card">
            <h5>Expenses Report</h5>
            <Toast />

            <Toolbar class="mb-4">
                <template v-slot:start>
                    <div class="flex flex-wrap align-items-center gap-2">
                        <span class="p-float-label">
                            <Calendar inputId="from" v-model="fromDate" dateFormat="yy-mm-dd" />
                            <label for="from">From Date</label>
                        </span>
                        <span class="p-float-label">
                            <Calendar inputId="to" v-model="toDate" dateFormat="yy-mm-dd" />
                            <label for="to">To Date</label>
                        </span>
                        <Button label="Search" icon="pi pi-search" class="p-button-success" @click="getExpenses" />
                    </div>
                </template>
                <template v-slot:end>
                    <Button label="CSV" icon="pi pi-file-excel" class="p-button-success" @click="exportCsv" />
                </template>
            </Toolbar>

            <DataTable scrollable scrollHeight="65vh" :value="expenses" :paginator="true" class="p-datatable-gridlines" :rows="20" dataKey="id"
                :rowHover="true" responsiveLayout="scroll">
                <Column header="Date">
                    <template #body="{ data }">{{ fmtDate(data.expense_date) }}</template>
                </Column>
                <Column field="ref_no" header="Ref" />
                <Column field="category_name" header="Category" />
                <Column field="payee" header="Payee" />
                <Column field="description" header="Description" />
                <Column field="payment_method" header="Method" />
                <Column header="Amount">
                    <template #body="{ data }">{{ commonService.commaSeparator(data.amount) }}</template>
                    <template #footer><strong>{{ commonService.commaSeparator(totalAmount) }}</strong></template>
                </Column>
                <Column header="Status">
                    <template #body="{ data }">
                        <Tag :value="data.payment_status"
                            :severity="data.payment_status === 'Paid' ? 'success' : 'warning'" />
                    </template>
                </Column>
                <Column header="Receipt">
                    <template #body="{ data }">
                        <a v-if="data.receipt_path" :href="data.receipt_path" target="_blank">View</a>
                    </template>
                </Column>
                <Column header="">
                    <template #body="{ data }">
                        <Button v-if="data.payment_status === 'Unpaid'" label="Pay" icon="pi pi-wallet"
                            class="p-button-sm p-button-outlined" @click="payExpense(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>
