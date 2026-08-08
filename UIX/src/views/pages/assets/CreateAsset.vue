<script setup>
import { ref, reactive, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const assetAccounts = ref([]);
const depreciationRates = ref([]);

const paymentOptions = ref([
    { name: 'Cash', value: 'CASH' },
    { name: 'Bank', value: 'BANK' },
    { name: 'Credit', value: 'CREDIT' }
]);
const methodOptions = ref([
    { name: 'Straight-line', value: 'STRAIGHT_LINE' },
    { name: 'Reducing balance', value: 'REDUCING_BALANCE' }
]);

const paymentMethod = ref(paymentOptions.value[0]);
const depMethod = ref(methodOptions.value[0]);
const form = reactive({
    assetAccount: null,
    name: null,
    description: null,
    purchaseAmount: null,
    salvageValue: 0,
    depreciationRate: null,
    payee: null,
    location: null,
    purchaseDate: new Date()
});
const formError = reactive({});

const validateField = (v, k) => { formError[k] = commonService.validateFormField(v); };
const fmtDate = (d) => { if (!d) return null; const dt = new Date(d); return isNaN(dt.getTime()) ? null : dt.toISOString().slice(0, 10); };

const fetchAssetAccounts = () => {
    commonService.genericRequest('gl-search', 'post', true, { acct_type: 'ASSET', indicator: 'DR' }).then((r) => {
        if (r.status) assetAccounts.value = r.data;
    });
};
const fetchRates = () => {
    commonService.genericRequest('get-depreciation-rates', 'get', true, {}).then((r) => {
        if (r.status) depreciationRates.value = r.data;
    });
};

const onSubmit = () => {
    validateField(form.assetAccount, 'assetAccount');
    validateField(form.name, 'name');
    validateField(form.purchaseAmount, 'purchaseAmount');
    if (commonService.validateRequiredFields(formError)) {
        commonService.showError(toast, 'Please fill in the missing fields.');
        return;
    }
    const postData = {
        name: form.name,
        description: form.description,
        asset_account: form.assetAccount?.acct_no,
        purchase_amount: form.purchaseAmount,
        salvage_value: form.salvageValue,
        payment_method: paymentMethod.value.value,
        depreciation_rate_id: form.depreciationRate?.id,
        depreciation_method: depMethod.value.value,
        payee: form.payee,
        location: form.location,
        purchase_date: fmtDate(form.purchaseDate)
    };
    commonService.genericRequest('create-fixed-asset', 'post', true, postData).then((r) => {
        if (r.status) {
            commonService.showSuccess(toast, r.message);
            form.assetAccount = null; form.name = null; form.description = null; form.purchaseAmount = null;
            form.salvageValue = 0; form.depreciationRate = null; form.payee = null; form.location = null;
            form.purchaseDate = new Date(); paymentMethod.value = paymentOptions.value[0]; depMethod.value = methodOptions.value[0];
        } else {
            commonService.showError(toast, r.message);
        }
    });
};

onMounted(() => { fetchAssetAccounts(); fetchRates(); });
</script>

<template>
    <div>
        <div class="card">
            <h5>Add Fixed Asset</h5>
            <div class="grid p-fluid mt-3">
                <Toast />
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="acct" :options="assetAccounts" v-model="form.assetAccount" filter optionLabel="description"
                            @blur="validateField(form.assetAccount,'assetAccount')" :class="{ 'p-invalid': formError.assetAccount }" />
                        <label for="acct">Asset Account</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText id="name" v-model="form.name" @blur="validateField(form.name,'name')" :class="{ 'p-invalid': formError.name }" />
                        <label for="name">Asset Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText id="amt" v-model="form.purchaseAmount" @blur="validateField(form.purchaseAmount,'purchaseAmount')" :class="{ 'p-invalid': formError.purchaseAmount }" />
                        <label for="amt">Purchase Amount</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="pm" :options="paymentOptions" v-model="paymentMethod" optionLabel="name" />
                        <label for="pm">Payment Method</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="rate" :options="depreciationRates" v-model="form.depreciationRate" optionLabel="name" />
                        <label for="rate">Depreciation Rate</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="dm" :options="methodOptions" v-model="depMethod" optionLabel="name" />
                        <label for="dm">Depreciation Method</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText id="salv" v-model="form.salvageValue" />
                        <label for="salv">Salvage Value</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText id="payee" v-model="form.payee" />
                        <label for="payee">Supplier / Payee</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText id="loc" v-model="form.location" />
                        <label for="loc">Location (room/dept)</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Calendar id="date" v-model="form.purchaseDate" dateFormat="yy-mm-dd" />
                        <label for="date">Purchase Date</label>
                    </span>
                </div>
                <div class="field col-12">
                    <span class="p-float-label">
                        <Textarea inputId="desc" rows="2" v-model="form.description" />
                        <label for="desc">Description</label>
                    </span>
                </div>
                <div class="field col-12 md:col-8"></div>
                <div class="field col-12 md:col-4">
                    <Button @click="onSubmit" label="Save Asset" class="p-button-outlined" />
                </div>
            </div>
        </div>
    </div>
</template>
