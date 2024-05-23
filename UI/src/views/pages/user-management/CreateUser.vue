<script setup>
 import {ref, onMounted} from 'vue';
 import { useRouter } from 'vue-router';
 import CommonService from '@/service/CommonService'
 import { useToast } from 'primevue/usetoast';

const toast = useToast();

 const commonService = new CommonService();

 const router = useRouter();
 const fName = ref(null);
 const lName = ref(null);
 const oName = ref(null);
 const email = ref(null);
 const phoneNumber = ref(null);
 const dob = ref(null);
 const gender = ref(null);
 const address = ref(null);
 const city = ref(null);
 const street = ref(null);
 const pOBox = ref(null);
 const description = ref(null);
 const password = ref(null);
 const confirmPassword = ref(null);
 const cities= ref(null);
 const type = ref(null);
 const institution= ref(null);
 const branch=ref(null);
 const institutionsData=ref(null);
 const branchData = ref(null);
 const role = ref(null);
 const rolesData=ref(null);
 const category =ref(null);
 const formError = ref({});

 const genderOptions = ref([
    { label: 'Male', value: 'Male' },
    { label: 'Female', value: 'Female' },
]);

const typeOptions = ref([
    { label: 'Super Admin', value: 'Admin' },
    { label: 'Institution Admin', value: 'Institution' },
]);

const categoryOptions =  ref([
    {label: 'Branch Admin', value:'BranchAdmin'},
    {label: 'Institution Admin', value:'InstitutionAdmin'}
])

 const onSubmit=()=>{

    formError.value.fName = commonService.validateFormField(fName.value);
    formError.value.lName = commonService.validateFormField(lName.value);
    formError.value.email = commonService.validateFormField(email.value);
    formError.value.type = commonService.validateFormField(type.value);
    formError.value.role = commonService.validateFormField(role.value);
    formError.value.password = commonService.validateFormField(password.value);
    formError.value.confirmPassword = commonService.validateFormField(confirmPassword.value);

    let invalid = commonService.validateRequiredFields(formError.value);
    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData = {
        first_name:fName?.value,
        last_name: lName?.value,
        other_name:oName?.value,
        email: email?.value,
        phone_number:phoneNumber?.value,
        date_of_birth:dob?.value,
        gender:gender?.value?.value,
        address:address?.value,
        city_id:city.value?.id,
        street:street?.value,
        p_o_box:pOBox?.value,
        description:description?.value,
        password:password?.value,
        confirm_password:confirmPassword?.value,
        status:'Active',
        user_type: type?.value?.value,
        user_category:category.value!=null?category.value.value:null,
        institution_id:institution.value!=null?institution?.value.id:null,
        role_id:role.value?.id,
        branch_id:branch.value!=null?branch.value.id:null,

    }
    commonService.genericRequest('create-user', 'post', true, postData).then((response)=>{
        if(response.status){
            commonService.showSuccess(toast,response.message);
            commonService.redirect(router, "/view-users");
            // router.push("/view-users");
        }else{
            commonService.showError(toast,response.message);
        }
    })
 }

 const getCities=()=>{
    commonService.genericRequest('get-city-county-id/'+1, 'get', true, {}).then((response)=>{
        if(response.status){
            cities.value = response.data
        }else{

        }
    })
}


 const getBranches=(id)=>{
    let postData={
        institutionId:id,
        status:'Active',
    }
    commonService.genericRequest('get-institution-branches', 'post', true, postData).then((response)=>{
        if(response.status){
            branchData.value = response.data
        }else{

        }
    })
}


const getRoles=(id)=>{
    let postData={
        institutionId:id,
        status:'Active',
    }
    commonService.genericRequest('get-institution-roles', 'post', true, postData).then((response)=>{
        if(response.status){
            rolesData.value = response.data
        }else{

        }
    })
}



const getAdminRoles=(type)=>{
    let postData={
        roleType:type,
        status:'Active',
    }
    commonService.genericRequest('get-role-type', 'post', true, postData).then((response)=>{
        if(response.status){
            rolesData.value = response.data
        }else{

        }
    })
}



 const getInstitution=()=>{
    commonService.genericRequest('get-institutions', 'get', true, {}).then((response)=>{
        if(response.status){
            institutionsData.value = response.data
        }else{

        }
    })
 }

 const onInstitutionChange=(event)=>{
    getBranches(event.value.id);
    getRoles(event.value.id)
}

const onInputBlur=(value, key)=>{
    formError.value[key] = commonService.validateFormField(value);
}

const roleChange=(event)=>{
    if(event.value.value == 'Admin'){
        getAdminRoles(event.value.value);
        category.value='SuperAdmin'
    }else{
        rolesData.value=[];
    }
}


onMounted(() => {
    getInstitution();
    getCities();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>Create User</h5>
            <div class="grid p-fluid mt-3">
                <Toast/>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="fName" @blur="onInputBlur(fName, 'fName')" :class="{ 'p-invalid': formError?.fName }" />  <!-- class="p-invalid"-->
                        <label for="firstName">First Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="lastName" v-model="lName" @blur="onInputBlur(lName, 'lName')" :class="{ 'p-invalid': formError?.lName }" />  <!-- class="p-invalid"-->
                        <label for="lastName">Last Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="otherName" v-model="oName"  />  <!-- class="p-invalid"-->
                        <label for="otherName">Other Names</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="email" v-model="email" @blur="onInputBlur(email, 'email')" :class="{ 'p-invalid': formError?.email }" />  <!-- class="p-invalid"-->
                        <label for="email">Email</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="phoneNumber" v-model="phoneNumber"  />  <!-- class="p-invalid"-->
                        <label for="phoneNumber">Phone Number</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Calendar inputId="dob" v-model="dob"></Calendar>
                        <label for="dob">Date Of Birth</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="gender" :options="genderOptions" v-model="gender" optionLabel="label"></Dropdown>
                        <label for="gender">Gender</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="type" @change="roleChange" :options="typeOptions" v-model="type" optionLabel="label" @blur="onInputBlur(type, 'type')" :class="{ 'p-invalid': formError?.type }"></Dropdown>
                        <label for="type">Type</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4" v-if="type?.value =='Institution'"> <!--v-on:change="onInstitutionChange($event)"-->
                    <span class="p-float-label">
                        <Dropdown id="institution" @change="onInstitutionChange"  :options="institutionsData" v-model="institution" optionLabel="name"></Dropdown>
                        <label for="institution">Institution</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4" v-if="type?.value =='Institution'">
                    <span class="p-float-label">
                        <Dropdown id="institution"  :options="branchData" v-model="branch" optionLabel="name"></Dropdown>
                        <label for="institution">Branch</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4" v-if="type?.value =='Institution'">
                    <span class="p-float-label">
                        <Dropdown id="institution"  :options="categoryOptions" v-model="category" optionLabel="label"></Dropdown>
                        <label for="institution">User Category</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4" >
                    <span class="p-float-label">
                        <Dropdown id="institution"  :options="rolesData" v-model="role" optionLabel="name" @blur="onInputBlur(role, 'role')" :class="{ 'p-invalid': formError?.role }"></Dropdown>
                        <label for="institution">Role</label>
                    </span>
                </div>

                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="textarea" rows="3" cols="30" v-model="address"></Textarea>
                        <label for="textarea">Address</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="instCity" :options="cities" v-model="city" optionLabel="name"></Dropdown>
                        <label for="instCity">City</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="instStreet" v-model="street" />
                        <label for="instStreet">Street</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="instPOBox" v-model="pOBox" />
                        <label for="instPOBox">P.O Box</label>
                    </span>
                </div>


                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="instDescription" rows="3" cols="30" v-model="description"></Textarea>
                        <label for="instDescription">More Info</label>
                    </span>
                </div>
                <!-- <div class="field col-12 md:col-2">
                    <h5>Attachment</h5>
                    <FileUpload mode="basic" name="demo[]" accept="image/*" :maxFileSize="1000000" @uploader="onUpload" customUpload />

                </div> -->

            </div>
        </div>

        <div class="card">
            <h5>Create Password</h5>
            <div class="grid p-fluid mt-3">
                <div class="field col-12 md:col-6">
                    <span class="p-float-label">
                        <Password type="text" id="password" v-model="password" @blur="onInputBlur(password, 'password')" :class="{ 'p-invalid': formError?.password }"/>
                        <label for="password">Password</label>
                    </span>
                </div>

                <div class="field col-12 md:col-6">
                    <span class="p-float-label">
                        <Password type="text" id="confirmPassword" v-model="confirmPassword" @blur="onInputBlur(confirmPassword, 'confirmPassword')" :class="{ 'p-invalid': formError?.confirmPassword }"/>
                        <label for="confirmPassword">Confirm Password</label>
                    </span>
                </div>
                <div class="field col-12 md:col-8"></div>
                <div class="field col-12 md:col-4">
                    <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                </div>
            </div>
        </div>
    </div>
</template>
