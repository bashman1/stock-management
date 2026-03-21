<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import CommonService from '@/service/CommonService';

const toast = useToast();
const commonService = new CommonService();

// Data refs
const efrisQueue = ref(null);

// Modal refs
const showDetailsDialog = ref(false);
const selectedRecord = ref(null);

// Retry confirmation dialog
const showRetryDialog = ref(false);
const retryRecordId = ref(null);
const retryLoading = ref(false);

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

// View details function
const viewDetails = (data) => {
    selectedRecord.value = data;
    showDetailsDialog.value = true;
};

// Parse JSON safely
const parseJson = (jsonString) => {
    if (!jsonString) return null;
    try {
        return JSON.parse(jsonString);
    } catch (e) {
        return null;
    }
};

// Get status info
const getStatusInfo = (record) => {
    const response = parseJson(record.response);
    if (!response || !response.status) {
        return { success: null, returnCode: record.status, returnMessage: record.status };
    }
    return response.status;
};

// Retry functions
const confirmRetry = (data) => {
    retryRecordId.value = data.id;
    showRetryDialog.value = true;
};

const retryTransaction = () => {
    if (!retryRecordId.value) return;

    retryLoading.value = true;
    const postData = { id: retryRecordId.value };

    commonService.genericRequest('consume-efris-outbound', 'POST', true, postData)
        .then((response) => {
            if (response.status) {
                commonService.showSuccess(toast, 'Transaction retry initiated successfully');
                getEfrisOutBound();
            } else {
                commonService.showError(toast, response.message || 'Failed to retry transaction');
            }
        })
        .catch((error) => {
            commonService.showError(toast, 'Error retrying transaction: ' + error.message);
        })
        .finally(() => {
            retryLoading.value = false;
            showRetryDialog.value = false;
            retryRecordId.value = null;
        });
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
                    {{ commonService.formatDate(data.created_at) }}
                </template>
            </Column>

            <Column headerStyle="min-width: 10rem;">
                <template #body="{ data }">
                    <Button icon="pi pi-eye" v-tooltip="'View Details'" class="p-button-primary mr-2" @click="viewDetails(data)" />
                    <Button icon="pi pi-refresh" v-tooltip="'Retry'" class="p-button-warning mr-2" v-if="!data.is_sent" @click="confirmRetry(data)" />
                    <Button icon="pi pi-download" v-tooltip="'Download'" class="p-button-success mr-2" />
                </template>
            </Column>
        </DataTable>

        <!-- Retry Confirmation Dialog -->
        <Dialog v-model:visible="showRetryDialog" modal header="Confirm Retry" :style="{ width: '30vw' }">
            <div class="flex align-items-center justify-content-center mb-3">
                <i class="pi pi-exclamation-triangle mr-2 text-4xl text-yellow-500"></i>
                <span>Are you sure you want to retry this transaction?</span>
            </div>
            <div class="flex justify-content-end gap-2">
                <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="showRetryDialog = false" :disabled="retryLoading" />
                <Button label="Retry" icon="pi pi-refresh" class="p-button-warning" @click="retryTransaction" :loading="retryLoading" />
            </div>
        </Dialog>

        <!-- View Details Dialog -->
        <Dialog v-model:visible="showDetailsDialog" modal header="Transaction Details" :style="{ width: '80vw' }" :maximizable="true">
            <div v-if="selectedRecord" class="grid">
                <!-- Basic Info -->
                <div class="col-12 md:col-6">
                    <Panel header="Basic Information" class="mb-2">
                        <div class="grid">
                            <div class="col-6 font-bold">Reference No:</div>
                            <div class="col-6">{{ selectedRecord.reference_no }}</div>
                            <div class="col-6 font-bold">Status:</div>
                            <div class="col-6">
                                <Tag :severity="getStatusInfo(selectedRecord).success === true ? 'success' : getStatusInfo(selectedRecord).success === false ? 'danger' : 'warn'">
                                    {{ getStatusInfo(selectedRecord).returnMessage || selectedRecord.status }}
                                </Tag>
                            </div>
                            <div class="col-6 font-bold">Return Code:</div>
                            <div class="col-6">{{ getStatusInfo(selectedRecord).returnCode }}</div>
                            <div class="col-6 font-bold">URI:</div>
                            <div class="col-6">{{ selectedRecord.uri }}</div>
                            <div class="col-6 font-bold">HTTP Method:</div>
                            <div class="col-6">{{ selectedRecord.http_method }}</div>
                            <div class="col-6 font-bold">Sent:</div>
                            <div class="col-6">{{ selectedRecord.is_sent ? 'Yes' : 'No' }}</div>
                            <div class="col-6 font-bold">Created:</div>
                            <div class="col-6">{{ selectedRecord.created_at }}</div>
                        </div>
                    </Panel>
                </div>

                <!-- Request/Response Status -->
                <div class="col-12 md:col-6">
                    <Panel header="Response Summary" class="mb-2">
                        <div class="grid">
                            <div class="col-6 font-bold">Success:</div>
                            <div class="col-6">
                                <Tag v-if="getStatusInfo(selectedRecord).success === true" severity="success">SUCCESS</Tag>
                                <Tag v-else-if="getStatusInfo(selectedRecord).success === false" severity="danger">FAILED</Tag>
                                <Tag v-else severity="warn">PENDING</Tag>
                            </div>
                            <div class="col-6 font-bold">Message:</div>
                            <div class="col-6">{{ getStatusInfo(selectedRecord).returnMessage || 'N/A' }}</div>
                        </div>
                    </Panel>
                </div>

                <!-- Payload -->
                <div class="col-12">
                    <Panel header="Request Payload" toggleable collapsed class="mb-2">
                        <pre class="p-3 surface-50 border-round" style="max-height: 400px; overflow: auto;">{{ parseJson(selectedRecord.payload) ? JSON.stringify(parseJson(selectedRecord.payload), null, 2) : selectedRecord.payload }}</pre>
                    </Panel>
                </div>

                <!-- Response -->
                <div class="col-12">
                    <Panel header="Response" toggleable collapsed class="mb-2">
                        <pre class="p-3 surface-50 border-round" style="max-height: 400px; overflow: auto;">{{ parseJson(selectedRecord.response) ? JSON.stringify(parseJson(selectedRecord.response), null, 2) : selectedRecord.response || 'No response yet' }}</pre>
                    </Panel>
                </div>

                <!-- Additional Details -->
                <div class="col-12 md:col-6" v-if="selectedRecord.order_id">
                    <Panel header="Order Info" class="mb-2">
                        <div class="grid">
                            <div class="col-6 font-bold">Order ID:</div>
                            <div class="col-6">{{ selectedRecord.order_id }}</div>
                            <div class="col-6 font-bold">Invoice ID:</div>
                            <div class="col-6">{{ selectedRecord.invoice_id || 'N/A' }}</div>
                        </div>
                    </Panel>
                </div>

                <div class="col-12 md:col-6" v-if="selectedRecord.description">
                    <Panel header="Description" class="mb-2">
                        <p>{{ selectedRecord.description }}</p>
                    </Panel>
                </div>
            </div>
        </Dialog>
    </div>
</template>
