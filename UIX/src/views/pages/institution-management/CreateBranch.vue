<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';
// import { required, email } from '@vuelidate/validators';
// import { useVuelidate } from '@vuelidate/core';

const toast = useToast();

const commonService = new CommonService();

const router = useRouter();
const route = useRoute()
const institutionsData = ref(null);
const formError = ref({});
const name = ref(null);
const institution = ref(null);
const city = ref(null);
const address = ref(null);
const p_o_box = ref(null);
const description = ref(null);
const cities = ref(null);
const street = ref(null);


const getInstitution = () => {
    commonService.genericRequest('get-institutions', 'get', true, {}).then((response) => {
        if (response.status) {
            institutionsData.value = response.data
        } else {

        }
    })
}

const getCities = () => {
    commonService.genericRequest('get-city-county-id/' + 1, 'get', true, {}).then((response) => {
        if (response.status) {
            cities.value = response.data
        } else {

        }
    })
}

const onInputBlur = (value, key) => {
    formError.value[key] = commonService.validateFormField(value);
}


const onSubmit = () => {
    formError.value.name = commonService.validateFormField(name.value);
    formError.value.institution = commonService.validateFormField(institution.value);

    let invalid = commonService.validateRequiredFields(formError.value);
    if (invalid) {
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData = {
        name:name.value,
        institution_id: institution.value?.id,
        city_id: city.value?.id,
        address: address.value,
        p_o_box: p_o_box.value,
        description: description.value,
        street:street.value,
        status:"Active",
        is_main:false,
    }

    commonService.genericRequest('create-branch', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            // commonService.redirect(router, "/view-institutions");
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


onMounted(() => {
    getInstitution();
    getCities()
});

</script>

<template>
    <div>
    <div class="card">
      <h5>Create Branch</h5>
        <div class="grid p-fluid mt-3">
            <Toast />
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <InputText type="text" id="instName" v-model="name" @blur="onInputBlur(name, 'name')"
                        :class="{ 'p-invalid': formError?.name }" /> <!-- class="p-invalid"-->
                    <label for="instName">Branch Name</label>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <Dropdown id="institution" @change="onInstitutionChange" :options="institutionsData"
                        v-model="institution" optionLabel="name"></Dropdown>
                    <label for="institution">Institution</label>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <Dropdown id="instCity" :options="cities" v-model="city" optionLabel="name"></Dropdown>
                    <label for="instCity">City</label>
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
                    <InputText type="text" id="instName" v-model="street" @blur="onInputBlur(street, 'street')"
                        :class="{ 'p-invalid': formError?.street }" /> <!-- class="p-invalid"-->
                    <label for="instName">Street</label>
                </span>
            </div>
            <div class="field col-12 md:col-4">
                <span class="p-float-label">
                    <InputText type="text" id="instName" v-model="p_o_box" @blur="onInputBlur(p_o_box, 'p_o_box')"
                        :class="{ 'p-invalid': formError?.p_o_box }" /> <!-- class="p-invalid"-->
                    <label for="instName">P.O Box</label>
                </span>
            </div>
            <div class="field col-12 md:col-12">
                <span class="p-float-label">
                    <Textarea id="textarea" rows="3" cols="30" v-model="description"></Textarea>
                    <label for="textarea">Description</label>
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
