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

const benefitType = ref([{ label: 'Multiplier', value: 'Multiplier' }, { label: 'Percentage', value: 'Percentage' } , { label: 'Fixed Value', value: 'Fixed Value' }]);
const projectType = ref([{ label: 'Goats', value: 'Goats' }, { label: 'Maize', value: 'Maize' } , { label: 'Bees', value: 'Bees' }]);

const onSubmit = () => {

    console.log(formData.value);

    let postData = formData.value;
    postData.duration_id = postData.duration?.id;
    postData.benefit_type = postData.benefitType?.value;

    formError.value.name = commonService.validateFormField(formData.value?.name);
    formError.value.duration = commonService.validateFormField(formData.value?.duration);
    formError.value.monitory = commonService.validateFormField(formData.value?.monitory);
    formError.value.benefitType = commonService.validateFormField(formData.value?.benefitType);
    formError.value.benefit = commonService.validateFormField(formData.value?.benefit);

    let invalid = commonService.validateRequiredFields(formError.value);
    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    commonService.genericRequest('create-project', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            commonService.redirect(router, "/view-projects");
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


onMounted(() => {
    userData.value=commonService.getStorage().userData;
    getDurations();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>Create Project</h5>
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
                    <Dropdown id="state" v-model="formData.duration" :options="durations" filter optionLabel="name"
                        @blur="onInputBlur(formData.duration, 'duration')" :class="{ 'p-invalid': formError?.duration }"
                            placeholder="Select duration"></Dropdown>
                        <label for="state">Duration</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputNumber type="text" id="email" v-model="formData.monitory"  @blur="onInputBlur(formData.monitory, 'monitory')" :class="{ 'p-invalid': formError?.monitory }" /> <!-- class="p-invalid"-->
                        <label for="email">Monitory Value</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                    <Dropdown id="state" v-model="formData.benefitType" :options="benefitType" filter optionLabel="label"
                    @blur="onInputBlur(formData.benefitType, 'benefitType')" :class="{ 'p-invalid': formError?.benefitType }"
                            placeholder="Benefit Type"></Dropdown>
                        <label for="state">Benefit Type</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                    <Dropdown id="state" v-model="formData.projectType" :options="projectType" filter optionLabel="label"
                    @blur="onInputBlur(formData.projectType, 'projectType')" :class="{ 'p-invalid': formError?.projectType }"
                            placeholder="Benefit Type"></Dropdown>
                        <label for="state">Project Type</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputNumber type="text" id="email" v-model="formData.benefit"  @blur="onInputBlur(formData.benefit, 'benefit')" :class="{ 'p-invalid': formError?.benefit }" /> <!-- class="p-invalid"-->
                        <label for="email">Benefit Value</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Calendar inputId="dob" v-model="formData.date"></Calendar>
                        <label for="dob">Date</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputNumber type="text" id="email" v-model="formData.gracePeriod" /> <!-- class="p-invalid"-->
                        <label for="email">Grace Period</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputNumber type="text" id="email" v-model="formData.size" /> <!-- class="p-invalid"-->
                        <label for="email">Size</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputNumber type="text" id="email" v-model="formData.investmentReturnValue" /> <!-- class="p-invalid"-->
                        <label for="email">Return Of Investment Value</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputNumber type="text" id="email" v-model="formData.investmentReturnDuration" /> <!-- class="p-invalid"-->
                        <label for="email">Return Of Investment Duration</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                    <Dropdown id="state" v-model="formData.duration" :options="durations" filter optionLabel="unit"
                            placeholder="Select duration"></Dropdown>
                        <label for="state">Return Of Investment Duration Unit </label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputNumber type="text" id="email" v-model="formData.investmentReturnValue" /> <!-- class="p-invalid"-->
                        <label for="email">Cost Per Unit</label>
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
