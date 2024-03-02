<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';
const router = useRouter();
const route = useRoute()
const toast = useToast();
const commonService = new CommonService();
const commissions = ref(null);
const selectedTransactions = ref(null);
const showPhoneNoModal=ref(false);
const formError = ref({});
const phoneNo = ref(null);
const verifiedName = ref(null);

const getCommissions=()=>{
     commonService.genericRequest('get-commissions', 'get', true, {}).then((response) => {
         if (response.status) {
            commissions.value = response.data;
         } else {
             commonService.showError(toast, response.message);
         }
     })
 }

 const approvePayment=(action)=>{
    let postData ={action:action, phoneNo:phoneNo.value}
    verifiedName.value = null;
    commonService.genericRequest('test-momo-api', 'post', false, postData).then((response) => {
         if (response.status) {
            if(action == 'VERIFY_NAME'){
                verifiedName.value = 'Name: '+response.data.name+' '+response.data.given_name;
            }else{
                verifiedName.value='Payment initiated successfully. Waiting to approval to complete the transaction';
            }
           
         } else {
             commonService.showError(toast, response.message);
         }
     })
 }

 const toggleModal = (action) => {
    showPhoneNoModal.value = action;
}

const onInputBlur=(value, key)=>{
    formError.value[key] = commonService.validateFormField(value);
}

onMounted(() => {
    getCommissions()
});


</script>
<template>
    <div class="grid">
        <div class="col-12">
            <div className="card">
                <h5>View Commission</h5>
                <Toast />
                <!-- <p>Use this page to start from scratch and place your custom content.</p> -->
                <Toolbar class="mb-4">
                    <template v-slot:start>
                        <div class="my-2">
<!--                            <Button label="New" icon="pi pi-plus" class="p-button-success mr-2" @click="openNew" />-->
<img src="/demo/images/mtnmomo.svg" alt="Image" height="50"  />
                            <Button label="Approve Payment" icon="pi pi-check" class="p-button-success" @click="toggleModal(true)" :disabled="!selectedTransactions || !selectedTransactions.length" />
                        </div>
                    </template>

                    <template v-slot:end>
<!--                        <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" chooseLabel="Import" class="mr-2 inline-block" />-->
<!--                        <Button label="Export" icon="pi pi-upload" class="p-button-help" @click="exportCSV($event)" />-->
                    </template>
                </Toolbar>

                <DataTable :value="commissions" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll" v-model:selection="selectedTransactions"
                    :rowsPerPageOptions="[5, 10, 25]">
                    <Column field="name" header="Name" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.institution_name }}
                        </template>
                    </Column>
                    <Column header="Rate" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.rate }}
                        </template>
                    </Column>
                    <Column header="Commission" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.commision }}
                        </template>
                    </Column>
                    <Column header="Member" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.member_name }}
                        </template>
                    </Column>
                    <Column header="Officer" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.officer_name }}
                        </template>
                    </Column>
                    <Column header="Tran Id" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.tran_id }}
                        </template>
                    </Column>
                    <Column header="Phone Number" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.phone_number }}
                        </template>
                    </Column>
                    <Column header="Status" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.status }}
                        </template>
                    </Column>
                    <Column header="Date" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.created_at }}
                        </template>
                    </Column>
                    <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                </DataTable>

                <Dialog header="Sales Details" v-model:visible="showPhoneNoModal" :style="{ width: '33vw' }" :modal="true"
                class="p-fluid">
                <p style="padding: 20px;">
                <div class="grid p-fluid">
                    <div class="field col-12 md:col-12">
                        <img src="/demo/images/mtnmomo.svg" alt="Image" height="50" class="mb-3" />
                        <span class="p-float-label">
                            <InputText type="text" id="phoneNo" @blur="onInputBlur(phoneNo, 'phoneNo')" v-model="phoneNo" :class="{ 'p-invalid': formError?.phoneNo }" />
                            <label for="unitName">MTN phone number</label>
                        </span>
                    </div>
                    <div class="field col-12 md:col-12"  v-if="verifiedName">
                         {{ verifiedName }}
                    </div>
                </div>
                </p>
                <template #footer>
                    <Button label="Cancel" v-if="!phoneNo" @click="toggleModal(false)" icon="pi pi-times"
                        class="p-button-outlined p-button-danger mr-2 mb-2" />
                    <Button v-if="!verifiedName" @click="approvePayment('VERIFY_NAME')" label="NEXT" class="p-button-outlined mr-2 mb-2" />
                    <Button v-if="verifiedName" @click="approvePayment('REQUEST_PAYMENT')" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                </template>
            </Dialog>

            </div>
        </div>
    </div>
</template>
