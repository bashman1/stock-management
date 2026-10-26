<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';
const route = useRoute()

const toast = useToast();

const commonService = new CommonService();
const router = useRouter();
const userDetails = ref(null);
const assignedBranches = ref(null);
const selectedBrach = ref(null);
const oldPassword = ref(null);
const newPassword = ref(null);
const confirmPassword = ref(null);
const matchingPassword = ref(true);
const formError = ref({});

const members = ref(null);

const getUserDetail=()=>{
    let postData={
        id: route.params.id,
    }

    commonService.genericRequest('get-member-details', 'post', true, postData).then((response) => {
        if (response.status) {
            userDetails.value = response.data[0];
        } else {
                commonService.showError(toast, response.message);
        }
    })
}



const getAllMembers = () => {
    commonService.genericRequest('get-investments', 'post', true, {member_id: route.params.id}).then((response) => {
        if (response.status) {
            members.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const maturityDate = (duration, startDate, investment_duration)=>{
    if (!duration || !startDate) return '';

    let date = new Date(startDate);
    switch (duration) {
        case 'Daily':
            date.setDate(date.getDate() + (1 * investment_duration) );
            break;
        case 'Weekly':
            date.setDate(date.getDate() + (7 * investment_duration) );
            break;
        case 'Monthly':
            date.setMonth(date.getMonth() + (1 * investment_duration) );
            break;
        case 'Quarterly':
            date.setMonth(date.getMonth() + (3 * investment_duration) );
            break;
        case 'Yearly':
            date.setFullYear(date.getFullYear() + (1 * investment_duration) );
            break;
        default:
            return '';
    }
    return commonService.formatDate(date);
}


const withdrawInvestment = (investment) => {
    commonService.genericRequest('withdraw-investment', 'post', true, { id: investment.id }).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getAllMembers();
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


// const onInputBlur=(value, key)=>{
//     formError.value[key] = commonService.validateFormField(value);
// }



onMounted(() => {
    getUserDetail();
    getAllMembers();
});


</script>

<template>
    <div class="grid">
        <div class="col-12 md:col-3">
            <div class="card p-fluid">
                <h5 class="text-500">Member Details</h5>

                <div class="field">
                    <label for="name1" class="text-900 font-bold">Name: <span class="font-normal"> {{userDetails?.member_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Member ID: <span class="font-normal"> {{userDetails?.member_number}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Phone No.: <span class="font-normal"> {{userDetails?.phone_number}}</span></label>
                </div>

                <div class="field">
                    <label for="name1" class="text-900 font-bold">Gender: <span class="font-normal"> {{userDetails?.gender}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Date of Birth: <span class="font-normal"> {{userDetails?.date_of_birth}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Email: <span class="font-normal"> {{userDetails?.email}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">City: <span class="font-normal"> {{userDetails?.city_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Address: <span class="font-normal"> {{userDetails?.address}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Street: <span class="font-normal"> {{userDetails?.street}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">P.O Box: <span class="font-normal"> {{userDetails?.p_o_box}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Status: <span class="font-normal"> {{userDetails?.status}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Next Of Kin: <span class="font-normal"> {{userDetails?.kin_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Next Of Kin Contact: <span class="font-normal"> {{userDetails?.kin_contact}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Next Of Kin Relationship: <span class="font-normal"> {{userDetails?.kin_relationship	}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Institution: <span class="font-normal"> {{userDetails?.institution_name	}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Branch: <span class="font-normal"> {{userDetails?.branch_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Description: <span class="font-normal"> {{userDetails?.description}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Created By: <span class="font-normal"> {{userDetails?.user_name}}</span></label>
                </div>
            </div>
        </div>

        <div class="col-12 md:col-9">
            <div class="card p-fluid">
                <h5 class="text-500">Investments</h5>
                <div class="grid p-fluid mt-3">

                    <DataTable :value="members" :paginator="true" size="small" class="" :rows="10" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">

                    <!-- <Column field="customer" header="Name" style="min-width: 10rem">
                    </Column> -->
                    <Column field="project_name" header="Project" style="min-width: 10rem">
                    </Column>
                     <Column field="quantity" header="Initial Investment" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ commonService.commaSeparator(data.quantity * data.monitory) }}
                        </template>
                    </Column>
                    <!-- <Column field="benefit" header="Benefit" style="min-width: 12rem">
                    </Column> -->
                    <!-- <Column field="benefit_type" header="Benefit Type" style="min-width: 10rem" /> -->
                    <!-- <Column header="Duration" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.investment_duration +' '+data.duration }}
                        </template>
                    </Column> -->
                    <Column field="status" header="Status" style="min-width: 8rem" />
                        <Column header="Return Of Investment" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ commonService.commaSeparator(data.benefit * data.quantity * data.monitory * data.investment_duration) }}
                        </template>
                    </Column>
                           <Column header="Maturity Date" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ maturityDate(data.duration, data.date, data.investment_duration) }}
                        </template>
                    </Column>
                    <Column header="Date" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.date	 }}
                        </template>
                    </Column>
                    <Column headerStyle="min-width:10rem;">
                        <template #body="{ data }">
                            <Button v-if="data.status == 'Active'" icon="pi pi-wallet" class=" p-button-success mr-2" v-tooltip="'ReInvestment'"
                                 />
                            <Button v-if="data.status == 'Active'" icon="pi pi-credit-card" class=" p-button-warning mt-2" v-tooltip="'Withdraw Investment'"
                                @click="withdrawInvestment(data)" />
                        </template>
                    </Column>

                </DataTable>
                </div>

            </div>

            <div class="card">
                <h5 class="text-500">Transaction History</h5>

                                    <DataTable :value="members" :paginator="true" size="small" class="" :rows="10" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">

                    <!-- <Column field="customer" header="Name" style="min-width: 10rem">
                    </Column> -->
                     <Column field="date" header="Date" style="min-width: 12rem" />
                    <!-- <Column field="project_name" header="Project" style="min-width: 10rem">
                    </Column> -->
                     <Column field="quantity" header="Amount" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ commonService.commaSeparator(data.quantity * data.monitory) }}
                        </template>
                    </Column>
                    <!-- <Column field="benefit" header="Benefit" style="min-width: 12rem">
                    </Column> -->
                    <!-- <Column field="benefit_type" header="Benefit Type" style="min-width: 10rem" /> -->
                    <!-- <Column header="Duration" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.investment_duration +' '+data.duration }}
                        </template>
                    </Column> -->
                    <Column field="status" header="Status" style="min-width: 8rem" />
                        <!-- <Column header="Return Of Investment" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ commonService.commaSeparator(data.benefit * data.quantity * data.monitory * data.investment_duration) }}
                        </template>
                    </Column> -->
                           <!-- <Column header="Maturity Date" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ maturityDate(data.duration, data.date, data.investment_duration) }}
                        </template>
                    </Column> -->


                    <!-- <Column headerStyle="min-width:10rem;">
                        <template #body="{ data }">
                            <Button icon="pi pi-plus" class=" p-button-success mr-2"
                                 />
                            <Button icon="pi pi-trash" class=" p-button-warning mt-2"
                                 />
                        </template>
                    </Column> -->

                </DataTable>

                <!-- <div class="grid p-fluid mt-3">
                    <div class="field col-12 md:col-12">
                        <span class="p-float-label">
                            <Dropdown id="instCity" :options="assignedBranches" v-model="selectedBrach" optionLabel="name"></Dropdown>
                            <label for="instCity">Select Branch</label>
                        </span>
                    </div>
                    <div class="field col-12 md:col-8"></div>
                    <div class="field col-12 md:col-4">
                        <Button @click="switchBranch" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                    </div>
                </div> -->

            </div>
        </div>
    </div>
</template>
