<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const memberBatch = ref(null);
const showProductDetailsModal = ref(false);
const orderItems = ref(null);
const commonService = new CommonService();
const orderDetails = ref({});
const router = useRouter();
const route = useRoute()


const getSales = () => {
    commonService.genericRequest('get-orders', 'get', true, {}).then((response) => {
        if (response.status) {
            memberBatch.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}
const viewDetails = (data) => {
    toggleModal(true);
    orderDetails.value = data;
    getOrderDetails(data?.id);

}

const getOrderDetails = (orderId) => {
    commonService.genericRequest('get-orders-details/' + orderId, 'get', true, {}).then((response) => {
        if (response.status) {
            orderItems.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const toggleModal = (action) => {
    showProductDetailsModal.value = action;
}

onMounted(() => {
    getSales();
});

</script>
<template>
    <div className="card">
        <h5>View Sales</h5>
        <!--        <p>Use this page to start from scratch and place your custom content.</p>-->
        <DataTable :value="memberBatch" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
            :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
            <Column field="Ref No." header="Product" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.ref_no }}
                </template>
            </Column>
            <Column field="Receipt No." header="Receipt No." style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.receipt_no }}
                </template>
            </Column>
            <Column field="name" header="Item Count" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.item_count }}
                </template>
            </Column>
            <!-- <Column field="name" header="Cost" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.total) }}
                </template>
            </Column> -->
            <Column field="name" header="Amount Paid" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.amount_paid) }}
                </template>
            </Column>
            <Column field="name" header="Cost" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.total) }}
                </template>
            </Column>
            <Column field="name" header="Change" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.amount_paid-data.total) }}
                </template>
            </Column>
            <Column header="Discount" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.discount) }}
                </template>
            </Column>
            <Column header="Status" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.payment_status }}
                </template>
            </Column>
            <Column header="Date" style="max-width: 10rem">
                <template #body="{ data }">
                    {{ data.created_at }}
                </template>
            </Column>
            <Column headerStyle="min-width:10rem;">
                <template #body="{ data }">
                    <Button icon="pi pi-info" class="p-button-rounded p-button-success mr-2" @click="viewDetails(data)" />
                    <!--                    <Button icon="pi pi-check" class="p-button-rounded p-button-warning mt-2"-->
                    <!--                            @click="confirmDeleteProduct(data)" />-->
                </template>
            </Column>
        </DataTable>



        <Dialog header="Sales Details" v-model:visible="showProductDetailsModal" :style="{ width: '33vw' }" :modal="true"
            class="p-fluid">
            <p style="padding: 20px;">
            <div class="grid p-fluid">
                <div class="field col-6 md:col-6">
                    <span class="p-float-label">
                        <!-- <InputText type="text" id="unitName" @blur="onTypeInputBlur(typeName, 'typeName')" v-model="typeName" :class="{ 'p-invalid': typeFormError?.typeName }" /> class="p-invalid" -->
                        <label for="unitName">Ref No.: {{ orderDetails.ref_no }} </label>
                    </span>
                </div>
                <div class="field col-6 md:col-6">
                    <span class="p-float-label">
                        <!-- <InputText type="text" id="unitName" @blur="onTypeInputBlur(typeName, 'typeName')" v-model="typeName" :class="{ 'p-invalid': typeFormError?.typeName }" /> class="p-invalid" -->
                        <label for="unitName">Receipt No.: {{ orderDetails.receipt_no }}</label>
                    </span>
                </div>
                <div class="field col-6 md:col-6">
                    <span class="p-float-label">
                        <!-- <InputText type="text" id="unitName" @blur="onTypeInputBlur(typeName, 'typeName')" v-model="typeName" :class="{ 'p-invalid': typeFormError?.typeName }" /> class="p-invalid" -->
                        <label for="unitName">Item Count: {{ orderDetails.item_count }}</label>
                    </span>
                </div>
                <div class="field col-6 md:col-6">
                    <span class="p-float-label">
                        <!-- <InputText type="text" id="unitName" @blur="onTypeInputBlur(typeName, 'typeName')" v-model="typeName" :class="{ 'p-invalid': typeFormError?.typeName }" /> class="p-invalid" -->
                        <label for="unitName">Total: {{ commonService.commaSeparator(orderDetails.total) }}</label>
                    </span>
                </div>
                <hr>
            </div>
            <div class="grid p-fluid">
                <DataTable :value="orderItems" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
                <Column field="Ref No." header="Item" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.name }}
                    </template>
                </Column>
                <Column field="Receipt No." header="Price" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.price) }}
                    </template>
                </Column>
                <Column field="name" header="Qty" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.qty }}
                    </template>
                </Column>
                <Column field="name" header="Sub Total" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.price * data.qty) }}
                    </template>
                </Column>
            </DataTable>
            </div>
            </p>
            <template #footer>
                <Button label="Cancel" @click="toggleTypeModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <!-- <Button @click="onSubmitType" label="SUBMIT" class="p-button-outlined mr-2 mb-2" /> -->
            </template>
        </Dialog>

    </div>
</template>
