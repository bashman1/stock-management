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
const deleteBuildingUnitDialog = ref(false);
const selectedBuildingUnit = ref(null);

const getBuildings = () => {
    let postData = {
        page: currentPage.value,
        search: searchKeyWord.value,
        rowPerPage: rowsPerPage.value,
        startDate: startDate.value,
        endDate: endDate.value,
        status: 'Active'
    }
    commonService.genericRequest('get-building-units', 'POST', true, postData).then((response) => {
        if (response.status) {
            buildings.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const toggleModal = (action)=>{
    deleteBuildingUnitDialog.value = action;
}

const toggleConfirmDialog = (data)=>{
    selectedBuildingUnit.value = data;
    toggleModal(true);
}

const deleteBuildingUnit =()=>{
    let postData = {
        id : selectedBuildingUnit.value?.id
    }

    commonService.genericRequest('archive-building-unit', 'POST', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getBuildings();
            toggleModal(false);
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const editBuildingUnit = (data)=>{
    commonService.redirect(router, 'update-building-unit/'+data.id);
}

const onPageChange = (event)=>{
    rowsPerPage.value = event.rows;
    currentPage.value = Number(event.page) + 1;
    getBuildings();
}


onMounted(() => {
    getBuildings();
});

</script>
<template>
    <div className="card">
        <h5>View Building Units</h5>
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
                 <Column field="name" header="Unit" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.name }}
                </template>
            </Column>
            <Column field="name" header="Unit No" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.unit_no }}
                </template>
            </Column>
            <Column field="name" header="Ref No" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.ref_no }}
                </template>
            </Column>
            <Column field="name" header="Building" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.building_name	 }}
                </template>
            </Column>
            <Column field="name" header="Rent" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.rent) }}
                </template>
            </Column>
            <Column field="name" header="Status" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.status }}
                </template>
            </Column>
            <Column field="name" header="Description" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.description }}
                </template>
            </Column>
            <Column header="Date" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.created_at }}
                </template>
            </Column>
            <Column headerStyle="min-width:8rem;">
                <template #body="{ data }">
                    <Button icon="pi pi-pencil" @click="editBuildingUnit(data)" class="p-button-success mr-2"
                        v-tooltip="'Edit building unit details'" />
                    <Button icon="pi pi-trash" class="p-button-danger mr-2" @click="toggleConfirmDialog(data)"
                        v-tooltip.left="'Archive building unit'" />
                </template>
            </Column>
        </DataTable>


        <Dialog v-model:visible="deleteBuildingUnitDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="selectedBuildingUnit"
                            >Are you sure you want to archive <b>{{ selectedBuildingUnit?.name +' '+selectedBuildingUnit?.unit_no }}</b
                            >?</span
                        >
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteBuildingUnitDialog = false" />
                        <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteBuildingUnit" />
                    </template>
        </Dialog>

    </div>
</template>
