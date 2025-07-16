<!-- <script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();

const drAcctList = ref([]);
const crAcctList = ref([]);

const paymentOptions = ref([
    {id: 1, name:"Cash"},
    {id: 2, name:"Bank"},
])
const paymentMethod = ref({id: 1, name:"Cash"})
const drAcct = ref(null)
const crAcct = ref(null)
const tranAmt = ref(null)
const tranDt = ref(new Date())
const description = ref(null)
const formError = ref({});

const getGlAccounts = (postData) => {

commonService.genericRequest('gl-search', 'post', true, postData).then((response) => {
    if (response.status) {
        if(postData?.indicator=='DR'){
            drAcctList.value = response.data;
        }else if(postData?.indicator=='CR'){
            crAcctList.value = response.data;
        }else{
            drAcctList.value = response.data;
            crAcctList.value = response.data;
        }
    } else {
        commonService.showError(toast, response.message);
    }
})
}

const getDrAccount=()=>{
    const postData = {
        acct_type:'EXPENSE',
        gl_no:null,
        description:null,
        acct_no:null,
        indicator:'DR'
    }
    getGlAccounts(postData)
}


const onPaymentMethodChange=()=>{
    let glType = '1301000'
    if(paymentMethod.value.name == 'Bank'){
        glType = '1302000'
    }
    const postData = {
        acct_type:null,
        gl_no:null,
        description:null,
        acct_no:null,
        indicator:'CR',
        gl_type_no: glType
    }
    getGlAccounts(postData)
}

const onInputBlur=(value, key)=>{
    formError.value[key] = commonService.validateFormField(value);
}

const onSubmit=()=>{
    formError.value.tranAmt = commonService.validateFormField(tranAmt.value);
    formError.value.drAcct = commonService.validateFormField(drAcct.value);
    formError.value.crAcct = commonService.validateFormField(crAcct.value);
    formError.value.description = commonService.validateFormField(description.value);
    let invalid = commonService.validateRequiredFields(formError.value);
    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }
    let postData = {
        drAcctNo:drAcct.value.acct_no,
        crAcctNo:crAcct.value.acct_no,
        drAcctType:drAcct.value.acct_type,
        crAcctType:crAcct.value.acct_type,
        drTitle:drAcct.value.description,
        crTitle:crAcct.value.description,
        tranAmt:tranAmt.value,
        description:description.value,
        tranDate:tranDt.value,
        status:'Active',
    }
    commonService.genericRequest('debit-credit-acct', 'post', true, postData).then((response) => {
        if(response.status){
            commonService.showSuccess(toast,response.message);
            drAcct.value = null;
            crAcct.value = null;
            tranAmt.value = null;
            description.value = null;
            tranDate.value = new Date();
        }else{
            commonService.showError(toast,response.message);
        }
    })
}
 onMounted(() => {
    getDrAccount();
    onPaymentMethodChange();
});

</script>

<template>
    <div>
        <div class="card">
            <h5>Expenses</h5>
            <div class="grid p-fluid mt-3">
                <Toast />

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="institution" :options="drAcctList" v-model="drAcct" optionLabel="description" @blur="onInputBlur(drAcct, 'drAcct')" :class="{ 'p-invalid': formError?.drAcct }"></Dropdown>
                        <label for="institution">Expense Account</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="institution" :options="paymentOptions" v-model="paymentMethod" optionLabel="name" @blur="onInputBlur(paymentMethod, 'paymentMethod')" :class="{ 'p-invalid': formError?.paymentMethod }"></Dropdown>
                        <label for="institution">Payment Method</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="institution" :options="crAcctList" v-model="crAcct" optionLabel="description" @blur="onInputBlur(crAcct, 'crAcct')" :class="{ 'p-invalid': formError?.crAcct }"></Dropdown>
                        <label for="institution">{{ paymentMethod.name }} Account</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="tranAmt" v-model="tranAmt"  @blur="onInputBlur(tranAmt, 'tranAmt')" :class="{ 'p-invalid': formError?.tranAmt }"/>
                        <label for="tranAmt">Amount</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Calendar id="tranDt" v-model="tranDt"></Calendar>
                        <label for="tranDt">Date</label>
                    </span>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="description" rows="3" cols="30" v-model="description" @blur="onInputBlur(description, 'description')" :class="{ 'p-invalid': formError?.description }"></Textarea>
                        <label for="description">Description</label>
                    </span>
                </div>
                <div class="field col-12 md:col-8"></div>
                <div class="field col-12 md:col-4">
                    <Button @click="onSubmit" label="Submit" class="p-button-outlined mr-2 mb-2" />
                </div>
            </div>
        </div>
    </div>
</template> -->


<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();

const drAcctList = ref([]);
const crAcctList = ref([]);

const paymentOptions = ref([
    { id: 1, name: "Cash" },
    { id: 2, name: "Bank" }
]);

const paymentMethod = ref(paymentOptions.value[0]); // Default to Cash

const formData = reactive({
    drAcct: null,
    crAcct: null,
    tranAmt: null,
    tranDt: new Date(),
    description: null
});

const formError = reactive({});

// Compute GL Type based on payment method
const glType = computed(() => (paymentMethod.value.name === "Bank" ? "1302000" : "1301000"));

// Fetch GL Accounts
const fetchGlAccounts = (indicator, acctType = null, glTypeNo = null) => {
    const postData = {
        acct_type: acctType,
        gl_no: null,
        description: null,
        acct_no: null,
        indicator,
        ...(glTypeNo && { gl_type_no: glTypeNo }) // Only include if provided
    };

    commonService.genericRequest('gl-search', 'post', true, postData).then(response => {
        if (response.status) {
            if (indicator === 'DR') {
                drAcctList.value = response.data;
            } else {
                crAcctList.value = response.data;
            }
        } else {
            commonService.showError(toast, response.message);
        }
    });
};
// Handle input validation
const validateField = (value, key) => {
    formError[key] = commonService.validateFormField(value);
};
// Form Submission
const onSubmit = () => {
    console.log(formData.crAcct)
    validateField(formData.drAcct, 'drAcct')
    validateField(formData.crAcct, 'crAcct')
    validateField(formData.tranAmt, 'tranAmt')
    validateField(formData.description, 'description')
    const invalid = commonService.validateRequiredFields(formError);

    if (invalid) {
        commonService.showError(toast, "Please fill in the missing fields.");
        return;
    }
    const postData = {
        drAcctNo: formData.drAcct?.acct_no,
        crAcctNo: formData.crAcct?.acct_no,
        drAcctType: formData.drAcct?.acct_type,
        crAcctType: formData.crAcct?.acct_type,
        drTitle: formData.drAcct?.description,
        crTitle: formData.crAcct?.description,
        tranAmt: formData.tranAmt,
        description: formData.description,
        tranDate: formData.tranDt,
        status: 'Active'
    };
    commonService.genericRequest('debit-credit-acct', 'post', true, postData).then(response => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            Object.keys(formData).forEach(key => (formData[key] = key === "tranDt" ? new Date() : null));
        } else {
            commonService.showError(toast, response.message);
        }
    });
};

// Watch for payment method changes
watch(paymentMethod, () => {
    fetchGlAccounts('CR', null, glType.value);
});

// Fetch data on mount
onMounted(() => {
    fetchGlAccounts('DR', 'EXPENSE');
    fetchGlAccounts('CR', null, glType.value);
});
</script>

<template>
    <div>
        <div class="card">
            <h5>Expenses</h5>
            <div class="grid p-fluid mt-3">
                <Toast />

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="drAcct" :options="drAcctList" v-model="formData.drAcct" optionLabel="description"
                            @blur="validateField(formData.drAcct, 'drAcct')" :class="{ 'p-invalid': formError.drAcct }" />
                        <label for="drAcct">Expense Account</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="paymentMethod" :options="paymentOptions" v-model="paymentMethod" optionLabel="name"
                            @blur="validateField(paymentMethod, 'paymentMethod')"
                            :class="{ 'p-invalid': formError.paymentMethod }" />
                        <label for="paymentMethod">Payment Method</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="crAcct" :options="crAcctList" v-model="formData.crAcct" optionLabel="description"
                            @blur="validateField(formData.crAcct, 'crAcct')" :class="{ 'p-invalid': formError.crAcct }" />
                        <label for="crAcct">{{ paymentMethod.name }} Account</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="tranAmt" v-model="formData.tranAmt"
                            @blur="validateField(formData.tranAmt, 'tranAmt')" :class="{ 'p-invalid': formError.tranAmt }" />
                        <label for="tranAmt">Amount</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Calendar id="tranDt" v-model="formData.tranDt" />
                        <label for="tranDt">Date</label>
                    </span>
                </div>

                <div class="field col-12">
                    <span class="p-float-label">
                        <Textarea inputId="description" rows="3" cols="30" v-model="formData.description"
                            @blur="validateField(formData.description, 'description')"
                            :class="{ 'p-invalid': formError.description }" />
                        <label for="description">Description</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4 offset-md-8">
                    <Button @click="onSubmit" label="Submit" class="p-button-outlined" />
                </div>
            </div>
        </div>
    </div>
</template>
