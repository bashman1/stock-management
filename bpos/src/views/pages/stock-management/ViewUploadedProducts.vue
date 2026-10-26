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
    commonService.genericRequest('get-product-upload-batch', 'post', true, {}).then((response) => {
        if (response.status) {
            memberBatch.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const goToApprove=(event)=>{
    router.push("/approve-uploaded-products/"+event?.id);
}

onMounted(() => {
    getMemberBatch();
});

</script>
<template>
    <div className="card">
        <h5>View Bulk Uploaded Products</h5>
        <!--        <p>Use this page to start from scratch and place your custom content.</p>-->
        <DataTable :value="memberBatch" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                   :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
            <Column field="name" header="Name" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.name }}
                </template>
            </Column>
            <!--            <Column header="Status" style="min-width: 10rem">-->
            <!--                <template #body="{ data }">-->
            <!--                    {{ data.status }}-->
            <!--                </template>-->
            <!--            </Column>-->
            <Column header="Institution" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.institution_name }}
                </template>
            </Column>
            <Column header="Date" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.created_at }}
                </template>
            </Column>
            <Column headerStyle="min-width:10rem;">
                <template #body="{ data }">
                    <Button icon="pi pi-info-circle" class="p-button-rounded p-button-success mr-2"
                            @click="goToApprove(data)" />
                    <!--                    <Button icon="pi pi-check" class="p-button-rounded p-button-warning mt-2"-->
                    <!--                            @click="confirmDeleteProduct(data)" />-->
                </template>
            </Column>

        </DataTable>
    </div>
</template>
