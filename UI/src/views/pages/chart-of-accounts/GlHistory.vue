<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const glAccountHistory = ref(null);
const commonService = new CommonService();
const router = useRouter();
const route = useRoute()

const updateGlModal = ref(false)
const description = ref(null);
const acctNo = ref(null);
const formError = ref({});
const debitSelected = ref(null);
const balance = ref(0);

const getGlAccountHistory = (acctNo) => {
    let postData = {
        acct_no: acctNo
    }
    commonService.genericRequest('get-gl-history', 'post', true, postData).then((response) => {
        if (response.status) {
            glAccountHistory.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onInputBlur = (value, key) => {
    formError.value[key] = commonService.validateFormField(value);
}

const updateGl = (event) => {
    description.value = event.description
    acctNo.value = event.acct_no
    toggleEditModal(true)

}

const toggleEditModal = (action) => {
    updateGlModal.value = action
}

const updateGlName = () => {
    formError.value.description = commonService.validateFormField(description.value);
    let invalid = commonService.validateRequiredFields(formError.value);
    if (invalid) {
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData = {
        description: description.value,
        acctNo: acctNo.value
    }
    commonService.genericRequest('update-gl-account', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getGlAccounts();
            toggleEditModal(false)
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const calculateBalance = (amount, type, ind) => {
    if (ind == 'Dr') {
        if (type == 'ASSET' || type == 'EXPENSE') {
           return balance.value= Number(balance.value) + Number(amount);
        } else {
           return balance.value= Number(balance.value) - Number(amount);
        }
    }
    if (ind == 'Cr') {
        if (type == 'ASSET' || type == 'EXPENSE') {
          return  balance.value= Number(balance.value) - Number(amount);
        } else {
           return balance.value= Number(balance.value) + Number(amount);
        }
    }
}

const debitAccount = (event) => {

}

const creditAccount = (event) => {

}

onMounted(() => {
    getGlAccountHistory(route.params.acctNo)
});

</script>

<template>
    <div className="card">
        <h5>Ledger account statement</h5>
        <DataTable :value="glAccountHistory" :paginator="true" class="p-datatable-gridlines" :rows="20" dataKey="id"
            :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
            <ColumnGroup type="header">
                <Row>
                    <Column header="Acct Info" :rowspan="3" />
                    <Column :header="'Account Name: '+route.params.acctNo" :colspan="6" />
                </Row>
                <Row>
                    <Column :header="'Account Number : '+route.params.acctNo" :colspan="6" />
                </Row>
                <Row>
                    <Column :header="'Opening Balance: '+0" :colspan="6" />
                </Row>
                <Row>
                    <Column header="Transaction Date" sortable field="transaction_date" />
                    <Column header="Description" sortable field="description" />
                    <Column header="Debit" sortable field="dr" />
                    <Column header="Credit" sortable field="cr" />
                    <Column header="Balance" sortable field="balance" />
                    <Column header="Reversed" sortable field="reversal_flag" />
                    <Column header="Posted By" sortable field="user_name" />
                </Row>
                
            </ColumnGroup>
            <Column field="transaction_date" header="Transaction Date" style="min-width: 12rem">
                <template #body="{ data }">
                    {{ data.transaction_date }}
                </template>
            </Column>
            <Column field="description" header="Description" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.description }}
                </template>
            </Column>
            <Column field="dr" header="Debit" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.dr_cr_ind == 'Dr' ? commonService.commaSeparator(data.tran_amount) : '' }}
                </template>
            </Column>
            <Column field="cr" header="Credit" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.dr_cr_ind == 'Cr' ? commonService.commaSeparator(data.tran_amount) : '' }}
                </template>
            </Column>
            <Column field="balance" header="Balance" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.balance) }}
                </template>
            </Column>
            <Column field="reversal_flag" header="Reversed" style="max-width: 10rem">
                <template #body="{ data }">
                    {{ data.reversal_flag == 'N' ? 'No' : 'Yes' }}
                </template>
            </Column>
            <Column field="name" header="Posted By" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.user_name }}
                </template>
            </Column>
        </DataTable>

        <Dialog header="Update Ledger Name" v-model:visible="updateGlModal" :style="{ width: '30vw' }" :modal="true"
            class="p-fluid">
            <p style="padding-top: 20px;">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="description" @blur="onInputBlur(description, 'description')" rows="7"
                            v-model="description" :class="{ 'p-invalid': formError?.description }"></Textarea>
                        <label for="description">Description</label>
                    </span>
                </div>
            </div>
            </p>
            <template #footer>
                <Button label="Cancel" @click="toggleEditModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="updateGlName" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>

    </div>
</template>
