<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const router = useRouter();
const route = useRoute()
const receivable = ref(null);
const commonService = new CommonService();

const formData = ref({
    amountPaid: null,
    paymentDate: null,
    paymentDesc: null
});
const showPaymentModal = ref(false);
const selectedReceivable = ref(null);

const togglePaymentModal = (action) => {
    showPaymentModal.value = action;
}

const makePayment = (data)=>{
    selectedReceivable.value = data;
    togglePaymentModal(true);
}

const getReceivables = () => {
    commonService.genericRequest('get-receivables', 'post', true, {}).then((response) => {
        if (response.status) {
            receivable.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const onSubmit = ()=>{
    let postData = {
        amount_paid: formData.value?.amountPaid,
        payment_date: formData.value?.paymentDate,
        payment_desc: formData.value?.paymentDesc,
        receivable_id: selectedReceivable.value?.id
    }

    alert(JSON.stringify(postData));
}

onMounted(() => {
    getReceivables();
});

</script>
<template>
    <div className="card">
        <h5>View Receivables</h5>

        <!--        <p>Use this page to start from scratch and place your custom content.</p>-->
        <DataTable :value="receivable" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
            :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
            <Column field="Date" header="Date" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.created_at }}
                </template>
            </Column>
            <Column field="name" header="Customer" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.customer_name }}
                </template>
            </Column>
            <Column field="name" header="Amount" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.amount }}
                </template>
            </Column>
            <Column field="name" header="Amount Paid" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.amount_paid }}
                </template>
            </Column>
            <Column field="Receipt No." header="Receipt No." style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.receipt_no }}
                </template>
            </Column>
            <Column header="Status" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.status }}
                </template>
            </Column>
            <Column header="Institution" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.institution_name }}
                </template>
            </Column>
            <Column header="Branch" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.branch_name }}
                </template>
            </Column>

            <Column headerStyle="min-width:10rem;">
                <template #body="{ data }">
                    <Button icon="pi pi-eye" class="p-button-primary mr-2" @click="viewDetails(data)"
                        v-tooltip="'View sell details'" />
                     <Button icon="pi pi-dollar" class="p-button-info mr-2" @click="makePayment(data)"
                        v-tooltip="'Make payment'" />
                    <!--                    <Button icon="pi pi-check" class="p-button-rounded p-button-warning mt-2"-->
                    <!--                            @click="confirmDeleteProduct(data)" />-->
                </template>
            </Column>
        </DataTable>



        <!-- Start of Modals-->
        <Dialog header="Create new customer" v-model:visible="showPaymentModal" :style="{ width: '75vw' }"
            :modal="true" class="p-fluid">

            <div class="grid p-fluid">
                <div class="field col-12 md:col-6">
                    <span class="p-float-label">
                        <InputNumber type="text" id="amountPaid" v-model="formData.amountPaid" /> <!-- class="p-invalid"-->
                        <label for="amountPaid">Amount Paid</label>
                    </span>
                </div>
                <div class="field col-12 md:col-6">
                    <span class="p-float-label">
                        <Calendar id="date" v-model="formData.paymentDate"></Calendar>
                        <label for="date">Date Paid</label>
                    </span>
                </div>

                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="Desc" rows="7" v-model="formData.paymentDesc"></Textarea>
                        <label for="Desc">Payment Description</label>
                    </span>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" @click="toggleCustomerModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>

    </div>
</template>
