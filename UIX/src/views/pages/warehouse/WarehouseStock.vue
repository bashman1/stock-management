<script setup>
import { ref, reactive, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const warehouses = ref([]);
const selectedWarehouse = ref(null);
const items = ref([]);
const total = ref(0);
const locations = ref([]);

const showDialog = ref(false);
const putaway = reactive({ row: null, toBin: null, quantity: null });

const getWarehouses = () => commonService.genericRequest('get-warehouses', 'post', true, {}).then((r) => {
    if (r.status) { warehouses.value = r.data; if (r.data?.length) { selectedWarehouse.value = r.data[0]; onWarehouseChange(); } }
});

const loadStock = () => {
    if (!selectedWarehouse.value) return;
    commonService.genericRequest('get-warehouse-stock', 'post', true, { warehouse_id: selectedWarehouse.value.id }).then((r) => {
        if (r.status) { items.value = r.data?.items ?? []; total.value = r.data?.total ?? 0; }
        else commonService.showError(toast, r.message);
    });
};

const loadLocations = () => {
    if (!selectedWarehouse.value) { locations.value = []; return; }
    commonService.genericRequest('get-warehouse-locations', 'post', true, { warehouse_id: selectedWarehouse.value.id }).then((r) => {
        if (r.status) locations.value = (r.data?.flat ?? []).map((l) => ({ ...l, label: `${l.type}: ${l.name}` }));
    });
};

const onWarehouseChange = () => { loadStock(); loadLocations(); };

const openPutaway = (row) => {
    putaway.row = row; putaway.toBin = null; putaway.quantity = null;
    showDialog.value = true;
};

const submitPutaway = () => {
    const qty = Number(putaway.quantity);
    if (!putaway.toBin) { commonService.showError(toast, 'Choose a destination bin.'); return; }
    if (!qty || qty <= 0) { commonService.showError(toast, 'Enter a quantity greater than 0.'); return; }
    if (qty > Number(putaway.row.quantity)) { commonService.showError(toast, 'Quantity exceeds what is available in this location.'); return; }
    const postData = {
        warehouse_id: selectedWarehouse.value.id,
        product_id: putaway.row.product_id,
        from_location_id: putaway.row.location_id ?? null,
        to_location_id: putaway.toBin.id,
        quantity: qty
    };
    commonService.genericRequest('put-away-stock', 'post', true, postData).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); showDialog.value = false; loadStock(); }
        else commonService.showError(toast, r.message);
    });
};

onMounted(() => getWarehouses());
</script>

<template>
    <div>
        <div class="card">
            <h5>Warehouse Stock</h5>
            <Toast />
            <div class="grid p-fluid mt-2">
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="wh" :options="warehouses" v-model="selectedWarehouse" optionLabel="name" @change="onWarehouseChange" />
                        <label for="wh">Warehouse</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4 flex align-items-center">
                    <Tag :value="'Total on hand: ' + commonService.commaSeparator(total)" severity="info" />
                </div>
            </div>

            <DataTable :value="items" :paginator="true" class="p-datatable-gridlines" :rows="20" dataKey="id" responsiveLayout="scroll">
                <Column field="product_name" header="Product" />
                <Column field="product_ref" header="Ref" />
                <Column header="Location"><template #body="{ data }">{{ data.location_name ? (data.location_type + ': ' + data.location_name) : 'Unassigned (pool)' }}</template></Column>
                <Column header="Quantity"><template #body="{ data }">{{ commonService.commaSeparator(data.quantity) }}</template></Column>
                <Column header="">
                    <template #body="{ data }">
                        <Button label="Put away" icon="pi pi-arrow-right" class="p-button-sm p-button-outlined"
                            :disabled="Number(data.quantity) <= 0 || !locations.length" @click="openPutaway(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="showDialog" modal header="Put Away to Bin" :style="{ width: '30rem' }">
            <div v-if="putaway.row" class="p-fluid">
                <p class="mb-3">
                    Move <b>{{ putaway.row.product_name }}</b> from
                    <b>{{ putaway.row.location_name ? (putaway.row.location_type + ': ' + putaway.row.location_name) : 'the unassigned pool' }}</b>
                    (available: {{ commonService.commaSeparator(putaway.row.quantity) }}).
                </p>
                <div class="field">
                    <label for="dbin">Destination bin</label>
                    <Dropdown id="dbin" :options="locations.filter((l) => l.id !== putaway.row.location_id)" v-model="putaway.toBin" filter optionLabel="label" placeholder="Select a location" />
                </div>
                <div class="field">
                    <label for="dqty">Quantity</label>
                    <InputText id="dqty" v-model="putaway.quantity" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" class="p-button-text" @click="showDialog = false" />
                <Button label="Put Away" icon="pi pi-check" @click="submitPutaway" />
            </template>
        </Dialog>
    </div>
</template>
