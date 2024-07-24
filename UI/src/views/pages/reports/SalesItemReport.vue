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
    commonService.genericRequest('get-item-sales-report', 'post', true, postData).then((response) => {
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
            .genericRequest('download-sales-item-report-pdf', 'post', true, {status: 'Active'}, true)

            .then((blob) => {
                const fileURL = window.URL.createObjectURL(new Blob([blob]));
                const fileLink = document.createElement('a');
                fileLink.href = fileURL;
                fileLink.setAttribute('download', 'sales_reports.pdf');
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
            .genericRequest('download-sales-item-report-csv', 'post', true, {status: 'Active'}, true)
            .then((blob) => {
                const fileURL = window.URL.createObjectURL(new Blob([blob]));
                const fileLink = document.createElement('a');
                fileLink.href = fileURL;
                fileLink.setAttribute('download', 'sales_reports.csv');
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
            <h5>Sales report.</h5>
            <!-- <div class="grid p-fluid mt-3"> -->
            <Toast />
            <Toolbar class="mb-4">
                <template v-slot:end>
                    <div class="my-2">
                        <Button label="CSV" icon="pi pi-file-excel" class="p-button-success mr-2" @click="() => getProductReportCsvFile()" />
                        <Button label="PDF" icon="pi pi-file-pdf" class="p-button-danger" @click="() => getProductReportPdfFile()" />
                    </div>
                </template>

            </Toolbar>
            <DataTable :value="productsReport" :paginator="true" class="p-datatable-gridlines" :rows="20" dataKey="id" :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">

                <Column field="name" header="Date" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.created_at }}
                    </template>
                </Column>
                <Column field="name" header="Name" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.product_name }}
                    </template>
                </Column>
                <Column field="name" header="Quantity Sold" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.quantity) }}
                    </template>
                </Column>
                <Column field="name" header="Measurement Unit" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.measurement_unit }}
                    </template>
                </Column>
                <Column field="name" header="Amount" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.quantity * data.selling_price) }}
                    </template>
                </Column>

                <Column field="name" header="Selling Price" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.selling_price) }}
                    </template>
                </Column>
                <Column field="name" header="Profit Margin" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.selling_price - data.purchase_price) }}
                    </template>
                </Column>
            </DataTable>
            <!-- </div> -->
        </div>
    </div>
</template>
