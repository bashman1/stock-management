<script setup>
import {ref, onMounted} from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

const router = useRouter();
const searchKey = ref(null);
const mName = ref(null);
const mNumber = ref(null);
const mContact = ref(null);
const tranAmt = ref(null);
const tType = ref(null);
const tDate = ref(null);
const description = ref(null);
const membersData = ref(null);
const membersId  = ref(null);
const selectedMember = ref(null);
const transactionType=ref([
    {label:"Deposit", value: "DP"},
])


const getMembers=(keyWord)=>{
    let postData={
        status:"Active",
        searchKey:keyWord
    }
    commonService.genericRequest('get-members-by-institution', 'post', true, postData).then((response)=>{
        if(response.status){
            membersData.value = response.data
        }else{

        }
    })
}

const checkValue=()=>{
    getMembers(searchKey.value)
}

const SelectedMember=(event)=>{
    searchKey.value=null;
    selectedMember.value=event;
    membersId.value=event.id;
    mName.value=event.name;
    mNumber.value=event.member_number;
    mContact.value=event.phone_number;
}

const onCancel=()=>{
    searchKey.value=null;
    selectedMember.value=null;
    membersData.value=null;
}

const onSubmit=()=>{
    let postData={
        name:mName.value,
        member_number:mNumber.value,
        member_contact:mContact.value,
        amount:tranAmt.value,
        tran_type:tType.value.value,
        tran_date:tDate.value,
        description:description.value,
        member_id:membersId.value,
    }
    commonService.genericRequest('collect-deposit', 'post', true, postData).then((response)=>{
        if(response.status){
            commonService.showSuccess(toast,response.message);
        }else{
            commonService.showError(toast,response.message);
        }
    })
}

onMounted(() => {
    // getMembers();
});
</script>

<template>
    <div className="card">
        <h5>Collect Money</h5>
        <Toast/>
        <div class="grid p-fluid mt-3">
            <div class="col-12 md:col-12">
                <div class="p-inputgroup">
                    <InputText placeholder="Search by Member name or number" v-model="searchKey" />
                    <Button @click="checkValue" :disabled="searchKey==null" label="Search" />
                </div>
            </div>
            <div class="field col-12 md:col-12" v-if="!selectedMember">
                <DataTable :value="membersData" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                           :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
                    <Column field="name" header="Name" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                    </Column>
                    <Column header="Member No." style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.member_number }}
                        </template>
                    </Column>
                    <Column header="Phone Number" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.phone_number }}
                        </template>
                    </Column>
                    <Column header="Alt. Member No." style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.alt_member_number }}
                        </template>
                    </Column>
                    <Column header="Select" headerStyle="min-width:5rem;">
                        <template #body="{ data }">
                            <Button icon="pi pi-check" class="p-button-rounded p-button-success mr-2" @click="SelectedMember(data)" />
                        </template>
                    </Column>
                </DataTable>
            </div>
            <div class="field col-12 md:col-4"  v-if="selectedMember">
                    <span class="p-float-label">
                        <InputText type="text" id="name" v-model="mName" disabled="true"/>  <!-- class="p-invalid"-->
                        <label for="name">Member Name</label>
                    </span>
            </div>
            <div class="field col-12 md:col-4" v-if="selectedMember">
                    <span class="p-float-label" >
                        <InputText type="text" id="number" v-model="mNumber"  disabled="true" />  <!-- class="p-invalid"-->
                        <label for="number">Member Number</label>
                    </span>
            </div>
            <div class="field col-12 md:col-4" v-if="selectedMember">
                    <span class="p-float-label">
                        <InputText type="text" id="contact" v-model="mContact" disabled="true"  />  <!-- class="p-invalid"-->
                        <label for="contact">Member Contact</label>
                    </span>
            </div>
            <div class="field col-12 md:col-4" v-if="selectedMember">
                    <span class="p-float-label">
                        <InputText type="text" id="amount" v-model="tranAmt"  />  <!-- class="p-invalid"-->
                        <label for="amount">Amount</label>
                    </span>
            </div>
            <div class="field col-12 md:col-4" v-if="selectedMember">
                <span class="p-float-label">
                        <Dropdown id="transactionType"  :options="transactionType" v-model="tType" optionLabel="label"></Dropdown>
                        <label for="transactionType">Transact type</label>
                    </span>
            </div>
            <div class="field col-12 md:col-4" v-if="selectedMember">
                    <span class="p-float-label">
                        <Calendar id="date" v-model="tDate"></Calendar>
                        <label for="date">Date</label>
                    </span>
            </div>
            <div class="field col-12 md:col-12" v-if="selectedMember">
                    <span class="p-float-label">
                        <Textarea  id="textarea" rows="3" cols="30" v-model="description"></Textarea>
                        <label for="textarea">Description</label>
                    </span>
            </div>

            <div class="field col-12 md:col-6"></div>
            <div class="field col-12 md:col-2" v-if="selectedMember">
                <Button @click="onCancel" label="CANCEL" class="p-button-outlined p-button-danger mr-2 mb-2" />
            </div>
            <div class="field col-12 md:col-4" v-if="selectedMember">
                <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </div>

        </div>
    </div>
</template>
