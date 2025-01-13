<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';
import AppConfig from '@/layout/AppConfig.vue';

const toast = useToast();

const commonService = new CommonService();

const router = useRouter();
const route = useRoute()

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
const validation = ref({})
const formError = ref({});
const taxConfig = ref({ id: 1, name: 'Enable tax', value: true });
const tin = ref(null);
const fName = ref(null);
const lName = ref(null);
const oName = ref(null);
const email = ref(null);
const password = ref(null);
const confirmPassword = ref(null);

const getInstitutionTypes = () => {
    commonService.genericRequest('get-institution-types', 'get', false, {}).then((response) => {
        if (response.status) {
            instType.value = response.data
        } else {

        }
    })
}


const getCities = () => {
    commonService.genericRequest('get-city-county-id/' + 1, 'get', false, {}).then((response) => {
        if (response.status) {
            cities.value = response.data
        } else {

        }
    })
}


const getBanks = () => {
    commonService.genericRequest('get-bank-ref', 'get', false, {}).then((response) => {
        if (response.status) {
            banks.value = response.data
        } else {

        }
    })
}

const onInputBlur = (value, key) => {
    formError.value[key] = commonService.validateFormField(value);
}

const validationCheck = (value, key) => {
    console.log(value)
    if (value == null || value.trim() == "") {
        validation.value[key] = true;
    } else {
        validation.value[key] = false;
    }
}


const onSubmit = () => {
    formError.value.name = commonService.validateFormField(name.value);
    formError.value.type = commonService.validateFormField(type.value);
    formError.value.city = commonService.validateFormField(city.value);
    formError.value.contactName = commonService.validateFormField(contactName.value);
    formError.value.contactNumber = commonService.validateFormField(contactNumber.value);
    formError.value.contactEmail = commonService.validateFormField(contactEmail.value);
    formError.value.bankName = commonService.validateFormField(bankName.value);
    formError.value.acctName = commonService.validateFormField(acctName.value);
    formError.value.acctNumber = commonService.validateFormField(acctNumber.value);
    formError.value.taxConfig = commonService.validateFormField(taxConfig.value);
    formError.value.fName = commonService.validateFormField(fName.value);
    formError.value.lName = commonService.validateFormField(lName.value);
    // formError.value.oName = commonService.validateFormField(oName.value);
    formError.value.email = commonService.validateFormField(email.value);
    formError.value.password = commonService.validateFormField(password.value);
    formError.value.confirmPassword = commonService.validateFormField(confirmPassword.value);

    let invalid = commonService.validateRequiredFields(formError.value);
    if (invalid) {
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData = {
        id:null,
        name: name?.value,
        type: type?.value?.id,
        start_date: (!startDate?.value) ? new Date() : startDate?.value,
        address: address?.value,
        city: city?.value?.id,
        street: street?.value,
        p_o_box: pOBox?.value,
        description: description?.value,
        contact_name: contactName?.value,
        contact_number: contactNumber?.value,
        contact_email: contactEmail?.value,
        contact_web: contactWeb?.value,
        bank_id: bankName?.value?.id,
        acct_name: acctName?.value,
        acct_no: acctNumber?.value,
        tax_config: taxConfig?.value?.value,
        tin: taxConfig.value.name,
        status: 'Active',
        first_name: fName.value,
        last_name: lName.value,
        other_name: oName.value,
        email: email.value,
        password: password.value,
        confirm_password: confirmPassword.value
    }

    // console.log("*****************************************")
    // console.log(postData)
    commonService.genericRequest('institution-self-registration', 'post', false, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            commonService.redirect(router, "/view-institutions");
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const goToLogin = () =>{
    router.push("/auth/login");
}


onMounted(() => {
    getInstitutionTypes();
    getCities();
    getBanks();

});

</script>

<template>
    <div class="py-6 px-6">

        <div class="card p-fluid">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-3">
                    <img src="demo/images/SmarCollectlogo-removebg-preview.png" alt="Sakai logo" class="mb-5 w-20rem flex-shrink-0" />
                </div>
                <div class="field col-12 md:col-9" >
                    <h2 class="text-right mt-6">Please fill in to register</h2>
                </div>

                <div class="col-12 md:col-12">
                    <h5>Institution Basic Info</h5>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="instName" v-model="name" @blur="onInputBlur(name, 'name')"
                            :class="{ 'p-invalid': formError?.name }" /> <!-- class="p-invalid"-->
                        <label for="instName">Institution Name</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="instType" @blur="onInputBlur(type, 'type')" :options="instType" v-model="type"
                            :class="{ 'p-invalid': formError?.type }" optionLabel="name"></Dropdown>
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
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="instCity" :options="cities" v-model="city" @blur="onInputBlur(city, 'city')"
                            :class="{ 'p-invalid': formError?.city }" optionLabel="name"></Dropdown>
                        <label for="instCity">City</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText type="text" id="instStreet" v-model="street" />
                        <label for="instStreet">Street</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText type="text" id="instPOBox" v-model="pOBox" />
                        <label for="instPOBox">P.O Box</label>
                    </span>
                </div>
                <!-- <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="instCity" :options="taxOptions" v-model="taxConfig"
                            @blur="onInputBlur(taxConfig, 'taxConfig')" :class="{ 'p-invalid': formError?.taxConfig }"
                            optionLabel="name"></Dropdown>
                        <label for="instCity">Tax Config</label>
                    </span>
                </div> -->
                <div class="field col-12 md:col-3" v-if="taxConfig?.value">
                    <span class="p-float-label">
                        <InputText type="text" id="tin" v-model="tin" />
                        <label for="instPOBox">TIN</label>
                    </span>
                </div>

                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea id="instDescription" rows="3" cols="30" v-model="description"></Textarea>
                        <label for="instDescription">Description</label>
                    </span>
                </div>

                <div class="col-12 md:col-12">
                    <h5>Institution Contact Info</h5>
                </div>

                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText type="text" id="contactName" v-model="contactName"
                            @blur="onInputBlur(contactName, 'contactName')"
                            :class="{ 'p-invalid': formError?.contactName }" />
                        <label for="contactName">Contact Person Name</label>
                    </span>
                </div>

                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <!-- <InputText type="text" id="contactNumber" v-model="contactNumber" /> -->
                        <InputText id="basic" v-model="contactNumber"
                            @blur="onInputBlur(contactNumber, 'contactNumber')"
                            :class="{ 'p-invalid': formError?.contactNumber }" />
                        <label for="contactNumber">Phone Number</label>
                    </span>
                </div>

                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText type="text" id="contactEmail" v-model="contactEmail"
                            @blur="onInputBlur(contactEmail, 'contactEmail')"
                            :class="{ 'p-invalid': formError?.contactEmail }" />
                        <label for="contactEmail">Email</label>
                    </span>
                </div>

                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText type="text" id="contactWeb" v-model="contactWeb" />
                        <label for="contactWeb">Website</label>
                    </span>
                </div>


                <div class="field col-12 md:col-12">
                    <h5>Institution Bank Info</h5>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="bankName" :options="banks" v-model="bankName" optionLabel="name"
                            @blur="onInputBlur(bankName, 'bankName')" :class="{ 'p-invalid': formError?.bankName }">
                        </Dropdown>
                        <label for="bankName">Bank</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="acctName" v-model="acctName"
                            @blur="onInputBlur(acctName, 'acctName')" :class="{ 'p-invalid': formError?.acctName }" />
                        <label for="acctName">Account Name</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="acctNumber" v-model="acctNumber"
                            @blur="onInputBlur(acctNumber, 'acctNumber')"
                            :class="{ 'p-invalid': formError?.acctNumber }" />
                        <label for="acctNumber">Account Number</label>
                    </span>
                </div>

                <div class="field col-12 md:col-12">
                    <h5>Institution User Info</h5>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="fName" @blur="onInputBlur(fName, 'fName')"
                            :class="{ 'p-invalid': formError?.fName }" /> <!-- class="p-invalid"-->
                        <label for="firstName">First Name</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="lastName" v-model="lName" @blur="onInputBlur(lName, 'lName')"
                            :class="{ 'p-invalid': formError?.lName }" /> <!-- class="p-invalid"-->
                        <label for="lastName">Last Name</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="otherName" v-model="oName" /> <!-- class="p-invalid"-->
                        <label for="otherName">Other Names</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="email" v-model="email" @blur="onInputBlur(email, 'email')"
                            :class="{ 'p-invalid': formError?.email }" /> <!-- class="p-invalid"-->
                        <label for="email">Email</label>
                    </span>
                </div>

                 <div  class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Password type="text" id="password" v-model="password" @blur="onInputBlur(password, 'password')"
                            :class="{ 'p-invalid': formError?.password }" />
                        <label for="password">Password</label>
                    </span>
                </div>

                <div  class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Password type="text" id="confirmPassword" v-model="confirmPassword"
                            @blur="onInputBlur(confirmPassword, 'confirmPassword')"
                            :class="{ 'p-invalid': formError?.confirmPassword }" />
                        <label for="confirmPassword">Confirm Password</label>
                    </span>
                </div>

                <!-- <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="phoneNumber" v-model="phoneNumber" />
                        <label for="phoneNumber">Phone Number</label>
                    </span>
                </div> -->

                <!-- <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Calendar inputId="dob" v-model="dob"></Calendar>
                        <label for="dob">Date Of Birth</label>
                    </span>
                </div> -->

                <!-- <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="gender" :options="genderOptions" v-model="gender" optionLabel="label"></Dropdown>
                        <label for="gender">Gender</label>
                    </span>
                </div> -->

                <div class="field col-12 md:col-8">
                    <a class="font-medium no-underline ml-2 text-left cursor-pointer" style="color: var(--primary-color)" @click="goToLogin" >Back To Login</a>
                </div>
                <div class="field col-12 md:col-4">
                    <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                </div>


            </div>
        </div>
    </div>
    <AppConfig simple />
</template>
