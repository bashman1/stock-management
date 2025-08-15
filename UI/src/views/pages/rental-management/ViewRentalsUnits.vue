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

const getBuildings = () => {
    let postData = {
        page: currentPage.value,
        search: searchKeyWord.value,
        rowPerPage: rowsPerPage.value,
        startDate: startDate.value,
        endDate: endDate.value,
    }
    commonService.genericRequest('get-building-rentals', 'POST', true, postData).then((response) => {
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


onMounted(() => {
    getBuildings();
});

</script>
<template>
    <div className="card">
        <h5>Rentals</h5>
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
                    {{ data.tenant_name }}
                </template>
            </Column>
            <Column field="name" header="Building" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.building_name }}
                </template>
            </Column>
            <Column field="name" header="unit" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.unit }}
                </template>
            </Column>
            <Column field="name" header="Rent" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.unit_rent) +' Per Month' }}
                </template>
            </Column>
            <Column field="name" header="Paid On" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.paid_date  }}
                </template>
            </Column>
              <Column field="name" header="Paid On" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.duration +' Months'}}
                </template>
            </Column>
                     <Column field="name" header="Due on" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.due_date }}
                </template>
            </Column>
            <Column field="name" header="Total" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.unit_rent * data.duration) }}
                </template>
            </Column>
            <Column field="name" header="Paid" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.amount_paid) }}
                </template>
            </Column>
            <Column field="name" header="Balance" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator((data.unit_rent * data.duration) - data.amount_paid) }}
                </template>
            </Column>
            <Column field="name" header="Status" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.status }}
                </template>
            </Column>
            <!-- <Column field="name" header="Description" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.description }}
                </template>
            </Column> -->
            <!-- <Column header="Institution" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.institution_name }}
                </template>
            </Column> -->
            <!-- <Column header="Branch" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.branch_name }}
                </template>
            </Column> -->
            <Column header="Date" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.created_at }}
                </template>
            </Column>
            <Column headerStyle="min-width:13rem;">
                <template #body="{ data }">
                    <Button icon="pi pi-eye"  class="p-button-primary mr-2"
                        v-tooltip="'View  details'" />
                    <Button icon="pi pi-pencil"  class="p-button-success mr-2"
                        v-tooltip="'Edit  details'" />
                    <!-- <Button icon="pi pi-refresh"  class="p-button-warning mr-2"
                        v-tooltip="'Restock product'" /> -->
                    <Button icon="pi pi-trash" class="p-button-danger mr-2"
                        v-tooltip="'Archive Details'" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>
