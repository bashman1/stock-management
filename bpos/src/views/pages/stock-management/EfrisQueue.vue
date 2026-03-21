<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import CommonService from '@/service/CommonService';

const toast = useToast();
const commonService = new CommonService();

// Data refs
const efrisQueue = ref(null);

// Pagination
const rowsPerPage = ref(15);
const currentPage = ref(1);
const totalRecords = ref(0);

// Search and filters
const searchKeyWord = ref(null);
const startDate = ref(null);
const endDate = ref(null);

// Filter options
const status = ref(null);
const isSent = ref(null);
const transactionType = ref(null);
const action = ref(null);

// Status options
const statusOptions = ref([
    { label: 'Active', value: 'Active' },
    { label: 'Pending', value: 'Pending' },
    { label: 'Completed', value: 'Completed' },
    { label: 'Failed', value: 'Failed' },
    { label: 'Cancelled', value: 'Cancelled' }
]);

// Transaction type options
const transactionTypeOptions = ref([
    { label: 'Invoice', value: 'Invoice' },
    { label: 'Credit Note', value: 'Credit Note' },
    { label: 'Debit Note', value: 'Debit Note' },
    { label: 'Restock', value: 'Restock' }
]);

// Action options
const actionOptions = ref([
    { label: 'Create', value: 'Create' },
    { label: 'Update', value: 'Update' },
    { label: 'Delete', value: 'Delete' }
]);

// Is sent options
const isSentOptions = ref([
    { label: 'Yes', value: true },
    { label: 'No', value: false }
]);

// Get Efris Outbound Transactions
const getEfrisOutBound = () => {
    let postData = {
        page: currentPage.value,
        per_page: rowsPerPage.value,
        search: searchKeyWord.value,
        status: status.value?.value || status.value,
        is_sent: isSent.value?.value !== undefined ? isSent.value.value : isSent.value,
        trnsaction_type: transactionType.value?.value || transactionType.value,
        action: action.value?.value || action.value,
        start_date: startDate.value,
        end_date: endDate.value
    };

    commonService.genericRequest('efris-outbound-transactions', 'GET', true, postData).then((response) => {
        if (response.status) {
            efrisQueue.value = response.data;
            totalRecords.value = response.data.total || 0;
        } else {
            commonService.showError(toast, response.message);
        }
    });
};

// Handle page change
const onPageChange = (event) => {
    rowsPerPage.value = event.rows;
    currentPage.value = Number(event.page) + 1;
    getEfrisOutBound();
};

// Search function
const searchRecords = () => {
    currentPage.value = 1;
    getEfrisOutBound();
};

// Handle filter changes - auto-search when status or other filters change
const onFilterChange = () => {
    currentPage.value = 1;
    getEfrisOutBound();
};

// Clear filters
const clearFilters = () => {
    searchKeyWord.value = null;
    startDate.value = null;
    endDate.value = null;
    status.value = null;
    isSent.value = null;
    transactionType.value = null;
    action.value = null;
    currentPage.value = 1;
    getEfrisOutBound();
};

// Initialize on mount
onMounted(() => {
    getEfrisOutBound();
});
</script>

<template>
    <div className="card">
        <h5>EFRIS Outbound Queue</h5>

        <!-- Toolbar with filters -->
        <Toolbar class="mb-1">
            <template #start>
                <!-- <span class="p-float-label mr-2">
                    <Calendar id="startDate" showIcon v-model="startDate"></Calendar>
                    <label for="startDate">Start Date</label>
                </span>
                <span class="p-float-label mr-2">
                    <Calendar id="endDate" showIcon fluid iconDisplay="input" v-model="endDate"></Calendar>
                    <label for="endDate">End Date</label>
                </span> -->
                <span class="p-float-label mr-2">
                    <Dropdown
                        id="status"
                        v-model="status"
                        :options="statusOptions"
                        optionLabel="label"
                        placeholder="Select Status"
                        showClear
                        @change="onFilterChange"
                    ></Dropdown>
                </span>
                <!-- <span class="p-float-label mr-2">
                    <Dropdown
                        id="isSent"
                        v-model="isSent"
                        :options="isSentOptions"
                        optionLabel="label"
                        placeholder="Is Sent"
                        showClear
                    ></Dropdown>
                </span> -->
                <!-- <span class="p-float-label mr-2">
                    <Dropdown
                        id="transactionType"
                        v-model="transactionType"
                        :options="transactionTypeOptions"
                        optionLabel="label"
                        placeholder="Transaction Type"
                        showClear
                    ></Dropdown>
                </span> -->
                <!-- <span class="p-float-label mr-2">
                    <Dropdown
                        id="action"
                        v-model="action"
                        :options="actionOptions"
                        optionLabel="label"
                        placeholder="Action"
                        showClear
                    ></Dropdown>
                </span> -->
                <span class="p-float-label mr-2">
                    <InputText type="text" id="search" v-model="searchKeyWord" placeholder="Search..." @keyup.enter="searchRecords" />
                </span>
                <Button @click="searchRecords" label="SEARCH" icon="pi pi-search" class="mr-2" />
                <Button @click="clearFilters" label="CLEAR" icon="pi pi-times" class="p-button-outlined p-button-secondary" />
            </template>

            <template #end>
                <Button label="EXCEL" icon="pi pi-file-excel" class="mr-2" />
                <Button label="CSV" icon="pi pi-file-excel" class="mr-2" severity="success" />
                <Button label="PDF" icon="pi pi-file-pdf" severity="danger" />
            </template>
        </Toolbar>

        <!-- Data Table -->
        <DataTable
            :value="efrisQueue?.data"
            :paginator="true"
            stripedRows
            size="small"
            :rows="rowsPerPage"
            :first="(currentPage - 1) * rowsPerPage"
            dataKey="id"
            :rowHover="true"
            :totalRecords="totalRecords"
            filterDisplay="menu"
            responsiveLayout="scroll"
            lazy
            @page="onPageChange($event)"
            :rowsPerPageOptions="[5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 100]"
        >
            <Column header="#" style="width: 3rem; text-align: center;">
                <template #body="{ index }">
                    {{ index + 1 }}
                </template>
            </Column>

            <Column field="reference_no" header="Reference No" style="min-width: 12rem">
                <template #body="{ data }">
                    {{ data.reference_no }}
                </template>
            </Column>

            <!-- <Column field="trnsaction_type" header="Transaction Type" style="min-width: 12rem">
                <template #body="{ data }">
                    {{ data.trnsaction_type }}
                </template>
            </Column> -->

            <Column field="status" header="Status" style="min-width: 10rem">
                <template #body="{ data }">
                    <Tag :severity="data.status === 'Completed' ? 'success' : data.status === 'Pending' ? 'warn' : data.status === 'Failed' ? 'danger' : 'info'">
                        {{ data.status }}
                    </Tag>
                </template>
            </Column>

            <!-- <Column field="action" header="Action" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.action }}
                </template>
            </Column> -->

            <!-- <Column field="description" header="Description" style="min-width: 15rem">
                <template #body="{ data }">
                    {{ data.description }}
                </template>
            </Column> -->

            <Column field="is_sent" header="Sent" style="min-width: 8rem">
                <template #body="{ data }">
                    <Tag :severity="data.is_sent ? 'success' : 'warning'">
                        {{ data.is_sent ? 'Yes' : 'No' }}
                    </Tag>
                </template>
            </Column>

            <Column field="uri" header="URI" style="min-width: 15rem">
                <template #body="{ data }">
                    {{ data.uri }}
                </template>
            </Column>

            <Column field="created_at" header="Date" style="min-width: 12rem">
                <template #body="{ data }">
                    {{ data.created_at }}
                </template>
            </Column>

            <Column headerStyle="min-width: 10rem;">
                <template #body="{ data }">
                    <Button icon="pi pi-eye" v-tooltip="'View Details'" class="p-button-primary mr-2" />
                    <Button icon="pi pi-refresh" v-tooltip="'Retry'" class="p-button-warning mr-2" v-if="!data.is_sent" />
                    <Button icon="pi pi-download" v-tooltip="'Download'" class="p-button-success mr-2" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>
