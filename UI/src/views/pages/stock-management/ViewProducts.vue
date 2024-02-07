<script setup>
import { ref, onMounted } from 'vue';
import {useRoute, useRouter} from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const memberBatch = ref(null);
const commonService = new CommonService();
const router = useRouter();
const route = useRoute()


const getMemberBatch=()=>{
    commonService.genericRequest('get-products', 'get', true, {}).then((response) => {
        if (response.status) {
            memberBatch.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const goToApprove=(event)=>{
    router.push("/get-products"+event?.id);
}

onMounted(() => {
    getMemberBatch();
});

</script>
<template>
    <div className="card">
        <h5>View Products</h5>
<!--        <p>Use this page to start from scratch and place your custom content.</p>-->
        <DataTable :value="memberBatch" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                   :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
            <Column field="name" header="Product" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.name }}
                </template>
            </Column>
            <Column field="name" header="Product ID" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.product_no }}
                </template>
            </Column>
            <Column field="name" header="Category" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.category_name }}
                </template>
            </Column>
            <Column field="name" header="Sub Category" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.sub_category_name }}
                </template>
            </Column>
            <Column field="name" header="Selling Price" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.selling_price }}
                </template>
            </Column>
            <Column field="name" header="Quantity" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.quantity+' '+data.unit }}
                </template>
            </Column>
           <Column header="Manufacturer" style="min-width: 10rem">
               <template #body="{ data }">
                   {{ data.manufacturer }}
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
            <Column header="Date" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.created_at }}
                </template>
            </Column>
            <Column headerStyle="min-width:10rem;">
                <template #body="{ data }">
                    <Button icon="pi pi-file-edit" class="p-button-rounded p-button-success mr-2"
                            @click="goToApprove(data)" />
<!--                    <Button icon="pi pi-check" class="p-button-rounded p-button-warning mt-2"-->
<!--                            @click="confirmDeleteProduct(data)" />-->
                </template>
            </Column>

        </DataTable>
    </div>
</template>
