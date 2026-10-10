<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

 const router = useRouter();
const productsReport = ref(null);
const startDate = ref(null);
const endDate = ref(null);
const searchKeyWord = ref(null);

const getProductsReport=()=>{
    let postData ={
        status: 'Active',
        from_date: startDate.value,
        to_date: endDate.value,
        searchKey: searchKeyWord.value
    }
    commonService.genericRequest('get-sales-report', 'post', true, postData).then((response)=>{
        if(response.status){
            productsReport.value = response.data
        }else{

        }
    })
}

const totalAmount = computed(() =>
  (productsReport.value || []).reduce((sum, item) => sum + (Number(item.total) || 0), 0)
);

const totalAmountPaid = computed(() =>
  (productsReport.value || []).reduce((sum, item) => sum + (Number(item.amount_paid) || 0), 0)
);

const totalItemCount = computed(() =>
  (productsReport.value || []).reduce((sum, item) => sum + (Number(item.item_count) || 0), 0)
);



 onMounted(() => {
    getProductsReport();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>Sales report.</h5>
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
                            <Button label="CSV" icon="pi pi-file-excel" class="p-button-success mr-2" @click="openNew" />
                            <Button label="PDF" icon="pi pi-file-pdf" class="p-button-danger" @click="confirmDeleteSelected" />
                        </div>
                    </template>
                </Toolbar>
                <DataTable :value="productsReport" :paginator="true" stripedRows size="small" :rows="20" dataKey="id"
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
                <Column field="name" header="Physical receipt no." style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.physical_receipt_no }}
                    </template>
                </Column>
                <Column field="name" header="Amount" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.total) }}
                    </template>
                     <template #footer>
                        <strong>{{ commonService.commaSeparator(totalAmount) }}</strong>
                    </template>
                </Column>
                <Column field="name" header="Amount Paid" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.amount_paid) }}
                    </template>
                    <template #footer>
                        <strong>{{ commonService.commaSeparator(totalAmountPaid) }}</strong>
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
                     <template #footer>
                        <strong>{{ totalItemCount }}</strong>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>
