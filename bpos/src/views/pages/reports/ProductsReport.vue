<script setup>
import { ref, onMounted, computed } from 'vue';
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
    commonService.genericRequest('get-products-report', 'post', true, postData).then((response) => {
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


const totalQuantity = computed(()=>
    productsReport?.value?.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0)
);


const totalSellingPrice = computed(() =>
  productsReport?.value?.reduce((sum, item) => sum + (Number(item.selling_price) || 0) * (Number(item.quantity) || 0), 0)
);

const totalPurchasePrice = computed(() =>
  productsReport?.value?.reduce((sum, item) => sum + (Number(item.purchase_price) || 0) * (Number(item.quantity) || 0), 0)
);

const totalProfitMargin = computed(() =>
  productsReport?.value?.reduce((sum, item) => sum + (((Number(item.selling_price) || 0) - (Number(item.purchase_price) || 0)) * (Number(item.quantity) || 0)), 0)
);

onMounted(() => {
    getProductsReport();
});
</script>
<template>
    <div>
        <div class="card">
            <h5>Products report.</h5>
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
                <Column header="#" style="width: 3rem; text-align: center;">
                    <template #body="{ index }">
                        {{  index + 1 }}
                    </template>
                </Column>
                <Column field="name" header="Name" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.name }}
                    </template>
                </Column>
                <Column field="name" header="Type" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.type_name }}
                    </template>
                </Column>
                <Column field="name" header="Category" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.category_name }}
                    </template>
                </Column>
                <Column field="name" header="Stock Available" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.quantity) }}
                    </template>
                    <template #footer>
                        <b>{{ commonService.commaSeparator(totalQuantity) }}</b>
                    </template>
                </Column>
                <Column field="name" header="Measurement" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.measurement_unit_name }}
                    </template>
                </Column>
                <Column field="name" header="Purchase Price" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.purchase_price) }}
                    </template>
                    <template #footer>
                        <b>{{ commonService.commaSeparator(totalPurchasePrice) }}</b>
                    </template>
                </Column>
                <Column field="name" header="Selling Price" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.selling_price) }}
                    </template>
                    <template #footer>
                        <b>{{ commonService.commaSeparator(totalSellingPrice) }}</b>
                    </template>
                </Column>
                <Column field="name" header="Profit Margin" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.selling_price - data.purchase_price) }}
                    </template>
                    <template #footer>
                        <b>{{ commonService.commaSeparator(totalProfitMargin) }}</b>
                    </template>
                </Column>

                <Column field="name" header="Product No." style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.product_no }}
                    </template>
                </Column>
                <Column field="name" header="VAT Tax" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.tax_config }}
                    </template>
                </Column>
                <Column headerStyle="min-width:10rem;">
                    <template #body="{ data }">
                        <Button icon="pi pi-shopping-bag" @click="goToInventory(data)" class="p-button-primary mr-2" v-tooltip="'Inventory report'" />
                        <Button icon="pi pi-ticket" @click="goToSales(data)" class="p-button-success mr-2" v-tooltip="'Sales report'" />
                    </template>
                </Column>
            </DataTable>
            <!-- </div> -->
        </div>
    </div>
</template>
