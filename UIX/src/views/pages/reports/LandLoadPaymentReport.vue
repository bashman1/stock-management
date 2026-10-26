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
const landLoadPayments = ref(null);

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


const getLandLoadPayments = () =>{
    let postData = {
        page: currentPage?.value,
        search: searchKeyWord?.value,
        row_per_page: rowsPerPage?.value,
        start_date: startDate?.value,
        end_date: endDate.value,
        // building_id: Number(route.params.id)
    }
    commonService.genericRequest('get-land-load-payments', 'post', true, postData).then((response) => {
        if (response.status) {
            landLoadPayments.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


onMounted(() => {
    getLandLoadPayments();
});

</script>
<template>
    <div className="card">
        <h5>Land Load Payments Report</h5>
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

                    <DataTable scrollable scrollHeight="65vh"
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
</template>
