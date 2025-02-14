<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();

const expenseAccts = ref([]);
const drAcctList = ref([]);
const crAcctList = ref([]);

const paymentOptions = ref([
    {id: 1, name:"Cash"},
    {id: 2, name:"Bank"},
])
const paymentMethod = ref({id: 1, name:"Cash"})


// const getExpenseAcct=()=>{

// }

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
                        <Dropdown id="institution" :options="drAcctList" v-model="drType" optionLabel="description"></Dropdown>
                        <label for="institution">Expense Account</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="institution" :options="paymentOptions" v-model="paymentMethod" optionLabel="name"></Dropdown>
                        <label for="institution">Payment Method</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="institution" :options="crAcctList" v-model="drType" optionLabel="description"></Dropdown>
                        <label for="institution">{{ paymentMethod.name }} Account</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="tranAmt" v-model="tranAmt"  @blur="onInputBlur(tranAmt, 'tranAmt')" :class="{ 'p-invalid': formError?.tranAmt }"/> <!-- class="p-invalid"-->
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
                        <Textarea inputId="description" rows="3" cols="30" v-model="description"></Textarea>
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
</template>
