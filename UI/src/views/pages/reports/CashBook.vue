<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

const router = useRouter();
const productsReport = ref(null);
const isDownloading = ref(false);

const getProductsReport = () => {
    let postData = {
        status: 'Active'
    };
    commonService.genericRequest('get-cash-book', 'post', true, postData).then((response) => {
        if (response.status) {
            productsReport.value = response.data;
        } else {
        }
    });
};

const goToInventory = (data) => {
    router.push('/product-inventory-report/' + data.id);
};

const goToSales = (data) => {
    router.push('/products-sales-report/' + data.id);
};

const getProductReportPdfFile = () => {
    try {
        isDownloading.value = true;
        commonService
            .genericRequest('download-product-report-pdf', 'post', true, {}, true)

            .then((blob) => {
                const fileURL = window.URL.createObjectURL(new Blob([blob]));
                const fileLink = document.createElement('a');
                fileLink.href = fileURL;
                fileLink.setAttribute('download', 'product_reports.pdf');
                document.body.appendChild(fileLink);
                fileLink.click();
            });
    } catch (error) {
        console.log(error);
    } finally {
        isDownloading.value = false;
    }
};


const getProductReportCsvFile = () => {
    try {
        isDownloading.value = true;
        commonService
            .genericRequest('download-product-report-csv', 'post', true, {}, true)

            .then((blob) => {
                //console.log(blob, 'data.......')
                const fileURL = window.URL.createObjectURL(new Blob([blob]));
                const fileLink = document.createElement('a');
                fileLink.href = fileURL;
                fileLink.setAttribute('download', 'product_reports.csv');
                document.body.appendChild(fileLink);
                fileLink.click();
            });
    } catch (error) {
        console.log(error);
    } finally {
        isDownloading.value = false;
    }
};

onMounted(() => {
    getProductsReport();
});
</script>
<template>
    <div>
        <div class="card">
            <h5>Cash Book</h5>
            <!-- <div class="grid p-fluid mt-3"> -->
            <Toast />
            <Toolbar class="mb-4">
                <template v-slot:end>
                    <div class="my-2">
                        <Button label="CSV" icon="pi pi-file-excel" class="p-button-success mr-2" @click="() => getProductReportCsvFile()" />
                        <Button label="PDF" icon="pi pi-file-pdf" class="p-button-danger" @click="() => getProductReportPdfFile()" />
                    </div>
                </template>

                <!-- <template v-slot:end>
                        <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" chooseLabel="Import" class="mr-2 inline-block" />
                        <Button label="Export" icon="pi pi-upload" class="p-button-help" @click="exportCSV($event)" />
                    </template> -->
            </Toolbar>
            <DataTable :value="productsReport" :paginator="true" class="p-datatable-gridlines" :rows="20" dataKey="id" :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
                <Column field="name" header="Date" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.dr_cr_ind=='Dr'? data.transaction_date:'' }}
                    </template>
                </Column>
                <Column field="name" header="Details" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{  data.dr_cr_ind=='Dr'?data.description:'' }}
                    </template>
                </Column>
                <Column field="name" header="V. No." style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.dr_cr_ind=='Dr'?data.tran_id :''}}
                    </template>
                </Column>
                <Column field="name" header="PR" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ }}
                    </template>
                </Column>
                <Column field="name" header="Discount" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.ind=='PD' && data.dr_cr_ind=='Dr' ? commonService.commaSeparator(data.tran_amount) : ''}}
                    </template>
                </Column>
                <Column field="name" header="Cash" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.ind=='CL' && data.dr_cr_ind=='Dr'? commonService.commaSeparator(data.tran_amount) : ''}}
                    </template>
                </Column>
                <Column field="name" header="Bank" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.ind=='CGL' && data.dr_cr_ind=='Dr'? commonService.commaSeparator(data.tran_amount) : ''}}
                    </template>
                </Column>
                <Column field="name" header="Date" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.dr_cr_ind=='Cr'?data.transaction_date:'' }}
                    </template>
                </Column>
                <Column field="name" header="Details" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.dr_cr_ind=='Cr'?data.description:'' }}
                    </template>
                </Column>
                <Column field="name" header="V. No." style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.dr_cr_ind=='Cr'?data.tran_id:'' }}
                    </template>
                </Column>
                <Column field="name" header="PR" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ }}
                    </template>
                </Column>
                <Column field="name" header="Discount" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.ind=='PD' && data.dr_cr_ind=='Cr' ? commonService.commaSeparator(data.tran_amount) : ''}}
                    </template>
                </Column>
                <Column field="name" header="Cash" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.ind=='CL' && data.dr_cr_ind=='Cr'? commonService.commaSeparator(data.tran_amount) : ''}}
                    </template>
                </Column>
                <Column field="name" header="Bank" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.ind=='CGL' && data.dr_cr_ind=='Cr'? commonService.commaSeparator(data.tran_amount) : ''}}
                    </template>
                </Column>


<!--                <Column headerStyle="min-width:10rem;">-->
<!--                    <template #body="{ data }">-->
<!--                        <Button icon="pi pi-shopping-bag" @click="goToInventory(data)" class="p-button-primary mr-2" v-tooltip="'Inventory report'" />-->
<!--                        <Button icon="pi pi-ticket" @click="goToSales(data)" class="p-button-success mr-2" v-tooltip="'Sales report'" />-->
<!--                    </template>-->
<!--                </Column>-->
            </DataTable>
            <!-- </div> -->
        </div>
    </div>
</template>
