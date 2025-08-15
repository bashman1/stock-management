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

const buildings = ref([]);

const startDate = ref(null);
const endDate = ref(null);
const rowsPerPage = ref(50);
const searchKeyWord = ref(null);
const currentPage = ref(1);

const benefitType = ref([{ label: 'Multiplier', value: 'Multiplier' }, { label: 'Percentage', value: 'Percentage' } , { label: 'Fixed Value', value: 'Fixed Value' }]);

const onSubmit = () => {

    // console.log(formData.value);

    let postData = formData.value;
    postData.building_id = postData.building?.id;
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

    commonService.genericRequest('create-building-unit', 'post', true, postData).then((response) => {
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



const getBuildings = () => {
    let postData = {
        page: currentPage.value,
        search: searchKeyWord.value,
        rowPerPage: rowsPerPage.value,
        startDate: startDate.value,
        endDate: endDate.value,
    }
    commonService.genericRequest('get-buildings', 'POST', true, postData).then((response) => {
        if (response.status) {
            buildings.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


onMounted(() => {
    userData.value=commonService.getStorage().userData;
    getBuildings();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>Create Building Units</h5>
            <div class="grid p-fluid mt-3">
                <Toast />
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="name" v-model="formData.name" @blur="onInputBlur(formData.name, 'name')" :class="{ 'p-invalid': formError?.name }" /> <!-- class="p-invalid"-->
                        <label for="name">Unit Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="unit" v-model="formData.unit" @blur="onInputBlur(formData.unit, 'unit')" :class="{ 'p-invalid': formError?.unit }" /> <!-- class="p-invalid"-->
                        <label for="unit">Unit Number</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="instCity" :options="buildings.data" v-model="formData.building" filter optionLabel="name"></Dropdown>
                        <label for="instCity">Building</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputNumber type="text" id="rent" v-model="formData.rent"  @blur="onInputBlur(formData.rent, 'rent')" :class="{ 'p-invalid': formError?.rent }" /> <!-- class="p-invalid"-->
                        <label for="rent">Monthly Rent</label>
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
