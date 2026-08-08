<script setup>
import { ref, reactive, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const products = ref([]);
const adjustments = ref([]);
const form = reactive({ type: 'IN', reason: null });
const lines = ref([{ product: null, quantity: null, unit_cost: null }]);
const typeOptions = [{ label: 'Write-on (IN)', value: 'IN' }, { label: 'Write-off (OUT)', value: 'OUT' }];

const money = (v) => commonService.commaSeparator(v);

const getProducts = () => commonService.genericRequest('get-products', 'post', true, { page: 1, rowPerPage: 1000 }).then((r) => {
    if (r.status) products.value = r.data?.data ?? r.data ?? [];
});
const getAdjustments = () => commonService.genericRequest('get-stock-adjustments', 'post', true, {}).then((r) => { if (r.status) adjustments.value = r.data; });

const addLine = () => lines.value.push({ product: null, quantity: null, unit_cost: null });
const removeLine = (i) => { if (lines.value.length > 1) lines.value.splice(i, 1); };

const onSubmit = () => {
    const rows = lines.value.filter((l) => l.product && Number(l.quantity) > 0);
    if (!rows.length) { commonService.showError(toast, 'Add at least one product line with a quantity.'); return; }
    const postData = {
        type: form.type, reason: form.reason,
        items: rows.map((l) => ({ product_id: l.product.id, quantity: Number(l.quantity), unit_cost: Number(l.unit_cost) || 0 }))
    };
    commonService.genericRequest('create-stock-adjustment', 'post', true, postData).then((r) => {
        if (r.status) {
            commonService.showSuccess(toast, r.message);
            form.reason = null; lines.value = [{ product: null, quantity: null, unit_cost: null }];
            getAdjustments();
        } else commonService.showError(toast, r.message);
    });
};

onMounted(() => { getProducts(); getAdjustments(); });
</script>

<template>
    <div>
        <div class="card">
            <h5>New Stock Adjustment</h5>
            <Toast />
            <div class="grid p-fluid mt-2">
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="type" :options="typeOptions" v-model="form.type" optionLabel="label" optionValue="value" />
                        <label for="type">Direction</label>
                    </span>
                </div>
                <div class="field col-12 md:col-9">
                    <span class="p-float-label">
                        <InputText id="reason" v-model="form.reason" />
                        <label for="reason">Reason</label>
                    </span>
                </div>
            </div>

            <table class="w-full" style="border-collapse: collapse">
                <thead>
                    <tr class="text-left">
                        <th class="p-2" style="width: 50%">Product</th>
                        <th class="p-2" style="width: 20%">Quantity</th>
                        <th class="p-2" style="width: 22%">Unit Cost (write-on)</th>
                        <th class="p-2" style="width: 8%"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(l, i) in lines" :key="i">
                        <td class="p-1"><Dropdown :options="products" v-model="l.product" filter optionLabel="name" placeholder="Select product" class="w-full" /></td>
                        <td class="p-1"><InputText v-model="l.quantity" placeholder="Qty (base units)" class="w-full" /></td>
                        <td class="p-1"><InputText v-model="l.unit_cost" :disabled="form.type === 'OUT'" :placeholder="form.type === 'OUT' ? 'by valuation method' : 'Cost per unit'" class="w-full" /></td>
                        <td class="p-1 text-center"><Button icon="pi pi-trash" class="p-button-text p-button-danger" @click="removeLine(i)" :disabled="lines.length <= 1" /></td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-2 flex justify-content-between">
                <Button label="Add Line" icon="pi pi-plus" class="p-button-text" @click="addLine" />
                <Button label="Post Adjustment" icon="pi pi-check" class="p-button-outlined" @click="onSubmit" />
            </div>
        </div>

        <div class="card">
            <h5>Adjustment History</h5>
            <DataTable :value="adjustments" :paginator="true" class="p-datatable-gridlines" :rows="15" dataKey="id" responsiveLayout="scroll">
                <Column field="ref_no" header="Ref" />
                <Column header="Direction"><template #body="{ data }"><Tag :value="data.type" :severity="data.type === 'IN' ? 'success' : 'warning'" /></template></Column>
                <Column field="reason" header="Reason" />
                <Column header="Lines"><template #body="{ data }">{{ data.items?.length }}</template></Column>
                <Column header="Value"><template #body="{ data }">{{ money(data.total_cost) }}</template></Column>
                <Column header="Date"><template #body="{ data }">{{ (data.adjustment_date || data.created_on || '').slice(0,10) }}</template></Column>
            </DataTable>
        </div>
    </div>
</template>
