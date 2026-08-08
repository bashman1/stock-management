<script setup>
import { ref, reactive, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const expenseAccounts = ref([]);

const paymentOptions = ref([
    { name: 'Cash', value: 'CASH' },
    { name: 'Bank', value: 'BANK' },
    { name: 'Credit', value: 'CREDIT' }
]);
const paymentMethod = ref(paymentOptions.value[0]);

const receipt = ref(null);
const formData = reactive({
    expenseAcct: null,
    amount: null,
    payee: null,
    expenseDate: new Date(),
    description: null
});
const formError = reactive({});

const validateField = (value, key) => {
    formError[key] = commonService.validateFormField(value);
};

// Expense categories = EXPENSE accounts (via gl-search)
const fetchExpenseAccounts = () => {
    const postData = { acct_type: 'EXPENSE', gl_no: null, description: null, acct_no: null, indicator: 'DR' };
    commonService.genericRequest('gl-search', 'post', true, postData).then((response) => {
        if (response.status) {
            expenseAccounts.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    });
};

const onReceiptSelect = (event) => {
    const file = event.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        receipt.value = e.target.result; // base64 data URL
    };
    reader.readAsDataURL(file);
};

const fmtDate = (d) => {
    if (!d) return null;
    const dt = new Date(d);
    return isNaN(dt.getTime()) ? null : dt.toISOString().slice(0, 10);
};

const onSubmit = () => {
    validateField(formData.expenseAcct, 'expenseAcct');
    validateField(formData.amount, 'amount');
    if (commonService.validateRequiredFields(formError)) {
        commonService.showError(toast, 'Please fill in the missing fields.');
        return;
    }

    const postData = {
        expense_acct_no: formData.expenseAcct?.acct_no,
        amount: formData.amount,
        payment_method: paymentMethod.value.value,
        payee: formData.payee,
        description: formData.description,
        expense_date: fmtDate(formData.expenseDate),
        receipt: receipt.value
    };

    commonService.genericRequest('create-expense', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            formData.expenseAcct = null;
            formData.amount = null;
            formData.payee = null;
            formData.description = null;
            formData.expenseDate = new Date();
            receipt.value = null;
            paymentMethod.value = paymentOptions.value[0];
        } else {
            commonService.showError(toast, response.message);
        }
    });
};

onMounted(() => {
    fetchExpenseAccounts();
});
</script>

<template>
    <div>
        <div class="card">
            <h5>Record Expense</h5>
            <div class="grid p-fluid mt-3">
                <Toast />

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="expenseAcct" :options="expenseAccounts" v-model="formData.expenseAcct" filter
                            optionLabel="description" @blur="validateField(formData.expenseAcct, 'expenseAcct')"
                            :class="{ 'p-invalid': formError.expenseAcct }" />
                        <label for="expenseAcct">Expense Category</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="paymentMethod" :options="paymentOptions" v-model="paymentMethod" optionLabel="name" />
                        <label for="paymentMethod">Payment Method</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="amount" v-model="formData.amount"
                            @blur="validateField(formData.amount, 'amount')" :class="{ 'p-invalid': formError.amount }" />
                        <label for="amount">Amount</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="payee" v-model="formData.payee" />
                        <label for="payee">Payee / Vendor</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Calendar id="expenseDate" v-model="formData.expenseDate" dateFormat="yy-mm-dd" />
                        <label for="expenseDate">Date</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <FileUpload mode="basic" accept="image/*" :auto="true" customUpload chooseLabel="Attach Receipt"
                        @select="onReceiptSelect" />
                    <small v-if="receipt" class="text-green-600">Receipt attached</small>
                </div>

                <div class="field col-12">
                    <span class="p-float-label">
                        <Textarea inputId="description" rows="3" v-model="formData.description" />
                        <label for="description">Description</label>
                    </span>
                </div>

                <div class="field col-12 md:col-8"></div>
                <div class="field col-12 md:col-4">
                    <Button @click="onSubmit" label="Submit" class="p-button-outlined" />
                </div>
            </div>
        </div>
    </div>
</template>
