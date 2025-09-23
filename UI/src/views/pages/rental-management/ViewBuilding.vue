<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const commonService = new CommonService();
const toast = useToast();
const router = useRouter();
const route = useRoute();

const startDate = ref(null);
const endDate = ref(null);
const rowsPerPage = ref(20);
const searchKeyWord = ref(null);
const currentPage = ref(1);

const buildings = ref([]);
const showLandLoadPaymentModal = ref(false);
const showMaintenancePaymentModal = ref(false);
const selectedBuilding = ref(null);
const formData = ref({});
const paymentType = ref([
    {name: 'Rent', code: 'RENT'},
    {name: 'Maintenance', code: 'MAINTENANCE'},
])

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

const onPageChange = (event)=>{
    rowsPerPage.value = event.rows;
    currentPage.value = Number(event.page) + 1;
    getBuildings();
}


const goToDetails= (data)=>{
    commonService.redirect(router, 'view-building-details/'+data.id);
}

const payLandLoad= (data)=>{
    // commonService.redirect(router, 'pay-building-load/'+data.id);
}

const toggleLandLoadPaymentModal = (status) => {
    showLandLoadPaymentModal.value = status;
}


const onLandLoadPayment =(data)=>{
    selectedBuilding.value = data;
    toggleLandLoadPaymentModal(true);
    formData.value = {};
}

const onSubmitPayments=()=>{
    if(!formData.value.amountPaid){
        commonService.showError(toast, "Please enter amount");
        return;
    }
    if(!formData.value.date){
        commonService.showError(toast, "Please select date");
        return;
    }
    if(!formData.value.paymentType){
        commonService.showError(toast, "Please select payment type");
        return;
    }

    let postData = {
        building_id: selectedBuilding?.value.id,
        amount: formData.value?.amountPaid,
        date: formData.value?.date,
        payment_type: formData.value?.paymentType?.code,
        description: formData.value?.description,
    }
    commonService.genericRequest('landlord-payments', 'POST', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            toggleLandLoadPaymentModal(false);
            formData.value = {};
            getBuildings();
        } else {
            commonService.showError(toast, response.message);
        }
    })
}




onMounted(() => {
    getBuildings();
});

</script>
<template>
    <div className="card">
        <h5>View Buildings</h5>
        <Toolbar class="mb-1">
            <template #start>
                <span class="p-float-label mr-2">
                    <Calendar id="date" showIcon v-model="startDate"></Calendar>
                    <label for="date">Start Date</label>
                </span>
                <span class="p-float-label mr-2">
                    <Calendar id="date" showIcon fluid iconDisplay="input" v-model="endDate"></Calendar>
                    <label for="date">End Date</label>
                </span>
                <span class="p-float-label mr-2">
                    <InputText type="text" id="purchasePrice" v-model="searchKeyWord" />
                    <label for="purchasePrice">Search </label>
                </span>
                <Button @click="getBuildings" label="SEARCH" icon="pi pi-search" class="mr-2" />
            </template>

            <template #end>
                <Button label="EXCEL" icon="pi pi-file-excel" class="mr-2"  />
                <Button label="CSV" icon="pi pi-file-excel" class="mr-2" severity="success"  />
                <Button label="PDF" icon="pi pi-file-pdf" severity="danger"  />
            </template>
        </Toolbar>

        <DataTable
        :value="buildings?.data"
        :paginator="true"
        stripedRows
        size="small"
        :rows="rowsPerPage"
        dataKey="id"
        :rowHover="true"
        :totalRecords="buildings?.total"
        filterDisplay="menu"
        responsiveLayout="scroll"
        lazy
        @page="onPageChange($event)"
        :rowsPerPageOptions="[5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 100]"
            >
            <Column header="#" style="width: 3rem; text-align: center;">
                <template #body="{ index }">
                    {{  index + 1 }}
                </template>
            </Column>
                 <Column field="name" header="Name" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.name }}
                </template>
            </Column>
            <Column field="name" header="City" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.city_name }}
                </template>
            </Column>
            <Column field="name" header="Plot. No" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.plot }}
                </template>
            </Column>
            <Column field="name" header="Address" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.address }}
                </template>
            </Column>
            <Column field="name" header="Status" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.status }}
                </template>
            </Column>
            <Column field="name" header="Ref No." style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.ref_no }}
                </template>
            </Column>
            <Column header="Institution" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.institution_name }}
                </template>
            </Column>
            <Column header="Branch" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.branch_name }}
                </template>
            </Column>
            <Column header="Date" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.created_at }}
                </template>
            </Column>
            <Column headerStyle="min-width:12rem;">
                <template #body="{ data }">
                    <Button icon="pi pi-eye" @click="goToDetails(data)" class="p-button-primary mr-2"
                        v-tooltip="'View product details'" />
                    <Button icon="pi pi-money-bill" @click="onLandLoadPayment(data)" class="p-button-success mr-2"
                        v-tooltip="'Pay land load'" />
                    <!-- <Button icon="pi pi-wrench" @click="onMaintenancePayment(data)" class="p-button-warning mr-2"
                        v-tooltip="'Maintenance Cost'" /> -->
                    <!-- <Button icon="pi pi-trash" class="p-button-danger mr-2" @click="toggleConfirmDialog(data)"
                        v-tooltip="'Archive product'" /> -->
                </template>
            </Column>
        </DataTable>

        <Dialog header="Make Land Load Payment" v-model:visible="showLandLoadPaymentModal" :style="{ width: '33vw' }" :modal="true"
            class="p-fluid">
                <div class="field mt-4 mb-5">
                    <p class="text-900 font-bold">Land load: <span class="font-normal"> {{selectedBuilding?.name }}</span></p>
                    <span class="p-float-label mb-5">
                        <InputNumber id="name1" type="text" v-model="formData.amountPaid" />
                        <label for="name1">Amount</label>
                    </span>
                    <span class="p-float-label mb-5">
                        <Calendar id="date" showIcon fluid iconDisplay="input" v-model="formData.date"></Calendar>
                        <label for="date">Date</label>
                    </span>
                    <span class="p-float-label mb-5">
                        <Dropdown id="supplier"  :options=" paymentType" filter v-model="formData.paymentType" optionLabel="name">
                        </Dropdown>
                        <label for="supplier">Payment</label>
                    </span>
                     <span class="p-float-label mb-5">
                        <Textarea type="text" id="purchasePrice" v-model="formData.description" />
                        <label for="purchasePrice">More Info </label>
                    </span>
                </div>
            <template #footer>
                <Button label="Cancel" @click="toggleLandLoadPaymentModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="onSubmitPayments"  label="Pay Payments" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>
    </div>
</template>
