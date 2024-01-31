<script setup>
 import {ref, onMounted} from 'vue';
 import { useRouter } from 'vue-router';
 import CommonService from '@/service/CommonService';
 import { useToast } from 'primevue/usetoast';
 // import { required, email } from '@vuelidate/validators';
 // import { useVuelidate } from '@vuelidate/core';

const toast = useToast();

 const commonService = new CommonService();

 const router = useRouter();
 const name = ref(null);
 const type = ref(null);
 const startDate = ref(null);
 const address = ref(null);
 const city = ref(null);
 const street = ref(null);
 const pOBox = ref(null);
 const description = ref(null);
 const contactName = ref(null);
 const contactNumber = ref(null);
 const contactEmail = ref(null);
 const contactWeb = ref(null);
 const bankName = ref(null);
 const acctName = ref(null);
 const acctNumber = ref(null);
 const instType = ref(null);
 const cities = ref(null);
 const banks = ref(null);
 const validation= ref({})

 const onSubmit=()=>{
    let postData = {
        name:name.value,
        type: type.value.id,
        start_date:startDate.value,
        address:address.value,
        city:city.value.id,
        street:street.value,
        p_o_box:pOBox.value,
        description:description.value,
        contact_name:contactName.value,
        contact_number:contactNumber.value,
        contact_email:contactEmail.value,
        contact_web:contactWeb.value,
        bank_id:bankName.value.id,
        acct_name:acctName.value,
        acct_no:acctNumber.value,
        status:'Active'
    }
    commonService.genericRequest('create-institution', 'post', true, postData).then((response)=>{
        if(response.status){
            commonService.showSuccess(toast,response.message);
            commonService.redirect(router, "/view-institutions");
        }else{
            commonService.showError(toast,response.message);
        }
    })
 }


 const getInstitutionTypes=()=>{
    commonService.genericRequest('get-institution-types', 'get', true, {}).then((response)=>{
        if(response.status){
            instType.value = response.data
        }else{

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

 const getBanks=()=>{
    commonService.genericRequest('get-bank-ref', 'get', true, {}).then((response)=>{
        if(response.status){
            banks.value = response.data
        }else{

        }
    })
 }

 const validationCheck=(value, key)=>{
     console.log(value)
     if (value==null || value.trim()==""){
         validation.value[key]=true;
     }else{
         validation.value[key]=false;
     }
 }

 // const rules = {
 //     name: required,
 //     contactEmail: { required, email },
 // };

 // const v = useVuelidate(rules, toRefs(validation));


 onMounted(() => {
    getInstitutionTypes();
    getCities();
    getBanks();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>Create Institution</h5>
            <div class="grid p-fluid mt-3">
                <Toast />
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="instName" v-model="name"   />  <!-- class="p-invalid"-->
                        <label for="instName">Institution Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="instType" @blur="validationCheck($event.value, 'type')" :options="instType" v-model="type" optionLabel="name" ></Dropdown>
                        <label for="instType">Institution Type</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Calendar id="instStart" v-model="startDate"></Calendar>
                        <label for="instStart">Start Date</label>
                    </span>
                </div>

                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea id="textarea" rows="3" cols="30" v-model="address"></Textarea>
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

                <div class="field col-12 md:col-10">
                    <span class="p-float-label">
                        <Textarea id="instDescription" rows="3" cols="30" v-model="description"></Textarea>
                        <label for="instDescription">Description</label>
                    </span>
                </div>
                <div class="field col-12 md:col-2">
                    <h5>Attachment</h5>
                    <FileUpload mode="basic" name="demo[]" accept="image/*" :maxFileSize="1000000" @uploader="onUpload" customUpload />

                </div>

            </div>
        </div>

        <div class="card">
            <h5>Institution Contact Info</h5>
            <div class="grid p-fluid mt-3">
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText type="text" id="contactName" v-model="contactName" />
                        <label for="contactName">Contact Person Name</label>
                    </span>
                </div>

                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <!-- <InputText type="text" id="contactNumber" v-model="contactNumber" /> -->
                        <InputMask id="basic" v-model="contactNumber" slotChar="_" mask="99-999999" placeholder="25-______" />
                        <label for="contactNumber">Phone Number</label>
                    </span>
                </div>

                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText type="text" id="contactEmail" v-model="contactEmail" />
                        <label for="contactEmail">Email</label>
                    </span>
                </div>

                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText type="text" id="contactWeb" v-model="contactWeb" />
                        <label for="contactWeb">Website</label>
                    </span>
                </div>
            </div>
        </div>

        <div class="card">
            <h5>Institution Bank Info</h5>
            <div class="grid p-fluid mt-3">
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="bankName" :options="banks" v-model="bankName" optionLabel="name"></Dropdown>
                        <label for="bankName">Bank</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="acctName" v-model="acctName" />
                        <label for="acctName">Account Name</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="acctNumber" v-model="acctNumber" />
                        <label for="acctNumber">Account Number</label>
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
