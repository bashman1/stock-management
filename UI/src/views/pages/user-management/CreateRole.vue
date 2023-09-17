<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

//  const router = useRouter(); 
const name = ref(null);
const type = ref(null);
const institution = ref(null);
const description = ref(null);


const typeOptions = ref([
    { label: 'Admin Role', value: 'Admin' },
    { label: 'Institution Role', value: 'Institution' },
]);

const onSubmit = () => {
    let postData = {
        name: name.value,
        type: type.value.value,
        institution_id:institution.value,
        description: description.value,
        status:'Active'
    }

    commonService.genericRequest('crate-role', 'post', true, postData).then((response) => { 
        if(response.status){
            commonService.showSuccess(toast,response.message);
        }else{
            commonService.showError(toast,response.message);
        }
    })
}


</script>
<template>
    <div>
        <div class="card">
            <h5>Create Role</h5>
            <div class="grid p-fluid mt-3">
                <Toast />
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="name" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="type" :options="typeOptions" v-model="type" optionLabel="label"></Dropdown>
                        <label for="type">Type</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4" v-if="type?.value =='Institution'">
                    <span class="p-float-label">
                        <Dropdown id="institution" :options="genderOptions" v-model="institution" optionLabel="label"></Dropdown>
                        <label for="institution">Institution</label>
                    </span>
                </div>

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
