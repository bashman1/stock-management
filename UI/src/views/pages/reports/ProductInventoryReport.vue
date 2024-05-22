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

const getGlAccountHistory = (productId) => {
    let postData = {
        productId: productId
    }
    commonService.genericRequest('get-inventory-product-report', 'post', true, postData).then((response) => {
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
    getGlAccountHistory(route.params.productId)
});

</script>

<template>
    <div className="card">
        <h5>Product Inventory Report</h5>
        <DataTable :value="glAccountHistory" :paginator="true" class="p-datatable-gridlines" :rows="20" dataKey="id"
            :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
            <ColumnGroup type="header">
                <Row>
                    <Column header="Product Info" :rowspan="3" />
                    <Column :header="'Account Name: '" :colspan="6" />
                </Row>
                <Row>
                    <Column :header="'Account Number : '" :colspan="6" />
                </Row>
                <Row>
                    <Column :header="'Opening Balance: '+0" :colspan="6" />
                </Row>
                <Row>
                    <Column header="Transaction Date" sortable field="transaction_date" />
                    <Column header="Name" sortable field="product_name" />
                    <!-- <Column header="Debit" sortable field="dr" /> -->
                    <Column header="Purchase Price" sortable field="purchase_price" />
                    <Column header="Quantity" sortable field="quantity" />
                    <!-- <Column header="Reversed" sortable field="reversal_flag" /> -->
                    <!-- <Column header="Posted By" sortable field="user_name" /> -->
                </Row>

            </ColumnGroup>
            <Column field="transaction_date" header="Transaction Date" style="min-width: 12rem">
                <template #body="{ data }">
                    {{ data.stock_date }}
                </template>
            </Column>
            <Column field="description" header="Name" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.product_name }}
                </template>
            </Column>
            <!-- <Column field="dr" header="Debit" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.dr_cr_ind == 'Dr' ? commonService.commaSeparator(data.tran_amount) : '' }}
                </template>
            </Column> -->
            <!-- <Column field="cr" header="Credit" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.dr_cr_ind == 'Cr' ? commonService.commaSeparator(data.tran_amount) : '' }}
                </template>
            </Column> -->
            <Column field="balance" header="Purchase Price" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.purchase_price) }}
                </template>
            </Column>
            <Column field="balance" header="Quantity" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.quantity) }}
                </template>
            </Column>
            <!-- <Column field="reversal_flag" header="Reversed" style="max-width: 10rem">
                <template #body="{ data }">
                    {{ data.reversal_flag == 'N' ? 'No' : 'Yes' }}
                </template>
            </Column>
            <Column field="name" header="Posted By" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.user_name }}
                </template>
            </Column> -->
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
