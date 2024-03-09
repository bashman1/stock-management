<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

 const router = useRouter();
const name = ref(null);
const cat = ref(null);
const subCat = ref(null);
const type = ref(null);
const glCatList = ref(null);
const glTypeList = ref(null);
const glSubCatList=ref(null);
const institution = ref(null);
const description = ref(null);
const formError = ref({});


const typeOptions = ref([
    { label: 'Admin Role', value: 'Admin' },
    { label: 'Institution Role', value: 'Institution' },
]);

const onSubmit = () => {
    formError.value.name = commonService.validateFormField(name.value);
    formError.value.type = commonService.validateFormField(type.value);

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

    commonService.genericRequest('crate-role', 'post', true, postData).then((response) => {
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


const getLedgerCat=()=>{
    commonService.genericRequest('get-gl-categories', 'post', true, {}).then((response)=>{
        if(response.status){
            glCatList.value = response.data
        }else{

        }
    })
}

const getLegerSubCat=(event)=>{
    let postData={acct_type: event.value.acct_type,gl_cat_no:event.value.gl_no, acct_type:event.value.acct_type, description:null };
    alert(JSON.stringify(postData));
    commonService.genericRequest('get-gl-sub-categories', 'post', true, postData).then((response)=>{
        if(response.status){
            glSubCatList.value = response.data
        }else{
        }
    })
}

const getLegerType=(event)=>{
    let postData={acct_type: event.value.acct_type,gl_cat_no:event.value.gl_cat_no, gl_sub_cat_no:event.value.gl_no, acct_type:event.value.acct_type, description:null };
    commonService.genericRequest('get-gl-type', 'post', true, postData).then((response)=>{
        if(response.status){
            glTypeList.value = response.data
        }else{
        }
    })
}



 onMounted(() => {
    getLedgerCat();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>Create Ledger</h5>
            <div class="grid p-fluid mt-3">
                <Toast />
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="name" v-model="name"  @blur="onInputBlur(name, 'name')" :class="{ 'p-invalid': formError?.name }"/> <!-- class="p-invalid"-->
                        <label for="firstName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="type" :options="glCatList" @change="getLegerSubCat($event)"  v-model="cat" optionLabel="description" @blur="onInputBlur(cat, 'cat')" :class="{ 'p-invalid': formError?.cat }"></Dropdown>
                        <label for="type">Category</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="type" :options="glSubCatList" @change="getLegerType($event)"  v-model="subCat" optionLabel="description" @blur="onInputBlur(subCat, 'subCat')" :class="{ 'p-invalid': formError?.subCat }"></Dropdown>
                        <label for="type">Sub Category</label>
                    </span>
                </div>

                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="type" :options="glTypeList"   v-model="type" optionLabel="description" @blur="onInputBlur(type, 'type')" :class="{ 'p-invalid': formError?.type }"></Dropdown>
                        <label for="type">Accounts</label>
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
