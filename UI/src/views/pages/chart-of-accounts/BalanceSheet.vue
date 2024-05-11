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
            glOverView.value = response.data;
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
                                <th colspan="3" class="bold">{{ gl.acct_type +' - '+gl.description}}</th>
                            </tr>
                            <tr v-for="(acct, i) in gl.list" :key="i">
                                <td>{{ acct.description }}</td>
                                <td>{{ acct.balance }}</td>
                                <td>{{ acct.balance }}</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
        </div>
    </div>
</template>
