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
    commonService.genericRequest('get-gl-balance', 'post', true, {}).then((response) => {
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
    <div className="card" v-for="(gl, index) in glOverView ">
        <h5>{{ gl[0]?.acct_type + ', Total ' + commonService.commaSeparator(getAccountTotal(gl)) }}</h5>
        <!-- <DataTable :value="gl" class="p-datatable-gridlines" dataKey="id" :rowHover="true" filterDisplay="menu"
            responsiveLayout="scroll">
            <Column field="name" header="Acct No." style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.acct_no }}
                </template>
            </Column>
            <Column field="name" header="Ledger No." style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.gl_no }}
                </template>
            </Column>
            <Column field="name" header="Name" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.description }}
                </template>
            </Column>
            <Column field="name" header="Acct. Balance" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.balance) }}
                </template>
            </Column>
        </DataTable> -->



        <DataTable v-model:expandedRowGroups="expandedRowGroups" :value="customers" tableStyle="min-width: 50rem"
            expandableRowGroups rowGroupMode="subheader" groupRowsBy="representative.name"
            @rowgroup-expand="onRowGroupExpand" @rowgroup-collapse="onRowGroupCollapse" sortMode="single"
            sortField="representative.name" :sortOrder="1">
            <template #groupheader="slotProps">
                <span class="vertical-align-middle ml-2 font-bold line-height-3">{{ slotProps.data.representative.name}}</span>
            </template>
            <Column field="name" header="Acct No." style="width: 20%"></Column>
            <Column field="country" header="Ledger No." style="width: 20%"></Column>
            <Column field="company" header="Name" style="width: 20%"></Column>
            <Column field="status" header="Status" style="width: 20%"></Column>
            <Column field="date" header="Acct Balance" style="width: 20%"></Column>
            <template #groupfooter="slotProps">
                <div class="flex justify-content-end font-bold w-full">Total Customers: {{
        calculateCustomerTotal(slotProps.data.representative.name) }}</div>
            </template>
        </DataTable>


    </div>
</template>
