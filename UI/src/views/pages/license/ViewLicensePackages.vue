<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const packages = ref(null);
const commonService = new CommonService();
const packageDetails = ref(null);
const showPackageDetailsModal = ref(false);
//  const router = useRouter();ges

const getPackages = (currentPage) => {
    commonService.genericRequest(`get-license-packages?page=${currentPage}`, 'get', false, {}).then((response) => {
        if (response.status) {
            packages.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onPageChange = (event) => {
    getPackages(event.page + 1)
    // {"originalEvent":{"page":0,"first":0,"rows":15,"pageCount":32},"first":0,"rows":15,"sortField":null,"sortOrder":null,"multiSortMeta":[],"filters":{},"pageCount":32,"page":0}
}

const toggleModal = (action) => {
    showPackageDetailsModal.value = action;
}

const viewDetails = (data) => {
    packageDetails.value = data;
    showPackageDetailsModal.value = commonService.openModal();
}

const editPackage = (data) => {

}

const deletePackage = (data) => {

}

onMounted(() => {
    getPackages(1);
});

</script>
<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <h5>License Packages</h5>

                <DataTable :value="packages?.data" :paginator="true" class="p-datatable-gridlines" lazy
                    :totalRecords="packages?.total" @page="onPageChange($event)" :rows="25" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
                    <Column field="package_name" header="Name" style="min-width: 8rem">
                        <template #body="{ data }">
                            {{ data.package_name }}
                        </template>
                    </Column>
                    <Column field="description" header="description" style="min-width: 8rem">
                        <template #body="{ data }">
                            {{ data.description }}
                        </template>
                    </Column>
                    <Column field="status" header="Status" style="min-width: 8rem">
                        <template #body="{ data }">
                            {{ data.status }}
                        </template>
                    </Column>
                    <Column field="Prices" header="Prices" style="min-width: 8rem">
                        <template #body="{ data }">
                            <span v-for="price in JSON.parse(data.license_prices)">
                                {{ price.duration + ': ' + commonService.commaSeparator(price.amount) }}
                                <br>
                            </span>
                        </template>
                    </Column>
                    <Column field="implementation" header="Implementation Fees" style="min-width: 8rem">
                        <template #body="{ data }">
                            {{ commonService.commaSeparator(data.implementation) }}
                        </template>
                    </Column>

                    <Column field="user_name" header="Created By" style="min-width: 8rem">
                        <template #body="{ data }">
                            {{ data.user_name }}
                        </template>
                    </Column>

                    <Column header="Date" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ commonService.formatDate(new Date(data.created_at)) }}
                        </template>
                    </Column>

                    <Column headerStyle="min-width:10rem;">
                        <template #body="{ data }">
                            <Button icon="pi pi-eye" class="p-button p-button-info mr-2" @click="viewDetails(data)"
                                v-tooltip="'View Details'" />
                            <Button icon="pi pi-pencil" class="p-button p-button-success mr-2"
                                @click="editPackage(data)" v-tooltip="'Edit'" />
                            <Button icon="pi pi-trash" class="p-button p-button-warning mt-2"
                                @click="deletePackage(data)" v-tooltip="'Delete'" />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>


        <Dialog header="Sales Details" v-model:visible="showPackageDetailsModal" :style="{ width: '40vw' }"
            :modal="true" class="p-fluid">

            <!-- Package Details -->
            <div class="grid p-fluid">
                <div class="field col-12">
                    <h3>{{ packageDetails.package_name }}</h3>
                </div>
                <div class="field col-6">
                    <label><strong>Implementation Fee:</strong> {{
                        commonService.commaSeparator(packageDetails.implementation)
                        }}</label>
                </div>
                <div class="field col-6">
                    <label><strong>Status:</strong> {{ packageDetails.status }}</label>
                </div>
                <div class="field col-12">
                    <label><strong>Description:</strong> {{ packageDetails.description }}</label>
                </div>
                <div class="field col-12">
                    <label><strong>Created On:</strong> {{ packageDetails.created_at }}</label>
                </div>
            </div>

            <!-- License Prices -->
            <div class="field col-12 mt-3">
                <h4>License Prices</h4>
                <DataTable :value="JSON.parse(packageDetails.license_prices)" class="p-datatable-gridlines" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
                    <Column field="duration" header="Duration" style="min-width: 8rem">
                        <template #body="{ data }">
                            {{ data.duration }}
                        </template>
                    </Column>
                    <Column field="amount" header="Amount" style="min-width: 8rem">
                        <template #body="{ data }">
                            {{ commonService.commaSeparator(data.amount) }}
                        </template>
                    </Column>
                    <Column field="status" header="Status" style="min-width: 8rem">
                        <template #body="{ data }">
                            {{ data.status }}
                        </template>
                    </Column>
                    <Column header="Date" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ commonService.formatDate(new Date(data.created_at)) }}
                        </template>
                    </Column>
                </DataTable>

            </div>

            <!-- Package Benefits -->
            <div class="field col-12 mt-3">
                <h4>Package Benefits</h4>
                <ul>
                    <li v-for="(benefit, index) in JSON.parse(packageDetails.benefits)" :key="index">
                        {{ benefit.benefit }}
                    </li>
                </ul>
            </div>


            <template #footer>
                <Button label="Cancel" @click="toggleModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <!-- <Button @click="printReceipt" icon="pi pi-print" label="Print" class="p-button-outlined mr-2 mb-2" /> -->
            </template>
        </Dialog>


    </div>
</template>
