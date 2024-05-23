<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

 const router = useRouter();
const productsReport = ref(null);

const getProductsReport=()=>{
    let postData ={
        status: 'Active'
    }
    commonService.genericRequest('get-sales-report', 'post', true, postData).then((response)=>{
        if(response.status){
            productsReport.value = response.data
        }else{

        }
    })
}



 onMounted(() => {
    getProductsReport();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>Sales report.</h5>
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
                        {{ data.tran_date }}
                    </template>
                </Column>
                <Column field="name" header="Ref no." style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.ref_no }}
                    </template>
                </Column>
                <Column field="name" header="Receipt no." style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.receipt_no }}
                    </template>
                </Column>
                <Column field="name" header="Amount" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.total) }}
                    </template>
                </Column>
                <Column field="name" header="Amount Paid" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.amount_paid) }}
                    </template>
                </Column>
                <Column field="name" header="Change" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.amount_paid - data.total==0?0:commonService.commaSeparator(data.amount_paid - data.total) }}
                    </template>
                </Column>
                <Column field="name" header="Institution" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.institution_name }}
                    </template>
                </Column>
                <Column field="name" header="Branch" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.branch_name }}
                    </template>
                </Column>
                <Column field="name" header="Item Count" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.item_count }}
                    </template>
                </Column>
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
