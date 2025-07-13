<script setup>

import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();
const route = useRoute()


const durations = ref([]);
const paymentMethod = ref([]);
const depreciationRate = ref([]);
const assetAccounts = ref([]);
const cashAccounts = ref([]);
const expenseAccounts = ref([]);
const formData = ref({});
const projects = ref([]);
const members = ref(null);

const dropdownItems = ref([
    { name: 'Option 1', code: 'Option 1' },
    { name: 'Option 2', code: 'Option 2' },
    { name: 'Option 3', code: 'Option 3' }
]);


const getDurations = () => {
    commonService.genericRequest("get-durations", 'get', false, {}).then((response) => {
        if (response.status) {
            durations.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const getProjects = () => {
    commonService.genericRequest('get-projects', 'get', true, {}).then((response) => {
        if (response.status) {
            projects.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const getAllMembers = () => {
    commonService.genericRequest('get-members', 'get', true, {}).then((response) => {
        if (response.status) {
            members.value = response.data.map(rate => { return { ...rate, name: rate.first_name + ' ' + rate.last_name + ' ' + rate.other_name } });
        } else {
            commonService.showError(toast, response.message);
        }
    })
}



// const getPaymentMethods = () => {
//     commonService.genericRequest("get-payment-methods", 'get', true, {}).then((response) => {
//         if (response.status) {
//             paymentMethod.value = response.data
//         } else {
//             commonService.showError(toast, response.message);
//         }
//     })
// }

// const getDepreciationRates = () => {
//     commonService.genericRequest("get-depreciation-rates", 'get', true, {}).then((response) => {
//         if (response.status) {
//             depreciationRate.value = response.data.map(rate=>{return {...rate, name: rate.name + ' (' + rate.rate + '%)' + ' ' + rate.duration.name }})
//         } else {
//             commonService.showError(toast, response.message);
//         }
//     })
// }

// Fetch GL Accounts
// const getFixedAssetAccounts = async () => {
//     assetAccounts.value = await searchGlAcct({ 'gl_sub_cat_no': '1100000' })
// };

// const getCashBankAccounts = async (account) => {
//     if (account.value.name == 'Cash') {
//         cashAccounts.value = await searchGlAcct({ 'gl_type_no': '1301000' })
//     } else if (account.value.name == 'Bank') {
//         cashAccounts.value = await searchGlAcct({ 'gl_type_no': '1302000' })
//     }
// }

// const getExpenseAccounts = async () => {
//     expenseAccounts.value = await searchGlAcct({ 'gl_cat_no': '5000000' });
// }

// const searchGlAcct = (postData) => {
//     return commonService.genericRequest('gl-search', 'post', true, postData).then(response => {
//         if (response.status) {
//             return response.data;
//         } else {
//             commonService.showError(toast, response.message);
//             return null;
//         }
//     });
// }

const onSubmit = () => {
    let postData = formData.value
    postData.project_id = formData.value.project.id;
    postData.member_id = formData.value.members.id;


    commonService.genericRequest("create-investment", 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            formData.value = {};
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

onMounted(() => {
    getDurations();
    getProjects();
    getAllMembers();
    // getDepreciationRates();
    // getPaymentMethods();
    // getFixedAssetAccounts();
    // getExpenseAccounts();
});

const dropdownItem = ref(null);
</script>

<template>
    <div class="grid">
        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5>Investment</h5>
                <div class="field mt-4">
                    <span class="p-float-label">
                        <Dropdown id="instType" filter optionLabel="name" :options="projects"
                            v-model="formData.project"></Dropdown>
                        <label for="instType">Project</label>
                    </span>
                </div>
                <div class="field mt-4">
                    <span class="p-float-label">
                        <Dropdown id="instType" filter optionLabel="name" :options="members"
                             v-model="formData.members"></Dropdown>
                        <label for="instType">Customer</label>
                    </span>
                </div>
                <div class="field mt-4">
                    <span class="p-float-label">
                        <InputNumber id="name1" type="text" v-model="formData.quantity" />
                        <label for="name1">Quantity</label>
                    </span>
                </div>
                <div class="field mt-4">
                    <span class="p-float-label">
                        <Calendar :showIcon="true" :showButtonBar="true" id="name1" type="text"
                            v-model="formData.date" />
                        <label for="name1">Date</label>
                    </span>
                </div>

                <div class="field mt-4">
                    <span class="p-float-label">
                        <Textarea id="age1" type="text" rows="3" cols="30" v-model="formData.description" />
                        <label for="age1">Description</label>
                    </span>
                </div>

                <div class="field mt-4">
                    <div class="grid">
                        <div class="field col-8 md:col-8"></div>
                        <div class="field col-4 md:col-4">
                            <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                        </div>
                    </div>
                </div>

            </div>


        </div>

        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5>Project Details</h5>

                <div class="grid">
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Name</p>
                        <p class="text-gray-900">{{ formData?.project?.name }}</p>
                    </div>

                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Description</p>
                        <p class="text-gray-900">{{ formData?.project?.description }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Status</p>
                        <p class="text-gray-900">{{ formData?.project?.status }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Benefit</p>
                        <p class="text-gray-900">{{ formData?.project?.benefit + ' ' + formData?.project?.benefit_type
                            }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Monitory Value</p>
                        <p class="text-gray-900">{{ formData?.project?.monitory }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Duration</p>
                        <p class="text-gray-900">{{ formData?.project?.duration?.name }}</p>
                    </div>
                    <hr>


                </div>

                <!-- <div class="field mt-4">
                    <span class="p-float-label">
                        <Dropdown id="instType" filter optionLabel="name" :options="depreciationRate"
                            v-model="formData.depreciationRate"></Dropdown>
                        <label for="instType">Depreciation Rate</label>
                    </span>
                </div>

                <div class="field mt-4">
                    <span class="p-float-label">
                        <Dropdown id="instType" filter optionLabel="description" :options="expenseAccounts"
                            v-model="formData.depreciationCreditAccount"></Dropdown>
                        <label for="instType">Depreciation Credit Account</label>
                    </span>
                </div> -->

            </div>
            <div class="card p-fluid">
                <h5>Customer Information</h5>

                <div class="grid">
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">End Date</p>
                        <p class="text-gray-900">{{ formData?.project?.monitory }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Total Benefits (Value)</p>
                        <p class="text-gray-900">{{ formData?.project?.benefit * formData?.quantity }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Total Benefits (Monitory)</p>
                        <p class="text-gray-900">{{ formData?.project?.monitory * formData?.quantity }}</p>
                    </div>
                </div>


            </div>
        </div>
    </div>
</template>
