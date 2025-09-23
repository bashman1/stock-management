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
        searchKey: searchKeyWord.value,
        rowPerPage: rowsPerPage.value,
        from_date: startDate.value,
        to_date: endDate.value,
        // building_id: 2,
    }
    commonService.genericRequest('rent-arrears-report', 'POST', true, postData).then((response) => {
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
        <h5>Arrears Report</h5>
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
                    {{ data.member_name }}
                </template>
            </Column>
            <Column field="name" header="Building" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.building_name	 }}
                </template>
            </Column>
            <Column field="name" header="Unit No" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.unit_no }}
                </template>
            </Column>
            <Column field="name" header="Expected" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(Number(data.duration) * Number(data.unit_monthly_rent)) }}
                </template>
            </Column>

            <Column field="name" header="Arrears" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator((Number(data.duration) * Number(data.unit_monthly_rent) - Number(data.amount_paid_as_at))) }}
                </template>
            </Column>
            <Column field="name" header="Status" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.status }}
                </template>
            </Column>

            <Column header="Date" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.created_at }}
                </template>
            </Column>
        </DataTable>
    </div>
</template>
