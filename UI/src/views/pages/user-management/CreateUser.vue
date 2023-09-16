<script setup>
 import {ref, onMounted} from 'vue';
 import { useRouter } from 'vue-router';
 import CommonService from '@/service/CommonService'

 const commonService = new CommonService();

//  const router = useRouter(); 
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


 const genderOptions = ref([
    { label: 'Male', value: 'Male' },
    { label: 'Female', value: 'Female' },
]);

 const onSubmit=()=>{
    let postData = {
        first_name:fName.value,
        last_name: lName.value,
        other_name:oName.value,
        email: email.value,
        phone_number:phoneNumber.value,
        date_of_birth:dob.value,
        gender:gender.value.value,
        address:address.value,
        city_id:city.value,
        street:street.value,
        p_o_box:pOBox.value,
        description:description.value,
        password:password.value,
        confirm_password:confirmPassword.value,
        status:'Active'
    }
    // alert(name.value)
    console.log(postData);
    commonService.genericRequest('create-user', 'post', false, postData).then((response)=>{})
 }


</script>
<template>
    <div>
        <div class="card">
            <h5>Create User</h5>
            <div class="grid p-fluid mt-3">
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="fName"  />  <!-- class="p-invalid"-->
                        <label for="firstName">First Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="lastName" v-model="lName"  />  <!-- class="p-invalid"-->
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
                        <InputText type="text" id="email" v-model="email"  />  <!-- class="p-invalid"-->
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
    
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="textarea" rows="3" cols="30" v-model="address"></Textarea>
                        <label for="textarea">Address</label>
                    </span>
                </div>
    
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="instCity" v-model="city" />
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
                        <InputText type="text" id="password" v-model="password" />
                        <label for="password">Password</label>
                    </span>
                </div>

                <div class="field col-12 md:col-6">
                    <span class="p-float-label">
                        <InputText type="text" id="confirmPassword" v-model="confirmPassword" />
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
