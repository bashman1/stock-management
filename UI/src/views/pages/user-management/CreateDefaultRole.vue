<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

 const router = useRouter();
const name = ref(null);
const type = ref(null);
const institutionsData = ref(null);
const institution = ref(null);
const description = ref(null);
const formError = ref({});


// const typeOptions = ref([
//     { label: 'Admin Role', value: 'Admin' },
//     { label: 'Institution Role', value: 'Institution' },
// ]);

const onSubmit = () => {
    formError.value.name = commonService.validateFormField(name.value);
    // formError.value.type = commonService.validateFormField(type.value);

    let invalid = commonService.validateRequiredFields(formError.value);
    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData = {
        name: name?.value,
        type: type?.value?.value,
        institution_id:institution?.value,
        description: description?.value,
        institution_id:institution.value!=null?institution?.value.id:null,
        role_type:type?.value?.value,
        status:'Active'
    }

    commonService.genericRequest('create-default-role', 'post', true, postData).then((response) => {
        if(response.status){
            commonService.showSuccess(toast,response.message);
            commonService.redirect(router, "/view-roles");
        }else{
            commonService.showError(toast,response.message);
        }
    })
}


const onInputBlur=(value, key)=>{
    formError.value[key] = commonService.validateFormField(value);
}


// const getInstitution=()=>{
//     commonService.genericRequest('get-institutions', 'get', true, {}).then((response)=>{
//         if(response.status){
//             institutionsData.value = response.data
//         }else{

//         }
//     })
//  }


 onMounted(() => {
    // getInstitution();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>Create Default Role</h5>
            <div class="grid p-fluid mt-3">
                <Toast />
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <InputText type="text" id="name" v-model="name"  @blur="onInputBlur(name, 'name')" :class="{ 'p-invalid': formError?.name }"/> <!-- class="p-invalid"-->
                        <label for="firstName">Name</label>
                    </span>
                </div>
                <!-- <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="type" :options="typeOptions" v-model="type" optionLabel="label" @blur="onInputBlur(type, 'type')" :class="{ 'p-invalid': formError?.type }"></Dropdown>
                        <label for="type">Type</label>
                    </span>
                </div> -->
                <!-- <div class="field col-12 md:col-4" v-if="type?.value =='Institution'">
                    <span class="p-float-label">
                        <Dropdown id="institution" :options="institutionsData" v-model="institution" optionLabel="name"></Dropdown>
                        <label for="institution">Institution</label>
                    </span>
                </div> -->

                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="description" rows="3" cols="30" v-model="description"></Textarea>
                        <label for="description">Description</label>
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
