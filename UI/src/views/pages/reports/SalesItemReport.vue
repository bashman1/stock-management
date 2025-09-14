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
const startDate = ref(null);
const endDate = ref(null);
const searchKeyWord = ref(null);

const getProductsReport = () => {
    let postData = {
        status: 'Active',
        from_date: startDate.value,
        to_date: endDate.value,
        searchKey: searchKeyWord.value
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
        let postData = {
            status: 'Active',
            from_date: startDate.value,
            to_date: endDate.value,
            searchKey: searchKeyWord.value
        };
        isDownloading.value = true;
        commonService
            .genericRequest('download-sales-item-report-pdf', 'post', true, postData, true)

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


const totalQuantity = computed(() =>
  productsReport?.value?.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0)
);

const totalAmount = computed(() =>
  productsReport?.value?.reduce((sum, item) => sum + ((Number(item.quantity) || 0) * (Number(item.selling_price) || 0)), 0)
);

const totalSellingPrice = computed(() =>
  productsReport?.value?.reduce((sum, item) => sum + (Number(item.selling_price) || 0), 0)
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
            <h5>Sales report.</h5>
            <!-- <div class="grid p-fluid mt-3"> -->
            <Toast />
            <Toolbar class="mb-4">
                 <template #start>
                    <span class="p-float-label mr-2">
                        <Calendar id="date" showIcon v-model="startDate"></Calendar>
                        <label for="date">Start Date</label>
                    </span>
                    <span class="p-float-label mr-2">
                        <Calendar id="date" showIcon fluid iconDisplay="input" v-model="endDate"></Calendar>
                        <label for="date">End Date</label>
                    </span>
                    <span class="p-float-label mr-2">
                        <InputText type="text" id="purchasePrice" v-model="searchKeyWord" />
                        <label for="purchasePrice">Search </label>
                    </span>
                    <Button @click="getProductsReport" label="SEARCH" icon="pi pi-search" class="mr-2" />
                </template>
                <template v-slot:end>
                    <div class="my-2">
                        <Button label="CSV" icon="pi pi-file-excel" class="p-button-success mr-2" @click="() => getProductReportCsvFile()" />
                        <Button label="PDF" icon="pi pi-file-pdf" class="p-button-danger" @click="() => getProductReportPdfFile()" />
                    </div>
                </template>

            </Toolbar>
            <DataTable :value="productsReport" :paginator="true" stripedRows size="small" :rows="20" dataKey="id" :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">

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
                    <template #footer>
                        <b>{{ commonService.commaSeparator(totalQuantity) }}</b>
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
                    <template #footer>
                        <b>{{ commonService.commaSeparator(totalAmount) }}</b>
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
                        {{ commonService.commaSeparator((data.selling_price - data.purchase_price)*data.quantity) }}
                    </template>
                    <template #footer>
                        <b>{{ commonService.commaSeparator(totalProfitMargin) }}</b>
                    </template>
                </Column>
            </DataTable>

        </div>
    </div>
</template>
