<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();
const route = useRoute()

const durations = ref([]);


const getDurations = () => {
    "get-durations"
    commonService.genericRequest("get-durations", 'get', false, {}).then((response) => {
        if (response.status) {
            durations.value = response.data
        } else {

        }
    })

}


onMounted(() => {
    getDurations();
});

</script>
<!-- <template>
    <div>
        <div class="card">
            <h5>Create Institution</h5>
            <div class="grid p-fluid mt-3">
                <Toast />

                <div class="field col-12 md:col-6">
                    <span class="p-float-label">
                        <InputText type="text" id="instName" v-model="name" @blur="onInputBlur(name, 'name')"
                            :class="{ 'p-invalid': formError?.name }" /> class="p-invalid"
                        <label for="instName">Institution Name</label>
                    </span>
                </div>

                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea id="textarea" rows="3" cols="30" v-model="address"></Textarea>
                        <label for="textarea">Address</label>
                    </span>
                </div>


            </div>
        </div>
    </div>

</template> -->

<template>
    <div class="grid">
        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5>Package Details</h5>
                <div class="field">
                    <span class="p-float-label">

                        <InputText id="name1" type="text" />
                        <label for="name1">Name</label>
                    </span>
                </div>

                <div class="field">
                    <span class="p-float-label">
                        <Textarea id="address" rows="4" />
                        <label for="address">Description</label>
                    </span>
                </div>
            </div>

            <div class="card p-fluid">
                <h5>Package Benefits</h5>
                <div class="formgrid grid">
                    <div class="field col">
                        <label for="name2">Name</label>
                        <InputText id="name2" type="text" />
                    </div>
                    <div class="field col">
                        <label for="email2">Email</label>
                        <InputText id="email2" type="text" />
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5>Package Prices</h5>
                <div class="field">
                    <span class="p-float-label">
                        <Dropdown id="state" v-model="dropdownItem" :options="durations" optionLabel="name"
                            placeholder="Select duration"></Dropdown>
                        <label for="state">Duration</label>
                    </span>
                </div>
                <div class="field">
                    <div class="p-inputgroup">
                        <span class="p-float-label">
                            <InputText id="name1" type="text" />
                            <label for="name1">Price</label>
                        </span>
                        <Button label="Add" />
                    </div>
                </div>

                <div class="field col-12 md:col-8"></div>
                <div class="field col-12 md:col-4">
                    <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                </div>
            </div>

        </div>

    </div>
</template>
