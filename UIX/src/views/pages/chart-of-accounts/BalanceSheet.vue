<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

import CustomerService from '@/service/CustomerService';

const toast = useToast();
const glAccounts = ref(null);
const commonService = new CommonService();
const customerService = new CustomerService();
const router = useRouter();
const route = useRoute();

const glOverView = ref([]);
const summary = ref(null);


const customers = ref();
const expandedRowGroups = ref();
// const toast = useToast();
const onRowGroupExpand = (event) => {
    toast.add({ severity: 'info', summary: 'Row Group Expanded', detail: 'Value: ' + event.data, life: 3000 });
};
const onRowGroupCollapse = (event) => {
    toast.add({ severity: 'success', summary: 'Row Group Collapsed', detail: 'Value: ' + event.data, life: 3000 });
};


const getSeverity = (status) => {
    switch (status) {
        case 'unqualified':
            return 'danger';

        case 'qualified':
            return 'success';

        case 'new':
            return 'info';

        case 'negotiation':
            return 'warning';

        case 'renewal':
            return null;
    }
};

const calculateCustomerTotal = (name) => {
    let total = 0;

    if (customers.value) {
        for (let customer of customers.value) {
            if (customer.representative.name === name) {
                total++;
            }
        }
    }

    return total;
};


const getGlAccountsOverView = () => {
    commonService.genericRequest('get-balance-sheet', 'post', true, {}).then((response) => {
        if (response.status) {
            // response.data = { sections, summary }
            glOverView.value = response.data?.sections ?? [];
            summary.value = response.data?.summary ?? null;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getAccountTotal = (acctArray) => {
    let total = 0;
    acctArray.forEach(element => {
        total = Number(total) + Number(element.balance);
    });
    return total;
}



onMounted(() => {
    getGlAccountsOverView();
    customerService.getCustomersMedium().then((data) => (customers.value = data));
});

</script>

<template>
    <div>
        <div class="card">
            <h4>Balance Sheet</h4>
            <!-- <div class="grid p-fluid mt-3"> -->
                <Toast />

                <Toolbar class="mb-4">
                    <template v-slot:start>
                        <div class="grid p-fluid mt-3">
                            <div class="field col-12 md:col-4">
                                <span class="p-float-label">
                                    <Calendar inputId="calendar" v-model="fromDate"></Calendar>
                                    <label for="calendar">From Date</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-4">
                                <span class="p-float-label">
                                    <Calendar @blur="checkDateFormat($event)" inputId="calendar" v-model="toDate"></Calendar>
                                    <label for="calendar">To Date</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-4">
                                <div>
                                    <Button label="Search" class="p-button-success mr-2" @click="generateIncomeStatement" />
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-slot:end>
                        <div class="my-2">
                            <Button label="CSV" icon="pi pi-file-excel" class="p-button-success mr-2" @click="openNew" />
                            <Button label="PDF" icon="pi pi-file-pdf" class="p-button-danger" @click="confirmDeleteSelected" />
                        </div>
                    </template>
                </Toolbar>

                <table class="income-statement">
                    <tbody>
                        <template v-for="(gl, index) in glOverView" :key="index">
                            <tr>
                                <th colspan="3" class="bold text-900">{{ gl.acct_type +' - '+gl.description}}</th>
                            </tr>
                            <tr v-for="(acct, i) in gl.list" :key="i">
                                <td class="left-align text-900" :class="{'bold':acct.description=='Total'}">{{ acct.description }}</td>
                                <td class="text-900">{{ commonService.commaSeparator(acct.balance) }}</td>
                                <td class="bold text-900">{{ commonService.commaSeparator(acct.total) }}</td>
                            </tr>
                        </template>

                        <!-- Equity section: retained earnings (accumulated net profit) -->
                        <template v-if="summary">
                            <tr>
                                <th colspan="3" class="bold text-900">EQUITY</th>
                            </tr>
                            <tr>
                                <td class="left-align text-900">Retained Earnings</td>
                                <td class="text-900">{{ commonService.commaSeparator(summary.retainedEarnings) }}</td>
                                <td class="text-900"></td>
                            </tr>
                            <tr>
                                <td class="left-align bold text-900">Total Equity</td>
                                <td class="text-900"></td>
                                <td class="bold text-900">{{ commonService.commaSeparator(summary.totalEquityAndRetained) }}</td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Balance check: Assets vs Liabilities + Equity -->
                <div v-if="summary" class="mt-4">
                    <div class="flex justify-content-between border-top-1 surface-border pt-3">
                        <span class="bold text-900">Total Assets</span>
                        <span class="bold text-900">{{ commonService.commaSeparator(summary.totalAssets) }}</span>
                    </div>
                    <div class="flex justify-content-between">
                        <span class="bold text-900">Total Liabilities + Equity</span>
                        <span class="bold text-900">{{ commonService.commaSeparator(summary.totalLiabilitiesAndEquity) }}</span>
                    </div>
                    <div class="mt-2">
                        <Tag v-if="summary.balanced" severity="success" value="Balanced ✓"></Tag>
                        <Tag v-else severity="danger"
                            :value="'Out of balance by ' + commonService.commaSeparator(summary.balanceCheck)"></Tag>
                    </div>
                </div>
        </div>
    </div>
</template>
