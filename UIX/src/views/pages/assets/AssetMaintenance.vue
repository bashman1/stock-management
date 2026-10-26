<script setup>
import { ref, reactive, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const assets = ref([]);
const maintenances = ref([]);
const typeOptions = ref(['Repair', 'Service', 'Inspection']);
const paymentOptions = ref([{ name: 'Cash', value: 'CASH' }, { name: 'Bank', value: 'BANK' }, { name: 'Credit', value: 'CREDIT' }]);
const paymentMethod = ref(paymentOptions.value[0]);
const form = reactive({ asset: null, type: 'Repair', cost: null, payee: null, description: null, date: new Date() });
const formError = reactive({});

const validateField = (v, k) => { formError[k] = commonService.validateFormField(v); };
const fmtDate = (d) => { if (!d) return null; const dt = new Date(d); return isNaN(dt.getTime()) ? null : commonService.toApiDate(dt); };

const getAssets = () => commonService.genericRequest('get-fixed-assets', 'post', true, {}).then((r) => { if (r.status) assets.value = r.data; });
const getMaintenances = () => commonService.genericRequest('get-asset-maintenances', 'post', true, {}).then((r) => { if (r.status) maintenances.value = r.data; });

const onSubmit = () => {
    validateField(form.asset, 'asset');
    validateField(form.cost, 'cost');
    if (commonService.validateRequiredFields(formError)) {
        commonService.showError(toast, 'Select an asset and enter a cost.');
        return;
    }
    const postData = {
        asset_id: form.asset?.id,
        type: form.type,
        cost: form.cost,
        payment_method: paymentMethod.value.value,
        payee: form.payee,
        description: form.description,
        maintenance_date: fmtDate(form.date)
    };
    commonService.genericRequest('create-asset-maintenance', 'post', true, postData).then((r) => {
        if (r.status) {
            commonService.showSuccess(toast, r.message);
            form.asset = null; form.cost = null; form.payee = null; form.description = null; form.date = new Date();
            getMaintenances();
        } else commonService.showError(toast, r.message);
    });
};

onMounted(() => { getAssets(); getMaintenances(); });
</script>

<template>
    <div>
        <div class="card">
            <h5>Record Maintenance</h5>
            <Toast />
            <div class="grid p-fluid mt-3">
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="asset" :options="assets" v-model="form.asset" filter optionLabel="name"
                            @blur="validateField(form.asset,'asset')" :class="{ 'p-invalid': formError.asset }" />
                        <label for="asset">Asset</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="type" :options="typeOptions" v-model="form.type" />
                        <label for="type">Type</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText id="cost" v-model="form.cost" @blur="validateField(form.cost,'cost')" :class="{ 'p-invalid': formError.cost }" />
                        <label for="cost">Cost</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="pm" :options="paymentOptions" v-model="paymentMethod" optionLabel="name" />
                        <label for="pm">Payment Method</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText id="payee" v-model="form.payee" />
                        <label for="payee">Vendor / Payee</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Calendar id="d" v-model="form.date" dateFormat="yy-mm-dd" />
                        <label for="d">Date</label>
                    </span>
                </div>
                <div class="field col-12 md:col-6">
                    <span class="p-float-label">
                        <InputText id="desc" v-model="form.description" />
                        <label for="desc">Description</label>
                    </span>
                </div>
                <div class="field col-12 md:col-9"></div>
                <div class="field col-12 md:col-3">
                    <Button @click="onSubmit" label="Save Maintenance" class="p-button-outlined" />
                </div>
            </div>
        </div>

        <div class="card">
            <h5>Maintenance History</h5>
            <DataTable :value="maintenances" :paginator="true" class="p-datatable-gridlines" :rows="15" dataKey="id" responsiveLayout="scroll">
                <Column field="asset_name" header="Asset" />
                <Column field="ref_no" header="Ref" />
                <Column field="type" header="Type" />
                <Column header="Date"><template #body="{ data }">{{ data.maintenance_date }}</template></Column>
                <Column header="Cost"><template #body="{ data }">{{ commonService.commaSeparator(data.cost) }}</template></Column>
                <Column field="payment_method" header="Method" />
                <Column field="payee" header="Vendor" />
                <Column field="description" header="Description" />
            </DataTable>
        </div>
    </div>
</template>
