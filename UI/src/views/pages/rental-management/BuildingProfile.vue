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
const buildingDetails = ref(null);
const buildingUnits = ref(null);
const landLoadPayments = ref(null);
const startDate = ref(null);
const endDate = ref(null);
const rowsPerPage = ref(20);
const searchKeyWord = ref(null);
const currentPage = ref(1);

const getBuildingUnits=()=>{
    let postData={
        page: 1,
        search: null,
        rowPerPage: 1000,
        startDate: null,
        endDate: null,
        building_id: Number(route.params.id)
    }

    commonService.genericRequest('get-building-units', 'post', true, postData).then((response) => {
        if (response.status) {
            buildingUnits.value = response.data;
        } else {
                commonService.showError(toast, response.message);
        }
    })
}


const getBuildingDetails = () => {
        let postData = {
        page: 1,
        search: null,
        rowPerPage: 10,
        startDate: null,
        endDate: null,
        id: Number(route.params.id)
    }
    commonService.genericRequest('get-buildings', 'post', true, postData).then((response) => {
        if (response.status) {
            buildingDetails.value = response?.data?.data[0];
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getLandLoadPayments = () =>{
    let postData = {
        page: currentPage?.value,
        search: searchKeyWord?.value,
        row_per_page: rowsPerPage?.value,
        start_date: startDate?.value,
        end_date: endDate.value,
        building_id: Number(route.params.id)
    }
    commonService.genericRequest('get-land-load-payments', 'post', true, postData).then((response) => {
        if (response.status) {
            landLoadPayments.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const onSearchPayments = ()=>{
    getLandLoadPayments();
}

const onPageChange = (event)=>{
    rowsPerPage.value = event.rows;
    currentPage.value = Number(event.page) + 1;
    getLandLoadPayments();
}


onMounted(() => {
    getBuildingUnits();
    getBuildingDetails();
    getLandLoadPayments();
});


</script>

<template>
    <div class="grid">
        <div class="col-12 md:col-3">
            <div class="card p-fluid">
                <h5 class="text-500">Estate Details</h5>

                <div class="field">
                    <label for="name1" class="text-900 font-bold">Name: <span class="font-normal"> {{buildingDetails?.name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">City: <span class="font-normal"> {{buildingDetails?.city_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Ref. No.: <span class="font-normal"> {{buildingDetails?.ref_no}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Address: <span class="font-normal"> {{buildingDetails?.address}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Street: <span class="font-normal"> {{buildingDetails?.street}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">P.O Box: <span class="font-normal"> {{buildingDetails?.p_o_box}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Status: <span class="font-normal"> {{buildingDetails?.status}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Institution: <span class="font-normal"> {{buildingDetails?.institution_name	}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Branch: <span class="font-normal"> {{buildingDetails?.branch_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Description: <span class="font-normal"> {{buildingDetails?.description}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Created By: <span class="font-normal"> {{buildingDetails?.user_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Created On: <span class="font-normal"> {{buildingDetails?.created_at}}</span></label>
                </div>
            </div>
        </div>

        <div class="col-12 md:col-9">
            <div class="card p-fluid">
                <h5 class="text-500">Units</h5>
                <div class="grid p-fluid mt-3" >
                    <div class="field col-12 md:col-2 " v-for="(unit, index) of buildingUnits?.data" >
                        <i v-if="unit.status == 'Occupied'" class="pi pi-check-square"  style="color: green; font-size: 3rem"></i>
                        <i v-if="unit.status == 'Active'" class="pi pi-stop"  style="color: red; font-size: 3rem"></i>
                        <p style="color: #000">{{unit.name+' '+unit.unit_no}}</p>
                    </div>
                </div>
            </div>

            <div class="card p-fluid">
                <h5 class="text-500">Land Load Payments</h5>
                <div class="grid p-fluid mt-3" >

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
                            <Button @click="onSearchPayments" label="SEARCH" icon="pi pi-search" class="mr-2" />
                        </template>

                        <template #end>
                            <Button label="EXCEL" icon="pi pi-file-excel" class="mr-2"  />
                            <Button label="CSV" icon="pi pi-file-excel" class="mr-2" severity="success"  />
                            <Button label="PDF" icon="pi pi-file-pdf" severity="danger"  />
                        </template>
                    </Toolbar>

                    <DataTable
                    :value="landLoadPayments?.data"
                    :paginator="true"
                    stripedRows
                    size="small"
                    :rows="rowsPerPage"
                    dataKey="id"
                    :rowHover="true"
                    :totalRecords="landLoadPayments?.total"
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
                        <Column field="payment_date" header="Date" style="min-width: 10rem" />
                        <Column field="" header="Amount" style="min-width: 10rem">
                            <template #body="{ data }">
                                {{ commonService.commaSeparator(data.amount) }}
                            </template>
                        </Column>
                        <Column field="payment_type" header="Payment" style="min-width: 10rem" />
                        <Column field="payment_method" header="Method" style="min-width: 10rem" />
                        <Column field="description" header="Description" style="min-width: 10rem" />

                    </DataTable>

                </div>
            </div>
        </div>



    </div>
</template>
