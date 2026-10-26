<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();

const transactions = ref(null);
const selectedTransactions = ref(null);
const displayApproveConfirmation = ref(null);
const displayDeclineConfirmation = ref(null);
const displayMessage = ref(null);

const getPendingTransactions = () => {
    commonService.genericRequest('get-pending-transactions/Pending', 'get', true, {}).then((response) => {
        if (response.status) {
            transactions.value = response.data
        } else {

        }
    })
}

const confirmApprove = () => {
    displayMessage.value = "Are you sure you want to approve the selected transactions?"
    displayApproveConfirmation.value = commonService.toggleModal(true);
}

const confirmDecline = () => {
    displayMessage.value = "Are you sure you want to decline the selected transactions?"
    displayDeclineConfirmation.value = commonService.toggleModal(true);
}

const closeConfirmation = () => {
    displayApproveConfirmation.value = commonService.toggleModal(false);
    displayDeclineConfirmation.value = commonService.toggleModal(false);
}

const onSubmit = (action) => {
    let postData = {
        action: action,
        tranList: selectedTransactions.value,
    }
    closeConfirmation();
    commonService.genericRequest('approve-transactions', 'post', true, postData).then((response) => {
        if (response.status) {
            selectedTransactions.value=[];
            getPendingTransactions();
            commonService.showSuccess(toast, response.message);
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

onMounted(() => {
    getPendingTransactions();
});
</script>

<template>
    <div class="grid">
        <div class="col-12">
            <div className="card">
                <h5>Approve Transactions</h5>
                <Toast />

                <Dialog header="Confirmation" v-model:visible="displayApproveConfirmation" :style="{ width: '550px' }"
                    :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>Are you sure you want to approve the selected transactions?</span>
                    </div>
                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button label="Confirm" icon="pi pi-check" @click="onSubmit('Approved')" class="p-button-text"
                            autofocus />
                    </template>
                </Dialog>

                <Dialog header="Confirmation" v-model:visible="displayDeclineConfirmation" :style="{ width: '550px' }"
                    :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>Are you sure you want to decline the selected transactions?</span>
                    </div>
                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button label="Confirm" icon="pi pi-check" @click="onSubmit('Declined')" class="p-button-text"
                            autofocus />
                    </template>
                </Dialog>
                <Toolbar class="mb-4">
                    <template v-slot:start>
                        <div class="my-2">
                            <Button label="Approve" icon="pi pi-check" class="p-button-success" @click="confirmApprove"
                                :disabled="!selectedTransactions || !selectedTransactions.length" />
                            <Button label="Reject" icon="pi pi-times" class="p-button-danger" @click="confirmDecline"
                                :disabled="!selectedTransactions || !selectedTransactions.length" />
                        </div>
                    </template>

                    <template v-slot:end>
                        <!--                        <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" chooseLabel="Import" class="mr-2 inline-block" />-->
                        <!--                        <Button label="Export" icon="pi pi-upload" class="p-button-help" @click="exportCSV($event)" />-->
                    </template>
                </Toolbar>
                <DataTable :value="transactions" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll" v-model:selection="selectedTransactions"
                    :rowsPerPageOptions="[5, 10, 25]">
                    <Column field="Date" header="Date" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.tran_date }}
                        </template>
                    </Column>
                    <Column header="Amount" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ commonService.commaSeparator(data.amount) }}
                        </template>
                    </Column>
                    <Column header="Member Name" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.member_name }}
                        </template>
                    </Column>
                    <Column header="Status" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.status }}
                        </template>
                    </Column>
                    <Column header="Officer" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.officer_name }}
                        </template>
                    </Column>
                    <!-- <Column header="Date" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.created_at }}
                        </template>
                    </Column> -->
                    <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                </DataTable>
        </div>
    </div>
</div></template>
