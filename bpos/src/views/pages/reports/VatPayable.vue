<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

 const router = useRouter();
const productsReport = ref(null);

const getVatPayableReport=()=>{
    let postData ={
        status: 'Active'
    }
    commonService.genericRequest('get-vat-payable', 'post', true, postData).then((response)=>{
        if(response.status){
            productsReport.value = response.data
        }else{

        }
    })
}



 onMounted(() => {
    getVatPayableReport();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>V.A.T Payable report.</h5>
            <!-- <div class="grid p-fluid mt-3"> -->
                <Toast />
                <Toolbar class="mb-4">
                    <template v-slot:end>
                        <div class="my-2">
                            <Button label="CSV" icon="pi pi-file-excel" class="p-button-success mr-2" @click="openNew" />
                            <Button label="PDF" icon="pi pi-file-pdf" class="p-button-danger" @click="confirmDeleteSelected" />
                        </div>
                    </template>

                    <!-- <template v-slot:end>
                        <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" chooseLabel="Import" class="mr-2 inline-block" />
                        <Button label="Export" icon="pi pi-upload" class="p-button-help" @click="exportCSV($event)" />
                    </template> -->
                </Toolbar>
                <DataTable :value="productsReport" :paginator="true" class="p-datatable-gridlines" :rows="20" dataKey="id"
                :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
                <Column field="name" header="Date" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.created_at }}
                    </template>
                </Column>
                <Column field="name" header="Customer Name" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.customer_name }}
                    </template>
                </Column>
                <Column field="name" header="Item" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.name }}
                    </template>
                </Column>
                <Column field="name" header="Description" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.tax_config }}
                    </template>
                </Column>
                <Column field="name" header="Total Sales" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.amount_after_vat) }}
                    </template>
                </Column>
                <Column field="name" header="Total Purchases" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.total_purchases) }}
                    </template>
                </Column>
                <Column field="name" header="Credit Balance" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.vat) }}
                    </template>
                </Column>
                <!-- <Column field="name" header="Branch" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.branch_name }}
                    </template>
                </Column> -->
                <!-- <Column field="name" header="Item Count" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.item_count }}
                    </template>
                </Column> -->
                <!-- <Column field="name" header="Selling Price" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.selling_price) }}
                    </template>
                </Column> -->
                <!-- <Column field="name" header="Product No." style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.product_no }}
                    </template>
                </Column> -->
                <!-- <Column headerStyle="max-width:10rem;">
                    <template #body="{ data }">
                            <Button icon="pi pi-eye" @click="goToDetails(data)" class="p-button-primary mr-2" />
                            <Button icon="pi pi-pencil" @click="updateGl(data)" class="p-button-success mr-2" />
                    </template>
                </Column> -->
            </DataTable>
            <!-- </div> -->
        </div>
    </div>
</template>
