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

const paymentSchedule = ref([]);

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


const maturityDate = computed(() => {
  const duration = formData.value?.project?.duration?.name;
  const startDate = formData.value?.date ? new Date(formData.value.date) : null;

    if(formData?.value?.project?.project_type == 'Goats'){
          if (!duration || !startDate) return '';

        switch (duration) {
            case 'Daily':
            startDate.setDate(startDate.getDate() + (1 * formData.value?.duration) );
            break;
            case 'Weekly':
            startDate.setDate(startDate.getDate() + (7 * formData.value?.duration) );
            break;
            case 'Monthly':
            startDate.setMonth(startDate.getMonth() + (1 * formData.value?.duration) );
            break;
            case 'Quarterly':
            startDate.setMonth(startDate.getMonth() + (3 * formData.value?.duration) );
            break;
            case 'Yearly':
            startDate.setFullYear(startDate.getFullYear() + (1 * formData.value?.duration) );
            break;
            default:
            return '';
        }

        return commonService.formatDate(startDate);
    }else if(formData?.value?.project?.project_type == 'Maize'){
          if ( !startDate) return '';

        const beginDate = new Date(startDate);

        startDate.setFullYear(startDate.getFullYear() + (3) );

        const endDate = new Date(startDate);

        paymentSchedule.value = generatePaymentSchedule(beginDate, endDate, 6, formData?.project?.return_of_investment_value? Number(formData?.project?.return_of_investment_value): 1300000);

        return commonService.formatDate(startDate);
    }else if(formData?.value?.project?.project_type == 'Bees'){
          if (!startDate) return '';

          if(formData.value?.gracePeriod){
              startDate.setMonth(startDate.getMonth() + (Number(formData.value?.gracePeriod)) );
          }
            const beginDate = new Date(startDate);

          startDate.setFullYear(startDate.getFullYear() + (5) );

            const endDate = new Date(startDate);

            paymentSchedule.value = generatePaymentSchedule(beginDate, endDate, 1, formData?.project?.return_of_investment_value? Number(formData?.project?.return_of_investment_value): 100000);

        return commonService.formatDate(startDate);
    }


});


/**
 * Generate a payment schedule between start and end dates
 * @param {Date} startDate - The starting date of the first payment
 * @param {Date} endDate - The last date payments should be made
 * @param {number} intervalMonths - Interval between payments in months
 * @param {number} amount - Amount to be paid per installment
 * @returns {Array} - Payment schedule
 */
const  generatePaymentSchedule=(startDate, endDate, intervalMonths, amount) =>{
    const schedule = [];
    let currentDate = new Date(startDate);
    let installment = 0;

    while (currentDate <= endDate) {

      // Skip the very first installment if it's exactly the start date
    if (installment === 0 && currentDate.getTime() === startDate.getTime()) {
        currentDate.setMonth(currentDate.getMonth() + intervalMonths);
        installment++;
        continue;
    }

        schedule.push({
            installment: installment,
            dueDate: new Date(currentDate),
            amount: amount
        });

        // Move date forward by interval
        currentDate.setMonth(currentDate.getMonth() + intervalMonths);
        installment++;

    }

    return schedule;
}




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


const onProjectSelection = (e) => {
    formData.value.gracePeriod = e.value.grace_period;
    if(e.value.project_type == 'Bees'){
        formData.value.duration = 5;

    }else if(e.value.project_type == 'Maize'){
        formData.value.duration = 3;

    }else{
        formData.value.duration = 1;
    }
    formData.value.quantity = 1;
    formData.value.date = new Date();
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
                        <Dropdown id="instType" @change="onProjectSelection" filter optionLabel="name" :options="projects"
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
                <div class="field mt-4" v-if="formData?.project?.project_type == 'Bees'">
                    <span class="p-float-label">
                        <InputNumber id="name1" type="text" v-model="formData.gracePeriod" />
                        <label for="name1">Grace Period in months</label>
                    </span>
                </div>
                <div class="field mt-4">
                    <span class="p-float-label">
                        <InputNumber id="name1" type="text" v-model="formData.duration" />
                        <label for="name1">Duration in {{ formData?.project?.duration?.unit }}</label>
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
                    <div class="col-12 md:col-6" v-if="formData?.project?.project_type == 'Goats'">
                        <p class="text-gray-900 font-bold">Benefit</p>
                        <p class="text-gray-900">{{ formData?.project?.benefit + ' ' + formData?.project?.benefit_type
                            }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Monitory Value</p>
                        <p class="text-gray-900">{{ commonService.commaSeparator(formData?.project?.monitory) }}</p>
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
                        <p class="text-gray-900 font-bold">Maturity Date</p>
                        <p class="text-gray-900">{{ maturityDate }}</p>
                    </div>
                    <div class="col-12 md:col-6">
                        <p class="text-gray-900 font-bold">Return Of Investment (Value)</p>
                        <p class="text-gray-900">{{ Number(formData?.project?.benefit) * formData?.quantity * formData?.duration }}</p>
                    </div>
                    <div class="col-12 md:col-6" v-if="formData?.project?.project_type == 'Goats'">
                        <p class="text-gray-900 font-bold">Return Of Investment (Monitory)</p>
                        <p class="text-gray-900">{{ commonService.commaSeparator(formData?.project?.monitory * formData?.project?.benefit * formData?.quantity * formData?.duration) }}</p>
                    </div>
                    <div class="col-12 md:col-6" v-if="formData?.project?.project_type == 'Maize'">
                        <p class="text-gray-900 font-bold">Return Of Investment (Monitory)</p>
                        <p class="text-gray-900">{{ commonService.commaSeparator( (formData?.duration * 2) * (formData?.project?.return_of_investment_value? Number(formData?.project?.return_of_investment_value): 1300000) ) }}</p>
                    </div>
                    <div class="col-12 md:col-6" v-if="formData?.project?.project_type == 'Bees'">
                        <p class="text-gray-900 font-bold">Return Of Investment (Monitory)</p>
                        <p class="text-gray-900">{{ commonService.commaSeparator( (formData?.duration * 12) * (formData?.project?.return_of_investment_value? Number(formData?.project?.return_of_investment_value): 100000) ) }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-fluid">
                <h5>Payment Schedule</h5>

                <div class="grid">
                    <div class="col-12 md:col-12" v-if="paymentSchedule.length == 0">
                        <p class="text-gray-900">No payment schedule available</p>
                    </div>
                    <div class="col-12 md:col-12" v-else>
                        <DataTable :value="paymentSchedule" stripedRows responsiveLayout="scroll" class="w-full">
                        <Column field="installment" header="Installment" class="text-center" />
                        <Column
                            field="dueDate"
                            header="Due Date"
                            :body="(rowData) => commonService.formatDate(rowData.dueDate)"
                            class="text-center"
                        />
                        <Column
                            field="amount"
                            header="Amount"
                            :body="(rowData) => commonService.commaSeparator(rowData.amount)"
                            class="text-right"
                        />
                    </DataTable>

                        <!-- <table class="table-auto w-full border-collapse border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2">Installment</th>
                                    <th class="border border-gray-300 px-4 py-2">Due Date</th>
                                    <th class="border border-gray-300 px-4 py-2">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in paymentSchedule" :key="index">
                                    <td class="border border-gray-300 px-4 py-2 text-center">{{ item.installment }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-center">{{ commonService.formatDate(item.dueDate) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-right">{{ commonService.commaSeparator(item.amount) }}</td>
                                </tr>
                            </tbody>
                        </table> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
