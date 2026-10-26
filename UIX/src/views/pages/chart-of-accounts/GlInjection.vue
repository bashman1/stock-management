<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

 const router = useRouter();
const name = ref(null);
const type = ref(null);
const institutionsData = ref(null);
const institution = ref(null);
const description = ref(null);
const glDrCatsList = ref(null);
const glCrCatsList = ref(null);

const drType = ref(null);
const drGlNo = ref(null);
const drAcctNo = ref(null);
const drDesc = ref(null);
const crType = ref(null);
const crGlNo = ref(null);
const crAcctNo = ref(null);
const crDesc = ref(null);

const drAcct = ref(null);
const crAcct = ref(null);
const drTyp = ref(null);
const crTyp = ref(null);
const drTitle = ref(null);
const crTitle = ref(null);
const tranAmt = ref(null);
const tranDt = ref(null);

const formError = ref({});

const glCatsList = ref(null);


const typeOptions = ref([
    { label: 'Admin Role', value: 'Admin' },
    { label: 'Institution Role', value: 'Institution' },
]);

const onSubmit = () => {
    formError.value.drAcct = commonService.validateFormField(drAcct.value);
    formError.value.crAcct = commonService.validateFormField(crAcct.value);
    formError.value.drTyp = commonService.validateFormField(drTyp.value);
    formError.value.crTyp = commonService.validateFormField(crTyp.value);
    formError.value.drTitle = commonService.validateFormField(drTitle.value);
    formError.value.crTitle = commonService.validateFormField(crTitle.value);
    formError.value.tranAmt = commonService.validateFormField(tranAmt.value);

    let invalid = commonService.validateRequiredFields(formError.value);
    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }
    let postData = {
        drAcctNo:drAcct.value,
        crAcctNo:crAcct.value,
        drAcctType:drTyp.value,
        crAcctType:crTyp.value,
        drTitle:drTitle.value,
        crTitle:crTitle.value,
        tranAmt:tranAmt.value,
        description:description.value,
        tranDate:tranDt.value,
        status:'Active',
    }
    commonService.genericRequest('debit-credit-acct', 'post', true, postData).then((response) => {
        if(response.status){
            commonService.showSuccess(toast,response.message);
            commonService.redirect(router, "/gl-accounts");
        }else{
            commonService.showError(toast,response.message);
        }
    })
}

const onInputBlur=(value, key)=>{
    formError.value[key] = commonService.validateFormField(value);
}


 const onDrSearch=()=>{
    let postData = {
        acct_type:drType?.value?.acct_type,
        gl_no:drGlNo?.value,
        description:drDesc?.value,
        acct_no:drAcctNo?.value,
        indicator:'DR'
    }

    getGlAccounts(postData);
 }


 const onCrSearch=()=>{
    let postData = {
        acct_type:crType?.value?.acct_type,
        gl_no:crGlNo?.value,
        description:crDesc?.value,
        acct_no:crAcctNo?.value,
        indicator:'CR'
    }
    getGlAccounts(postData);
 }

 const getGlAccounts = (postData) => {

    commonService.genericRequest('gl-search', 'post', true, postData).then((response) => {
        if (response.status) {
            if(postData?.indicator=='DR'){
                glDrCatsList.value = response.data;
            }else if(postData?.indicator=='CR'){
                glCrCatsList.value = response.data;
            }else{
                glCrCatsList.value = response.data;
                glDrCatsList.value = response.data;
            }
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getGlCats =()=>{
    commonService.genericRequest('gl-cats', 'get', true, {}).then((response) => {
        if (response.status) {
            glCatsList.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const debitAccount=(data)=>{
    drAcct.value = data.acct_no;
    drTyp.value = data.acct_type;
    drTitle.value = data.description;
    glDrCatsList.value=null;
}


const creditAccount=(data)=>{
    crAcct.value = data.acct_no;
    crTyp.value = data.acct_type;
    crTitle.value = data.description;
    glCrCatsList.value=null;
}

 onMounted(() => {
    getGlCats();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>Debit/Credit Ledger Accounts</h5>
            <div class="grid p-fluid mt-3">
                <Toast />
                <div class="col-12 md:col-6">
                    <div class="card">
                        <h5>Debit Account</h5>
                        <div class="grid">
                            <div class="field col-12 md:col-4">
                                <span class="p-float-label">
                                    <Dropdown id="institution" :options="glCatsList" v-model="drType" optionLabel="acct_type"></Dropdown>
                                    <label for="institution">Gl type</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-4">
                                <span class="p-float-label">
                                    <InputText type="text" id="name" v-model="drGlNo"  />
                                    <label for="firstName">Gl ledger no.</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-4">
                                <span class="p-float-label">
                                    <InputText type="text" id="name" v-model="drAcctNo"  />
                                    <label for="firstName">Gl acct. no.</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-8">
                                <span class="p-float-label">
                                    <InputText type="text" id="name" v-model="drDesc" />
                                    <label for="firstName">Name</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-4">
                                <Button @click="onDrSearch" label="Search" class="p-button-outlined mr-2 mb-2" />
                            </div>
                        </div>

                        <DataTable v-if="glDrCatsList" :value="glDrCatsList" size="small" :paginator="true" class="p-datatable-gridlines" :rows="3" dataKey="id"
                        selectionMode="single" :metaKeySelection="true" :rowHover="true"  v-model:selection="debitSelected"
                        filterDisplay="menu" responsiveLayout="scroll">
                        <Column field="name" header="Name" style="min-width: 10rem">

                            <template #body="{ data }">
                                {{ data.description }}
                            </template>
                        </Column>
                        <Column field="name" header="Acct No." style="min-width: 10rem">

                            <template #body="{ data }">
                                {{ data.acct_no }}
                            </template>
                        </Column>
                        <Column field="name" header="Acct Type" style="min-width: 10rem">

                            <template #body="{ data }">
                                {{ data.acct_type }}
                            </template>
                        </Column>
                        <Column field="name" header="Ledger No." style="min-width: 10rem">

                            <template #body="{ data }">
                                {{ data.gl_no }}
                            </template>
                        </Column>
                        <Column field="name" header="Acct Balance" style="min-width: 10rem">

                            <template #body="{ data }">
                                {{ data.balance }}
                            </template>
                        </Column>
                        <Column headerStyle="max-width:10rem;">
                            <template #body="{ data }">
                                <Button icon="pi pi-check" class="p-button-rounded p-button-success mr-2"
                                    @click="debitAccount(data)" />
                            </template>
                        </Column>
                    </DataTable>

                    </div>
                </div>
                <div class="col-12 md:col-6">
                    <div class="card">
                        <h5>Credit Account</h5>

                        <div class="grid">
                            <div class="field col-12 md:col-4">
                                <span class="p-float-label">
                                    <Dropdown id="institution" :options="glCatsList" v-model="crType" optionLabel="acct_type"></Dropdown>
                                    <label for="institution">Gl type</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-4">
                                <span class="p-float-label">
                                    <InputText type="text" id="name" v-model="crGlNo" /> <!-- class="p-invalid"-->
                                    <label for="firstName">Gl ledger no.</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-4">
                                <span class="p-float-label">
                                    <InputText type="text" id="name" v-model="crAcctNo"  /> <!-- class="p-invalid"-->
                                    <label for="firstName">Gl acct. no.</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-8">
                                <span class="p-float-label">
                                    <InputText type="text" id="name" v-model="crDesc"  /> <!-- class="p-invalid"-->
                                    <label for="firstName">Name</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-4">
                                <Button @click="onCrSearch" label="Search" class="p-button-outlined mr-2 mb-2" />
                            </div>
                        </div>

                        <DataTable v-if="glCrCatsList" :value="glCrCatsList" size="small" :paginator="true" class="p-datatable-gridlines" :rows="3" dataKey="id"
                        :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
                        <Column field="name" header="Name" style="min-width: 10rem">

                            <template #body="{ data }">
                                {{ data.description }}
                            </template>
                        </Column>
                        <Column field="name" header="Acct No." style="min-width: 10rem">

                            <template #body="{ data }">
                                {{ data.acct_no }}
                            </template>
                        </Column>
                        <Column field="name" header="Acct Type" style="min-width: 10rem">

                            <template #body="{ data }">
                                {{ data.acct_type }}
                            </template>
                        </Column>
                        <Column field="name" header="Ledger No." style="min-width: 10rem">

                            <template #body="{ data }">
                                {{ data.gl_no }}
                            </template>
                        </Column>
                        <Column field="name" header="Acct Balance" style="min-width: 10rem">

                            <template #body="{ data }">
                                {{ data.balance }}
                            </template>
                        </Column>
                        <Column headerStyle="max-width:10rem;">
                            <template #body="{ data }">
                                <Button icon="pi pi-check" class="p-button-rounded p-button-success mr-2"
                                    @click="creditAccount(data)" />
                            </template>
                        </Column>
                    </DataTable>
                    </div>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="drAcct" v-model="drAcct"  @blur="onInputBlur(drAcct, 'drAcct')" :class="{ 'p-invalid': formError?.drAcct }"/> <!-- class="p-invalid"-->
                        <label for="drAcct">Dr Acct. No.</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="drTyp" v-model="drTyp"  @blur="onInputBlur(drTyp, 'drTyp')" :class="{ 'p-invalid': formError?.drTyp }"/> <!-- class="p-invalid"-->
                        <label for="drTyp">Dr Acct. Type.</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="drTitle" v-model="drTitle"  @blur="onInputBlur(drTitle, 'drTitle')" :class="{ 'p-invalid': formError?.drTitle }"/> <!-- class="p-invalid"-->
                        <label for="drTitle">Dr Acct. Title.</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="crAcct" v-model="crAcct"  @blur="onInputBlur(crAcct, 'crAcct')" :class="{ 'p-invalid': formError?.crAcct }"/> <!-- class="p-invalid"-->
                        <label for="crAcct">Cr Acct. No.</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="crTyp" v-model="crTyp"  @blur="onInputBlur(crTyp, 'crTyp')" :class="{ 'p-invalid': formError?.crTyp }"/> <!-- class="p-invalid"-->
                        <label for="crTyp">Cr Acct. Type.</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="crTitle" v-model="crTitle"  @blur="onInputBlur(crTitle, 'crTitle')" :class="{ 'p-invalid': formError?.crTitle }"/> <!-- class="p-invalid"-->
                        <label for="crTitle">Cr Acct. Title.</label>
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
