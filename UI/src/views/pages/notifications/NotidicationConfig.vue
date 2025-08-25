<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();
const formData = ref({});
const userData=ref(null);
const formError = ref({});
const durations = ref([]);
const citiesData = ref(null);

const benefitType = ref([{ label: 'Multiplier', value: 'Multiplier' }, { label: 'Percentage', value: 'Percentage' } , { label: 'Fixed Value', value: 'Fixed Value' }]);

const onSubmit = () => {

    // console.log(formData.value);

    let postData = formData.value;
    postData.city_id = postData.city?.id;
    postData.date = postData.date ? postData.date: new Date();

    console.log(formData.value);

    // let postData = formData.value;
    // postData.duration_id = postData.duration?.id;
    // postData.benefit_type = postData.benefitType?.value;

    // formError.value.name = commonService.validateFormField(formData.value?.name);
    // formError.value.duration = commonService.validateFormField(formData.value?.duration);
    // formError.value.monitory = commonService.validateFormField(formData.value?.monitory);
    // formError.value.benefitType = commonService.validateFormField(formData.value?.benefitType);
    // formError.value.benefit = commonService.validateFormField(formData.value?.benefit);

    // let invalid = commonService.validateRequiredFields(formError.value);
    // if(invalid){
    //     commonService.showError(toast, "Please fill in the missing field");
    //     return
    // }

    commonService.genericRequest('create-building', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            // commonService.redirect(router, "/view-projects");
            formData.value = {};
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onInputBlur=(value, key)=>{
    formError.value[key] = commonService.validateFormField(value);
}

const getDurations = () => {
    commonService.genericRequest("get-durations", 'get', false, {}).then((response) => {
        if (response.status) {
            durations.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getCities = () => {
    commonService.genericRequest('get-city-county-id/' + 1, 'get', true, {}).then((response) => {
        if (response.status) {
            citiesData.value = response.data
        } else {

        }
    })
}


onMounted(() => {
    userData.value=commonService.getStorage().userData;
    getDurations();
    getCities();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>Notification Config</h5>
            <div class="grid p-fluid mt-3">
                <Toast />
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="formData.name" @blur="onInputBlur(formData.name, 'name')" :class="{ 'p-invalid': formError?.name }" /> <!-- class="p-invalid"-->
                        <label for="firstName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="instCity" :options="citiesData" v-model="formData.city" filter optionLabel="name"></Dropdown>
                        <label for="instCity">City</label>
                    </span>
                </div>

                 <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="formData.street" @blur="onInputBlur(formData.street, 'street')" :class="{ 'p-invalid': formError?.street }" /> <!-- class="p-invalid"-->
                        <label for="firstName">Street</label>
                    </span>
                </div>
                 <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="formData.address" @blur="onInputBlur(formData.address, 'address')" :class="{ 'p-invalid': formError?.address }" /> <!-- class="p-invalid"-->
                        <label for="firstName">Address</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="formData.plot" @blur="onInputBlur(formData.plot, 'plot')" :class="{ 'p-invalid': formError?.plot }" /> <!-- class="p-invalid"-->
                        <label for="firstName">Plot No.</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputNumber type="text" id="email" v-model="formData.rent"  @blur="onInputBlur(formData.rent, 'rent')" :class="{ 'p-invalid': formError?.rent }" /> <!-- class="p-invalid"-->
                        <label for="email">Monthly Rent</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Calendar inputId="dob" v-model="formData.date"></Calendar>
                        <label for="dob">Date</label>
                    </span>
                </div>

                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="instDescription" rows="3" cols="30" v-model="formData.description"></Textarea>
                        <label for="instDescription">More Info</label>
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
