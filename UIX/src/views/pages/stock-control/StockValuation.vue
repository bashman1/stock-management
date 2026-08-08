<script setup>
import { ref, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const products = ref([]);
const selectedProduct = ref(null);
const layers = ref([]);
const summary = ref({ on_hand: 0, total_value: 0, average_cost: 0, valuation_method: null, standard_cost: 0 });

const money = (v) => commonService.commaSeparator(v);
const methodLabel = (m) => ({ WAVG: 'Weighted Average', FIFO: 'FIFO', LIFO: 'LIFO', STANDARD: 'Standard Cost' }[m] || m);

const getProducts = () => commonService.genericRequest('get-products', 'post', true, { page: 1, rowPerPage: 1000 }).then((r) => {
    if (r.status) products.value = r.data?.data ?? r.data ?? [];
});

const loadLayers = () => {
    if (!selectedProduct.value) return;
    commonService.genericRequest('get-cost-layers', 'post', true, { product_id: selectedProduct.value.id }).then((r) => {
        if (r.status) {
            layers.value = r.data?.layers ?? [];
            summary.value = {
                on_hand: r.data?.on_hand ?? 0, total_value: r.data?.total_value ?? 0, average_cost: r.data?.average_cost ?? 0,
                valuation_method: r.data?.valuation_method, standard_cost: r.data?.standard_cost ?? 0
            };
        } else commonService.showError(toast, r.message);
    });
};

onMounted(() => getProducts());
</script>

<template>
    <div>
        <div class="card">
            <h5>Stock Valuation &amp; Cost Layers</h5>
            <Toast />
            <div class="grid p-fluid mt-2">
                <div class="field col-12 md:col-5">
                    <span class="p-float-label">
                        <Dropdown id="prod" :options="products" v-model="selectedProduct" filter optionLabel="name" @change="loadLayers" />
                        <label for="prod">Product</label>
                    </span>
                </div>
                <div class="col-12 md:col-7 flex align-items-center gap-2 flex-wrap" v-if="selectedProduct">
                    <Tag :value="'Method: ' + methodLabel(summary.valuation_method)" severity="info" />
                    <Tag v-if="summary.valuation_method === 'STANDARD'" :value="'Standard cost: ' + money(summary.standard_cost)" />
                    <Tag :value="'On hand: ' + money(summary.on_hand)" severity="success" />
                    <Tag :value="'Inventory value: ' + money(summary.total_value)" severity="success" />
                    <Tag :value="'Avg cost: ' + money(summary.average_cost)" />
                </div>
            </div>

            <DataTable :value="layers" :paginator="true" class="p-datatable-gridlines" :rows="20" dataKey="id" responsiveLayout="scroll">
                <Column header="Received"><template #body="{ data }">{{ (data.receipt_date || '').slice(0,10) }}</template></Column>
                <Column field="source_type" header="Source" />
                <Column field="source_ref" header="Ref" />
                <Column header="Original Qty"><template #body="{ data }">{{ money(data.quantity) }}</template></Column>
                <Column header="Remaining"><template #body="{ data }">{{ money(data.remaining_quantity) }}</template></Column>
                <Column header="Unit Cost"><template #body="{ data }">{{ money(data.unit_cost) }}</template></Column>
                <Column header="Layer Value"><template #body="{ data }">{{ money(data.remaining_quantity * data.unit_cost) }}</template></Column>
            </DataTable>
        </div>
    </div>
</template>
