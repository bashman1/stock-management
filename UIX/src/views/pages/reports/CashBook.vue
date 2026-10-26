<script setup>
import { ref, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const entries = ref([]);
const summary = ref(null);
const fromDate = ref(null);
const toDate = ref(null);

const fmtDate = (d) => {
    if (!d) return null;
    const dt = new Date(d);
    if (isNaN(dt.getTime())) return null;
    return commonService.toApiDate(dt);
};

const getCashBook = () => {
    const postData = {
        status: 'Active',
        fromDate: fmtDate(fromDate.value),
        toDate: fmtDate(toDate.value)
    };
    commonService.genericRequest('get-cash-book', 'post', true, postData).then((response) => {
        if (response.status) {
            entries.value = response.data?.entries ?? [];
            summary.value = response.data?.summary ?? null;
        } else {
            commonService.showError(toast, response.message);
        }
    });
};

// receipts (Dr) value for a given column code, else blank
const receipt = (row, cd) => (row.ind === cd && row.dr_cr_ind === 'Dr' ? Number(row.tran_amount) : null);
const payment = (row, cd) => (row.ind === cd && row.dr_cr_ind === 'Cr' ? Number(row.tran_amount) : null);
const show = (v) => (v === null || v === undefined ? '' : commonService.commaSeparator(v));

const exportCsv = () => {
    const head = ['Date', 'Details', 'V. No.', 'Type', 'Receipt Cash', 'Receipt Bank', 'Receipt Discount', 'Payment Cash', 'Payment Bank', 'Payment Discount'];
    const rows = entries.value.map((r) => [
        r.transaction_date ?? '',
        (r.description ?? '').replace(/"/g, '""'),
        r.tran_id ?? '',
        r.ind ?? '',
        receipt(r, 'CL') ?? '', receipt(r, 'CGL') ?? '', receipt(r, 'PD') ?? '',
        payment(r, 'CL') ?? '', payment(r, 'CGL') ?? '', payment(r, 'PD') ?? ''
    ]);
    const csv = [head, ...rows].map((row) => row.map((c) => `"${c}"`).join(',')).join('\n');
    const url = window.URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'cash_book.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const printPdf = () => window.print();

onMounted(() => {
    getCashBook();
});
</script>

<template>
    <div>
        <div class="card">
            <h5>Cash Book</h5>
            <Toast />

            <Toolbar class="mb-4">
                <template v-slot:start>
                    <div class="flex flex-wrap align-items-center gap-2">
                        <span class="p-float-label">
                            <Calendar inputId="from" v-model="fromDate" dateFormat="yy-mm-dd"></Calendar>
                            <label for="from">From Date</label>
                        </span>
                        <span class="p-float-label">
                            <Calendar inputId="to" v-model="toDate" dateFormat="yy-mm-dd"></Calendar>
                            <label for="to">To Date</label>
                        </span>
                        <Button label="Search" icon="pi pi-search" class="p-button-success" @click="getCashBook" />
                    </div>
                </template>
                <template v-slot:end>
                    <div class="my-2">
                        <Button label="CSV" icon="pi pi-file-excel" class="p-button-success mr-2" @click="exportCsv" />
                        <Button label="PDF" icon="pi pi-file-pdf" class="p-button-danger" @click="printPdf" />
                    </div>
                </template>
            </Toolbar>

            <!-- Opening / closing balance summary -->
            <div v-if="summary" class="grid mb-3">
                <div class="col-6 md:col-3">
                    <div class="surface-100 border-round p-3">
                        <div class="text-500 text-sm">Opening Cash</div>
                        <div class="text-900 font-bold">{{ show(summary.openingCash) }}</div>
                    </div>
                </div>
                <div class="col-6 md:col-3">
                    <div class="surface-100 border-round p-3">
                        <div class="text-500 text-sm">Closing Cash</div>
                        <div class="text-900 font-bold">{{ show(summary.closingCash) }}</div>
                    </div>
                </div>
                <div class="col-6 md:col-3">
                    <div class="surface-100 border-round p-3">
                        <div class="text-500 text-sm">Opening Bank</div>
                        <div class="text-900 font-bold">{{ show(summary.openingBank) }}</div>
                    </div>
                </div>
                <div class="col-6 md:col-3">
                    <div class="surface-100 border-round p-3">
                        <div class="text-500 text-sm">Closing Bank</div>
                        <div class="text-900 font-bold">{{ show(summary.closingBank) }}</div>
                    </div>
                </div>
            </div>

            <DataTable scrollable scrollHeight="65vh" :value="entries" :paginator="true" class="p-datatable-gridlines" :rows="20" dataKey="id" :rowHover="true" responsiveLayout="scroll">
                <ColumnGroup type="header">
                    <Row>
                        <Column header="Date" :rowspan="2" />
                        <Column header="Details" :rowspan="2" />
                        <Column header="V. No." :rowspan="2" />
                        <Column header="Receipts (Dr)" :colspan="3" />
                        <Column header="Payments (Cr)" :colspan="3" />
                    </Row>
                    <Row>
                        <Column header="Cash" />
                        <Column header="Bank" />
                        <Column header="Discount" />
                        <Column header="Cash" />
                        <Column header="Bank" />
                        <Column header="Discount" />
                    </Row>
                </ColumnGroup>

                <Column header="Date"><template #body="{ data }">{{ data.transaction_date }}</template></Column>
                <Column header="Details"><template #body="{ data }">{{ data.description }}</template></Column>
                <Column header="V. No."><template #body="{ data }">{{ data.tran_id }}</template></Column>
                <Column header="Cash"><template #body="{ data }">{{ show(receipt(data, 'CL')) }}</template></Column>
                <Column header="Bank"><template #body="{ data }">{{ show(receipt(data, 'CGL')) }}</template></Column>
                <Column header="Discount"><template #body="{ data }">{{ show(receipt(data, 'PD')) }}</template></Column>
                <Column header="Cash"><template #body="{ data }">{{ show(payment(data, 'CL')) }}</template></Column>
                <Column header="Bank"><template #body="{ data }">{{ show(payment(data, 'CGL')) }}</template></Column>
                <Column header="Discount"><template #body="{ data }">{{ show(payment(data, 'PD')) }}</template></Column>

                <ColumnGroup type="footer" v-if="summary">
                    <Row>
                        <Column footer="Totals:" :colspan="3" footerStyle="text-align:right" />
                        <Column :footer="show(summary.cashReceipts)" />
                        <Column :footer="show(summary.bankReceipts)" />
                        <Column :footer="show(summary.discountAllowed)" />
                        <Column :footer="show(summary.cashPayments)" />
                        <Column :footer="show(summary.bankPayments)" />
                        <Column :footer="show(summary.discountReceived)" />
                    </Row>
                </ColumnGroup>
            </DataTable>
        </div>
    </div>
</template>
