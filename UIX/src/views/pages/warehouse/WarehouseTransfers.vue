<script setup>
import { ref, reactive, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const warehouses = ref([]);
const products = ref([]);
const transfers = ref([]);
const fromLocations = ref([]);
const toLocations = ref([]);
const form = reactive({ product: null, from: null, fromBin: null, to: null, toBin: null, quantity: null, reason: null, date: new Date() });
const formError = reactive({});

const validateField = (v, k) => { formError[k] = commonService.validateFormField(v); };
const fmtDate = (d) => { if (!d) return null; const dt = new Date(d); return isNaN(dt.getTime()) ? null : dt.toISOString().slice(0, 10); };

const getWarehouses = () => commonService.genericRequest('get-warehouses', 'post', true, {}).then((r) => { if (r.status) warehouses.value = r.data; });

// load a warehouse's locations (bins/shelves/racks) into the chosen side
const loadLocations = (warehouse, target) => {
    const bucket = target === 'from' ? fromLocations : toLocations;
    bucket.value = [];
    if (target === 'from') form.fromBin = null; else form.toBin = null;
    if (!warehouse) return;
    commonService.genericRequest('get-warehouse-locations', 'post', true, { warehouse_id: warehouse.id }).then((r) => {
        if (r.status) bucket.value = (r.data?.flat ?? []).map((l) => ({ ...l, label: `${l.type}: ${l.name}` }));
    });
};
const getProducts = () => commonService.genericRequest('get-products', 'post', true, { page: 1, rowPerPage: 1000 }).then((r) => {
    if (r.status) products.value = r.data?.data ?? r.data ?? [];
});
const getTransfers = () => commonService.genericRequest('get-warehouse-transfers', 'post', true, {}).then((r) => { if (r.status) transfers.value = r.data; });

const onSubmit = () => {
    validateField(form.product, 'product');
    validateField(form.from, 'from');
    validateField(form.to, 'to');
    validateField(form.quantity, 'quantity');
    if (commonService.validateRequiredFields(formError)) { commonService.showError(toast, 'Fill product, source, destination and quantity.'); return; }
    const postData = {
        product_id: form.product?.id, from_warehouse_id: form.from?.id, to_warehouse_id: form.to?.id,
        from_location_id: form.fromBin?.id ?? null, to_location_id: form.toBin?.id ?? null,
        quantity: form.quantity, reason: form.reason, transfer_date: fmtDate(form.date)
    };
    commonService.genericRequest('create-warehouse-transfer', 'post', true, postData).then((r) => {
        if (r.status) {
            commonService.showSuccess(toast, r.message);
            form.product = null; form.from = null; form.fromBin = null; form.to = null; form.toBin = null;
            form.quantity = null; form.reason = null; form.date = new Date();
            fromLocations.value = []; toLocations.value = [];
            getTransfers();
        } else commonService.showError(toast, r.message);
    });
};

onMounted(() => { getWarehouses(); getProducts(); getTransfers(); });
</script>

<template>
    <div>
        <div class="card">
            <h5>Transfer Stock Between Warehouses</h5>
            <Toast />
            <div class="grid p-fluid mt-3">
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="prod" :options="products" v-model="form.product" filter optionLabel="name"
                            @blur="validateField(form.product,'product')" :class="{ 'p-invalid': formError.product }" />
                        <label for="prod">Product</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="from" :options="warehouses" v-model="form.from" optionLabel="name"
                            @change="loadLocations(form.from,'from')" @blur="validateField(form.from,'from')" :class="{ 'p-invalid': formError.from }" />
                        <label for="from">From Warehouse</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="fromBin" :options="fromLocations" v-model="form.fromBin" filter showClear optionLabel="label" :disabled="!fromLocations.length" />
                        <label for="fromBin">From Bin (optional)</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="to" :options="warehouses" v-model="form.to" optionLabel="name"
                            @change="loadLocations(form.to,'to')" @blur="validateField(form.to,'to')" :class="{ 'p-invalid': formError.to }" />
                        <label for="to">To Warehouse</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="toBin" :options="toLocations" v-model="form.toBin" filter showClear optionLabel="label" :disabled="!toLocations.length" />
                        <label for="toBin">To Bin (optional)</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText id="qty" v-model="form.quantity" @blur="validateField(form.quantity,'quantity')" :class="{ 'p-invalid': formError.quantity }" />
                        <label for="qty">Quantity (base units)</label>
                    </span>
                </div>
                <div class="field col-12 md:col-9">
                    <span class="p-float-label">
                        <InputText id="reason" v-model="form.reason" />
                        <label for="reason">Reason</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <Button @click="onSubmit" label="Transfer" class="p-button-outlined" />
                </div>
            </div>
        </div>

        <div class="card">
            <h5>Transfer History</h5>
            <DataTable :value="transfers" :paginator="true" class="p-datatable-gridlines" :rows="15" dataKey="id" responsiveLayout="scroll">
                <Column field="ref_no" header="Ref" />
                <Column field="product_name" header="Product" />
                <Column field="from_warehouse" header="From" />
                <Column field="to_warehouse" header="To" />
                <Column header="Quantity"><template #body="{ data }">{{ commonService.commaSeparator(data.quantity) }}</template></Column>
                <Column header="Date"><template #body="{ data }">{{ (data.transfer_date || '').slice(0,10) }}</template></Column>
                <Column field="reason" header="Reason" />
            </DataTable>
        </div>
    </div>
</template>
