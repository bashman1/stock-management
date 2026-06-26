<script setup>
import { ref, computed, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const assets = ref([]);
const search = ref(null);

const totalCost = computed(() => (assets.value || []).reduce((s, a) => s + (Number(a.purchase_amount) || 0), 0));
const totalBook = computed(() => (assets.value || []).reduce((s, a) => s + (Number(a.book_value) || 0), 0));

const getAssets = () => {
    commonService.genericRequest('get-fixed-assets', 'post', true, { searchKey: search.value }).then((r) => {
        if (r.status) assets.value = r.data; else commonService.showError(toast, r.message);
    });
};

const runDepreciation = () => {
    commonService.genericRequest('run-depreciation', 'post', true, {}).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); getAssets(); }
        else commonService.showError(toast, r.message);
    });
};

const exportCsv = () => {
    const head = ['Ref', 'Name', 'Cost', 'Accumulated Dep', 'Book Value', 'Location', 'Status'];
    const rows = (assets.value || []).map((a) => [
        a.ref_no ?? '', (a.name ?? '').replace(/"/g, '""'), a.purchase_amount ?? '',
        a.accumulated_depreciation ?? '', a.book_value ?? '', (a.location ?? '').replace(/"/g, '""'), a.status ?? ''
    ]);
    const csv = [head, ...rows].map((r) => r.map((c) => `"${c}"`).join(',')).join('\n');
    const url = window.URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
    const link = document.createElement('a');
    link.href = url; link.setAttribute('download', 'asset_register.csv');
    document.body.appendChild(link); link.click(); document.body.removeChild(link);
};

onMounted(() => getAssets());
</script>

<template>
    <div>
        <div class="card">
            <h5>Asset Register</h5>
            <Toast />
            <Toolbar class="mb-4">
                <template v-slot:start>
                    <div class="flex flex-wrap align-items-center gap-2">
                        <InputText v-model="search" placeholder="Search" />
                        <Button label="Search" icon="pi pi-search" class="p-button-success" @click="getAssets" />
                        <Button label="Run Depreciation" icon="pi pi-replay" class="p-button-help" @click="runDepreciation" />
                    </div>
                </template>
                <template v-slot:end>
                    <Button label="CSV" icon="pi pi-file-excel" class="p-button-success" @click="exportCsv" />
                </template>
            </Toolbar>

            <DataTable :value="assets" :paginator="true" class="p-datatable-gridlines" :rows="20" dataKey="id"
                :rowHover="true" responsiveLayout="scroll">
                <Column field="ref_no" header="Ref" />
                <Column field="name" header="Name" />
                <Column header="Cost">
                    <template #body="{ data }">{{ commonService.commaSeparator(data.purchase_amount) }}</template>
                    <template #footer><strong>{{ commonService.commaSeparator(totalCost) }}</strong></template>
                </Column>
                <Column header="Accumulated Dep.">
                    <template #body="{ data }">{{ commonService.commaSeparator(data.accumulated_depreciation) }}</template>
                </Column>
                <Column header="Book Value">
                    <template #body="{ data }">{{ commonService.commaSeparator(data.book_value) }}</template>
                    <template #footer><strong>{{ commonService.commaSeparator(totalBook) }}</strong></template>
                </Column>
                <Column header="Method">
                    <template #body="{ data }">{{ data.depreciation_method }}</template>
                </Column>
                <Column field="location" header="Location" />
                <Column header="Branch">
                    <template #body="{ data }">{{ data.branch?.name }}</template>
                </Column>
                <Column header="Status">
                    <template #body="{ data }"><Tag :value="data.status" severity="info" /></template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>
