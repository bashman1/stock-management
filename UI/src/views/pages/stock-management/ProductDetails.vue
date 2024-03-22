<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';
const route = useRoute()

const toast = useToast();

const commonService = new CommonService();
const router = useRouter();
const productDetails = ref(null)

const getProductDetail=()=>{
    commonService.genericRequest('get-batch-members', 'post', true, {}).then((response) => {
         if (response.status) {
            productDetails.value = response.data;
         } else {
             commonService.showError(toast, response.message);
         }
     })
}

onMounted(() => {
    getInstitution();
});


</script>

<template>
    <div class="grid">
        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5>Product Details</h5>
                <!-- <div class="field">
                    <label for="name1">Name</label>
                    <InputText id="name1" type="text" />
                </div> -->
                <!-- <div class="field">
                    <label for="email1">Email</label>
                    <InputText id="email1" type="text" />
                </div> -->
                <!-- <div class="field">
                    <label for="age1">Age</label>
                    <InputText id="age1" type="text" />
                </div> -->
            </div>

            <div class="card p-fluid">
                <h5>Vertical Grid</h5>
                <div class="formgrid grid">
                    <!-- <div class="field col">
                        <label for="name2">Name</label>
                        <InputText id="name2" type="text" />
                    </div> -->
                    <!-- <div class="field col">
                        <label for="email2">Email</label>
                        <InputText id="email2" type="text" />
                    </div> -->
                </div>
            </div>
        </div>

        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5>Stock</h5>
                <!-- <div class="field grid">
                    <label for="name3" class="col-12 mb-2 md:col-2 md:mb-0">Name</label>
                    <div class="col-12 md:col-10">
                        <InputText id="name3" type="text" />
                    </div>
                </div>
                <div class="field grid">
                    <label for="email3" class="col-12 mb-2 md:col-2 md:mb-0">Email</label>
                    <div class="col-12 md:col-10">
                        <InputText id="email3" type="text" />
                    </div>
                </div> -->
            </div>

            <div class="card">
                <h5>Inline</h5>
                <div class="formgroup-inline">
                    <!-- <div class="field">
                        <label for="firstname1" class="p-sr-only">Firstname</label>
                        <InputText id="firstname1" type="text" placeholder="Firstname" />
                    </div> -->
                    <!-- <div class="field">
                        <label for="lastname1" class="p-sr-only">Lastname</label>
                        <InputText id="lastname1" type="text" placeholder="Lastname" />
                    </div> -->
                    <!-- <Button label="Submit"></Button> -->
                </div>
            </div>
        </div>
    </div>
</template>
