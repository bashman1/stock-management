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

const buildings = ref([]);
const buildingUnits = ref([]);
const startDate = ref(null);
const endDate = ref(null);
const rowsPerPage = ref(50);
const searchKeyWord = ref(null);
const currentPage = ref(1);


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


const maturityDate = computed(() => {
  const duration = formData.value?.project?.duration?.name;
  const startDate = formData.value?.date ? new Date(formData.value.date) : null;

  if (!duration || !startDate) return '';

  switch (duration) {
    case 'Daily':
      startDate.setDate(startDate.getDate() + 1);
      break;
    case 'Weekly':
      startDate.setDate(startDate.getDate() + 7);
      break;
    case 'Monthly':
      startDate.setMonth(startDate.getMonth() + 1);
      break;
    case 'Quarterly':
      startDate.setMonth(startDate.getMonth() + 3);
      break;
    case 'Yearly':
      startDate.setFullYear(startDate.getFullYear() + 1);
      break;
    default:
      return '';
  }

   return commonService.formatDate(startDate);
});


const onSubmit = () => {
    let postData = formData.value
    postData.building_id = formData.value.building.id;
    postData.member_id = formData.value.members.id;
    postData.unit_id = formData.value.unit.id;
    postData.duration = formData.value.quantity;
    postData.amount_paid = formData.value.amountPaid;
    postData.rental_date = formData.value.date;


    commonService.genericRequest("rent-building-unit", 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            formData.value = {};
        } else {
            commonService.showError(toast, response.message);
        }
    })
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


const getBuildingUnits = (buildingId) => {
    let postData = {
        page: currentPage.value,
        search: searchKeyWord.value,
        rowPerPage: rowsPerPage.value,
        startDate: startDate.value,
        endDate: endDate.value,
        building_id: buildingId,
        status: "Active"
    }
    commonService.genericRequest('get-building-units', 'POST', true, postData).then((response) => {
        if (response.status) {
            buildingUnits.value = response?.data?.data.map(unit => {
                return {
                    ...unit,
                    name: unit.name +'-'+ unit.unit_no +' @ '+ commonService.commaSeparator(unit.rent) +' '+ ' Per Month',
                };
            });
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onBuildingChange=(event)=>{
    getBuildingUnits(event.value.id);
}

const onBuildingUnitChange=(event)=>{
    formData.value.amountPaid = Number(formData.value?.unit?.rent) * Number(formData.value?.quantity);
}


onMounted(() => {
    // getDurations();
    // getProjects();
    getAllMembers();
    getBuildings();
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
                <h5>Renting </h5>
                <div class="field mt-4">
                    <span class="p-float-label">
                        <Dropdown id="instCity" :options="buildings.data" @change="onBuildingChange" v-model="formData.building" filter optionLabel="name"></Dropdown>
                        <label for="instCity">Building</label>
                    </span>
                </div>
                <div class="field mt-4">
                    <span class="p-float-label">
                        <Dropdown id="instCity" :options="buildingUnits" @change="onBuildingUnitChange" v-model="formData.unit" filter optionLabel="name"></Dropdown>
                        <label for="instCity">Unit</label>
                    </span>
                </div>
                <div class="field mt-4">
                    <span class="p-float-label">
                        <Dropdown id="instType" filter optionLabel="name" :options="members"
                             v-model="formData.members"></Dropdown>
                        <label for="instType">Tenants</label>
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
                        <InputNumber id="name1" type="text" v-model="formData.amountPaid" />
                        <label for="name1">Amount Paid</label>
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
                <h5>Building Details</h5>

                <div class="grid">
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Name</p>
                        <p class="text-gray-900">{{ formData?.building?.name }}</p>
                    </div>

                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Description</p>
                        <p class="text-gray-900">{{ formData?.building?.description }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Status</p>
                        <p class="text-gray-900">{{ formData?.building?.status }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Plot</p>
                        <p class="text-gray-900">{{ formData?.building?.plot + ' ' +formData?.building?.street}}</p>

                    </div>
                </div>

                <h5>Unit Details</h5>

                <div class="grid">
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Unit</p>
                        <p class="text-gray-900">{{ formData?.unit?.name  + ' '+formData?.unit?.unit_no}}</p>
                    </div>

                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Description</p>
                        <p class="text-gray-900">{{ formData?.unit?.description }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Status</p>
                        <p class="text-gray-900">{{ formData?.unit?.status }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Rent</p>
                        <p class="text-gray-900">{{ formData?.unit?.rent + ' Per Month'}}</p>

                    </div>
                </div>

            </div>
            <div class="card p-fluid">
                <h5>Customer Information</h5>

                <div class="grid">
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Name</p>
                        <p class="text-gray-900">{{ formData?.members?.name }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Contact</p>
                        <p class="text-gray-900">{{ formData?.members?.phone_number +' | '+  formData?.members?.email  }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Tenant Ref</p>
                        <p class="text-gray-900">{{ formData?.members?.member_number }}</p>
                    </div>
                </div>


            </div>
        </div>
    </div>
</template>
