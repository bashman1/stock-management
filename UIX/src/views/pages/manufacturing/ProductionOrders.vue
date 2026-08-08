<script setup>
import { ref, reactive, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const orders = ref([]);
const boms = ref([]);
const expanded = ref([]);
const details = ref({}); // orderId -> full order with materials + movements
const form = reactive({ bom: null, quantity: null });
const formError = reactive({});

const showDialog = ref(false);
const dialogMode = ref('scrap'); // 'scrap' | 'complete'
const action = reactive({ order: null, quantity: null, note: null });

const validateField = (v, k) => { formError[k] = commonService.validateFormField(v); };
const money = (v) => commonService.commaSeparator(v);
const severity = (s) => ({ Draft: 'secondary', InProgress: 'warning', Completed: 'success', Cancelled: 'danger' }[s] || 'info');

const getBoms = () => commonService.genericRequest('get-boms', 'post', true, {}).then((r) => {
    if (r.status) boms.value = r.data.map((b) => ({ ...b, label: `${b.product_name} — ${b.name}` }));
});
const getOrders = () => commonService.genericRequest('get-production-orders', 'post', true, {}).then((r) => { if (r.status) orders.value = r.data; });

const onRowExpand = (event) => {
    commonService.genericRequest('get-production-order', 'post', true, { id: event.data.id }).then((r) => {
        if (r.status) details.value = { ...details.value, [event.data.id]: r.data };
    });
};

const createOrder = () => {
    validateField(form.bom, 'bom');
    validateField(form.quantity, 'quantity');
    if (commonService.validateRequiredFields(formError)) { commonService.showError(toast, 'Pick a BOM and quantity.'); return; }
    commonService.genericRequest('create-production-order', 'post', true, { bom_id: form.bom.id, quantity: Number(form.quantity) }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); form.bom = null; form.quantity = null; getOrders(); }
        else commonService.showError(toast, r.message);
    });
};

const refresh = (orderId) => { getOrders(); if (orderId) onRowExpand({ data: { id: orderId } }); };

const issue = (order) => {
    commonService.genericRequest('issue-materials', 'post', true, { id: order.id }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); refresh(order.id); }
        else commonService.showError(toast, r.message);
    });
};
const cancel = (order) => {
    commonService.genericRequest('cancel-production-order', 'post', true, { id: order.id }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); refresh(order.id); }
        else commonService.showError(toast, r.message);
    });
};
const openAction = (order, mode) => { dialogMode.value = mode; action.order = order; action.quantity = null; action.note = null; showDialog.value = true; };
const submitAction = () => {
    const qty = Number(action.quantity);
    if (!qty || qty <= 0) { commonService.showError(toast, 'Enter a quantity greater than 0.'); return; }
    const url = dialogMode.value === 'scrap' ? 'record-scrap' : 'complete-production';
    const postData = dialogMode.value === 'scrap'
        ? { id: action.order.id, quantity: qty, note: action.note }
        : { id: action.order.id, quantity_completed: qty };
    commonService.genericRequest(url, 'post', true, postData).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); showDialog.value = false; refresh(action.order.id); }
        else commonService.showError(toast, r.message);
    });
};

onMounted(() => { getBoms(); getOrders(); });
</script>

<template>
    <div>
        <div class="card">
            <h5>New Production Order</h5>
            <Toast />
            <div class="grid p-fluid mt-2">
                <div class="field col-12 md:col-6">
                    <span class="p-float-label">
                        <Dropdown id="bom" :options="boms" v-model="form.bom" filter optionLabel="label"
                            @blur="validateField(form.bom,'bom')" :class="{ 'p-invalid': formError.bom }" />
                        <label for="bom">Bill of Materials</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText id="q" v-model="form.quantity" @blur="validateField(form.quantity,'quantity')" :class="{ 'p-invalid': formError.quantity }" />
                        <label for="q">Quantity to produce</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <Button @click="createOrder" label="Create Order" class="p-button-outlined" />
                </div>
            </div>
        </div>

        <div class="card">
            <h5>Production Orders</h5>
            <DataTable :value="orders" v-model:expandedRows="expanded" @rowExpand="onRowExpand" dataKey="id"
                :paginator="true" :rows="15" class="p-datatable-gridlines" responsiveLayout="scroll">
                <Column :expander="true" headerStyle="width: 3rem" />
                <Column field="order_no" header="Order" />
                <Column field="product_name" header="Finished Product" />
                <Column header="Qty"><template #body="{ data }">{{ data.quantity }}</template></Column>
                <Column header="Done"><template #body="{ data }">{{ data.quantity_completed }}</template></Column>
                <Column header="Scrapped"><template #body="{ data }">{{ data.quantity_scrapped }}</template></Column>
                <Column header="WIP cost"><template #body="{ data }">{{ money(data.wip_cost) }}</template></Column>
                <Column header="Status"><template #body="{ data }"><Tag :value="data.status" :severity="severity(data.status)" /></template></Column>
                <Column header="Actions">
                    <template #body="{ data }">
                        <Button v-if="data.status === 'Draft'" label="Issue" icon="pi pi-send" class="p-button-sm p-button-outlined mr-1 mb-1" @click="issue(data)" />
                        <Button v-if="data.status === 'InProgress'" label="Complete" icon="pi pi-check" class="p-button-sm p-button-outlined p-button-success mr-1 mb-1" @click="openAction(data,'complete')" />
                        <Button v-if="data.status === 'InProgress'" label="Scrap" icon="pi pi-times" class="p-button-sm p-button-outlined p-button-warning mr-1 mb-1" @click="openAction(data,'scrap')" />
                        <Button v-if="data.status === 'Draft'" label="Cancel" icon="pi pi-ban" class="p-button-sm p-button-text p-button-danger" @click="cancel(data)" />
                    </template>
                </Column>
                <template #expansion="{ data }">
                    <div class="p-3" v-if="details[data.id]">
                        <h6>Materials</h6>
                        <table class="w-full mb-3" style="border-collapse: collapse">
                            <thead><tr class="text-left"><th class="p-2">Material</th><th class="p-2 text-right">Required</th><th class="p-2 text-right">Consumed</th><th class="p-2 text-right">Unit cost</th></tr></thead>
                            <tbody>
                                <tr v-for="m in details[data.id].materials" :key="m.id">
                                    <td class="p-2">{{ m.material?.name }}</td>
                                    <td class="p-2 text-right">{{ m.quantity_required }}</td>
                                    <td class="p-2 text-right">{{ m.quantity_consumed }}</td>
                                    <td class="p-2 text-right">{{ money(m.unit_cost) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <h6>Movements</h6>
                        <table class="w-full" style="border-collapse: collapse">
                            <thead><tr class="text-left"><th class="p-2">Type</th><th class="p-2 text-right">Quantity</th><th class="p-2 text-right">Cost</th><th class="p-2">Note</th></tr></thead>
                            <tbody>
                                <tr v-for="mv in details[data.id].movements" :key="mv.id">
                                    <td class="p-2"><Tag :value="mv.type" :severity="mv.type === 'SCRAP' ? 'warning' : (mv.type === 'COMPLETE' ? 'success' : 'info')" /></td>
                                    <td class="p-2 text-right">{{ mv.quantity }}</td>
                                    <td class="p-2 text-right">{{ money(mv.cost) }}</td>
                                    <td class="p-2 text-600">{{ mv.note }}</td>
                                </tr>
                                <tr v-if="!details[data.id].movements?.length"><td colspan="4" class="p-2 text-600">No movements yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </DataTable>
        </div>

        <Dialog v-model:visible="showDialog" modal :header="dialogMode === 'scrap' ? 'Record Scrap' : 'Complete Production'" :style="{ width: '28rem' }">
            <div v-if="action.order" class="p-fluid">
                <p class="mb-3">
                    {{ dialogMode === 'scrap' ? 'Scrap units from' : 'Receive finished units from' }}
                    <b>{{ action.order.order_no }}</b>
                    (remaining: {{ action.order.quantity - action.order.quantity_completed - action.order.quantity_scrapped }}).
                </p>
                <div class="field">
                    <label for="aq">Quantity</label>
                    <InputText id="aq" v-model="action.quantity" />
                </div>
                <div class="field" v-if="dialogMode === 'scrap'">
                    <label for="an">Reason</label>
                    <InputText id="an" v-model="action.note" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" class="p-button-text" @click="showDialog = false" />
                <Button :label="dialogMode === 'scrap' ? 'Record Scrap' : 'Complete'" icon="pi pi-check" @click="submitAction" />
            </template>
        </Dialog>
    </div>
</template>
