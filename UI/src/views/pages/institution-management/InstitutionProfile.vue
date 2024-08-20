<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';
const route = useRoute()

const toast = useToast();

const commonService = new CommonService();
const router = useRouter();
const institutionDetails = ref(null);
const assignedBranches = ref([]);
const selectedUser = ref({});
const assignUserBranchModal = ref(false);

const getProductDetail=()=>{
    let postData={
        institutionId: route.params.id,
        status: 'Active',
    }
    commonService.genericRequest('get-institution-profile', 'post', true, postData).then((response) => {
         if (response.status) {
            institutionDetails.value = response?.data[0];
         } else {
             commonService.showError(toast, response.message);
         }
     })
}

const goaToDetails=(data)=>{
    commonService.redirect(router, "/user-profile/" + data?.id)
}

const openModal=(data)=>{
    getAssignedUserBranches(data.id)
    selectedUser.value = data;
    assignUserBranchModal.value = true;
    assignedBranches.value=[];

}

const submitAssignedBranches=()=>{
    if(!assignedBranches.value || assignedBranches.value.length === 0){
        commonService.showError(toast, "Please Select at least a branch");
        return
    }

    let postData = {
        user:selectedUser.value.id,
        branches: assignedBranches.value,
    }

    commonService.genericRequest('assign-user-branches', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            assignUserBranchModal.value = false;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getAssignedUserBranches=(userId)=>{
    let postData={
        status: "Active",
        user_id:userId
    }
    commonService.genericRequest('get-assigned-user-branches', 'post', true, postData).then((response) => {
    if (response.status) {
        assignedBranches.value = institutionDetails?.value?.branches.filter(element =>
            response?.data.some(branch => branch.branch_id === element.id)
        );
    } else {
        commonService.showError(toast, response.message);
    }
    })
}


onMounted(() => {
    getProductDetail();
});


</script>

<template>
    <div class="grid">
        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5 class="text-500">Institution Details</h5>
                <!-- <Toast /> -->
                <div class="field">
                    <label for="name1" class="text-900">Name: <span> {{institutionDetails?.name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Ref No.: <span> {{institutionDetails?.ref_no}}</span></label>
                </div>

                <div class="field">
                    <label for="name1" class="text-900">Type: <span> {{institutionDetails?.type_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Status: <span> {{institutionDetails?.status}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">P.O Box: <span> {{institutionDetails?.p_o_box}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Start Date: <span> {{institutionDetails?.created_at}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Tin: <span> {{institutionDetails?.tin}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Ref. No: <span> {{institutionDetails?.ref_no}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Description: <span> {{institutionDetails?.description}}</span></label>
                </div>
            </div>

            <div class="card p-fluid">
                <h5 class="text-500">Address</h5>
                    <div class="field">
                        <label class="text-900" for="name1">City: <span> {{institutionDetails?.city_name}}</span></label>
                    </div>

                    <div class="field">
                        <label class="text-900" for="name1">Address: <span> {{institutionDetails?.address}}</span></label>
                    </div>
            </div>
        </div>

        <div class="col-12 md:col-6">

            <div class="card p-fluid">
                <h5 class="text-500">Branches</h5>
                <div class="field"  v-for="(branch, index) in institutionDetails?.branches" :key="index">
                    <label class="text-900" for="name1"><span> {{index+1 +'. '+ branch?.name+', '+branch?.address}}</span></label>
                </div>
            </div>

            <div class="card">
                <h5 class="text-500">Bank Info</h5>
                <div class="field">
                    <label class="text-900" for="name1">Bank: <span> {{institutionDetails?.bank_name}}</span></label>
                </div>

                <div class="field">
                    <label class="text-900" for="name1">Name: <span> {{institutionDetails?.acct_name}}</span></label>
                </div>

                <div class="field">
                    <label class="text-900" for="name1">Number: <span> {{institutionDetails?.acct_number}}</span></label>
                </div>
            </div>


        </div>
        <div class="col-12 md:col-12">
        <div class="card">
                <h5 class="text-500">Users</h5>
               <DataTable :value="institutionDetails?.users"  size="small" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">

                    <Column field="name" header="Name" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.first_name+' '+data.last_name+' '+data.other_name }}
                        </template>
                    </Column>
                    <Column header="Phone No." style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.phone_number }}
                        </template>
                    </Column>
                    <Column header="Email" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.email }}
                        </template>
                    </Column>
                    <Column header="Branch" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.branch }}
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
                    <Column headerStyle="min-width:10rem;">
                        <template #body="{ data }">
                            <Button icon="pi pi-eye" @click="goaToDetails(data)" class="p-button-primary mr-2"  v-tooltip="'View user details'" />
                            <Button icon="pi pi-arrow-right-arrow-left" @click="openModal(data)" class="p-button-success mr-2"  v-tooltip="'Assign users branches'" />
                            <!-- <Button icon="pi pi-trash" class="p-button-danger mr-2" @click="editInstitution(data)"  v-tooltip="'Archive user'" /> -->
                        </template>
                    </Column>
                </DataTable>



            <Dialog :header=" selectedUser.first_name+' '+selectedUser.last_name+' '+selectedUser.other_name" v-model:visible="assignUserBranchModal" :style="{ width: '30vw' }" :modal="true"
                class="p-fluid">
                <p style="padding-top: 20px;">
                <div class="grid p-fluid">
                    <div class="field col-12 md:col-12">
                        <span class="p-float-label">
                                <MultiSelect id="multiselect" v-model="assignedBranches" :options="institutionDetails?.branches" optionLabel="name"></MultiSelect>
                            <label for="subCatCat">Select Branches</label>
                        </span>
                    </div>
                </div>
                </p>
                <template #footer>
                    <Button label="Cancel" @click="assignUserBranchModal = false" icon="pi pi-times"
                        class="p-button-outlined p-button-danger mr-2 mb-2" />
                    <Button @click="submitAssignedBranches" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                </template>
            </Dialog>


            </div>
        </div>
    </div>
</template>
