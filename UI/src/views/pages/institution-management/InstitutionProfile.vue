<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';
const route = useRoute()

const toast = useToast();

const commonService = new CommonService();
const router = useRouter();
const institutionDetails = ref(null)

const getProductDetail=()=>{
    let postData={
        institutionId: route.params.id,
        status: 'Active',
    }
    commonService.genericRequest('get-institution-profile', 'post', true, postData).then((response) => {
         if (response.status) {
            institutionDetails.value = response?.data[0];
         } else {
             commonService.showError(toast, response.message);
         }
     })
}

onMounted(() => {
    getProductDetail();
});


</script>

<template>
    <div class="grid">
        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5 class="text-500">Institution Details</h5>
                <div class="field">
                    <label for="name1" class="text-900">Name: <span> {{institutionDetails?.name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Ref No.: <span> {{institutionDetails?.ref_no}}</span></label>
                </div>

                <div class="field">
                    <label for="name1" class="text-900">Type: <span> {{institutionDetails?.type_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Status: <span> {{institutionDetails?.status}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">P.O Box: <span> {{institutionDetails?.p_o_box}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Start Date: <span> {{institutionDetails?.created_at}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Tin: <span> {{institutionDetails?.tin}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Ref. No: <span> {{institutionDetails?.ref_no}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Description: <span> {{institutionDetails?.description}}</span></label>
                </div>
            </div>

            <div class="card p-fluid">
                <h5 class="text-500">Address</h5>
                    <div class="field">
                        <label class="text-900" for="name1">City: <span> {{institutionDetails?.city_name}}</span></label>
                    </div>

                    <div class="field">
                        <label class="text-900" for="name1">Address: <span> {{institutionDetails?.address}}</span></label>
                    </div>
            </div>
        </div>

        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5 class="text-500">Branches</h5>
                <div class="field"  v-for="(branch, index) in institutionDetails?.branches" :key="index">
                    <label class="text-900" for="name1"><span> {{index+1 +'. '+ branch?.name+', '+branch?.address}}</span></label>
                </div>
            </div>

            <div class="card">
                <h5 class="text-500">Bank Info</h5>
                <div class="field">
                    <label class="text-900" for="name1">Bank: <span> {{institutionDetails?.bank_name}}</span></label>
                </div>

                <div class="field">
                    <label class="text-900" for="name1">Name: <span> {{institutionDetails?.acct_name}}</span></label>
                </div>

                <div class="field">
                    <label class="text-900" for="name1">Number: <span> {{institutionDetails?.acct_number}}</span></label>
                </div>
            </div>
        </div>
    </div>
</template>
