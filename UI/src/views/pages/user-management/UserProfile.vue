<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';
const route = useRoute()

const toast = useToast();

const commonService = new CommonService();
const router = useRouter();
const userDetails = ref(null);
const assignedBranches = ref(null);
const selectedBrach = ref(null);
const oldPassword = ref(null);
const newPassword = ref(null);
const confirmPassword = ref(null);
const matchingPassword = ref(true);
const formError = ref({});

const getUserDetail=()=>{
    let postData={
        userId: route.params.id,
    }

    commonService.genericRequest('get-user-details', 'post', true, postData).then((response) => {
        if (response.status) {
            userDetails.value = response.data[0];
        } else {
                commonService.showError(toast, response.message);
        }
    })
}

const getAssignedUserBranches=()=>{
    let postData={
        status: "Active",
        user_id:route.params.id
    }
    commonService.genericRequest('get-assigned-user-branches', 'post', true, postData).then((response) => {
    if (response.status) {
        assignedBranches.value = response?.data
    } else {
        commonService.showError(toast, response.message);
    }
    })
}

const switchBranch=()=>{

    let previousData = commonService.getStorage();
    if(!selectedBrach.value){
        commonService.showError(toast, "Please Select a branch");
        return
    }
    let postData = {
        userId:route.params.id,
        branchId:selectedBrach.value.branch_id,
    }
    commonService.genericRequest('switch-branches', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            if (previousData?.userData?.id == response?.data?.id){
                const storedData = {
                    token: previousData?.token,
                    userData: response.data,
                };
                commonService.setStorage(storedData);
                router.go(0);
            }
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onInputBlur=(value, key)=>{
    formError.value[key] = commonService.validateFormField(value);
}

const checkMatchingPassword=()=>{
    if(newPassword.value === confirmPassword.value ){
        matchingPassword.value = true;
    }else{
        matchingPassword.value = false;
    }
}

const onPasswordReset=()=>{
    formError.value.oldPassword=commonService.validateFormField(oldPassword.value);
    formError.value.newPassword=commonService.validateFormField(newPassword.value);
    formError.value.confirmPassword=commonService.validateFormField(confirmPassword.value);

    let invalid = commonService.validateRequiredFields(formError.value);
    if(invalid || newPassword.value != confirmPassword.value){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData = {
        userId:route.params.id,
        oldPassword: oldPassword.value,
        newPassword: newPassword.value,
        confirmPassword: confirmPassword.value,
    }

    commonService.genericRequest('reset-password', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

onMounted(() => {
    getUserDetail();
    getAssignedUserBranches();
});


</script>

<template>
    <div class="grid">
        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5 class="text-500">User Details</h5>
                <div class="field">
<!--                    <p>Product Name: <span> {{userDetails?.name}}</span></p>-->
                    <label for="name1" class="text-900">Name: <span> {{userDetails?.first_name+' '+ userDetails?.last_name+' '+userDetails?.other_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Phone No.: <span> {{userDetails?.phone_number}}</span></label>
                </div>

                <div class="field">
                    <label for="name1" class="text-900">Gender: <span> {{userDetails?.gender}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Date of Birth: <span> {{userDetails?.date_of_birth}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Email: <span> {{userDetails?.email}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">City: <span> {{userDetails?.city}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Address: <span> {{userDetails?.address}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Street: <span> {{userDetails?.street}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">P.O Box: <span> {{userDetails?.p_o_box}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Status: <span> {{userDetails?.status}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Role: <span> {{userDetails?.role}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">User Type: <span> {{userDetails?.user_type}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">User Category: <span> {{userDetails?.user_category}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Institution: <span> {{userDetails?.institution}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Branch: <span> {{userDetails?.branch}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Description: <span> {{userDetails?.description}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Created By: <span> {{userDetails?.created_by}}</span></label>
                </div>
            </div>
        </div>

        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5 class="text-500">Reset Password</h5>
                <div class="grid p-fluid mt-3">

                    <div class="field col-12 md:col-12">
                        <span class="p-float-label">
                            <Password type="text" id="password" v-model="oldPassword" @blur="onInputBlur(oldPassword, 'oldPassword')"
                                :class="{ 'p-invalid': formError?.oldPassword }"  :toggleMask="true"/>
                            <label for="password">Old Password</label>
                        </span>
                    </div>

                    <div class="field col-12 md:col-12">
                        <span class="p-float-label">
                            <Password type="text" id="password"  @keyup="checkMatchingPassword" v-model="newPassword" @blur="onInputBlur(newPassword, 'newPassword')"
                                :class="{ 'p-invalid': formError?.newPassword || !matchingPassword}"  :toggleMask="true"/>
                            <label for="password">New Password</label>
                        </span>
                    </div>

                    <div  class="field col-12 md:col-12">
                        <span class="p-float-label">
                            <Password type="text" id="confirmPassword" @keyup="checkMatchingPassword" v-model="confirmPassword"
                                @blur="onInputBlur(confirmPassword, 'confirmPassword')"
                                :class="{ 'p-invalid': formError?.confirmPassword || !matchingPassword  }"  :toggleMask="true"/>
                            <label for="confirmPassword">Confirm Password</label>
                        </span>
                    </div>
                    <div class="field col-12 md:col-8"></div>
                    <div class="field col-12 md:col-4">
                        <Button @click="onPasswordReset" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                    </div>
                </div>

            </div>

            <div class="card">
                <h5 class="text-500">Switch Branch</h5>

                <div class="grid p-fluid mt-3">
                    <div class="field col-12 md:col-12">
                        <span class="p-float-label">
                            <Dropdown id="instCity" :options="assignedBranches" v-model="selectedBrach" optionLabel="name"></Dropdown>
                            <label for="instCity">Select Branch</label>
                        </span>
                    </div>
                    <div class="field col-12 md:col-8"></div>
                    <div class="field col-12 md:col-4">
                        <Button @click="switchBranch" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
