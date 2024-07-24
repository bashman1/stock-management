<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';
const router = useRouter();
const route = useRoute()
const toast = useToast();
const commonService = new CommonService();

const members = ref(null);
const selectedMember = ref(null);



const getBatchMembers=(batchId)=>{
    let postData={
        status:"Pending",
        batchId:batchId
    }

    commonService.genericRequest('get-batch-products', 'post', true, postData).then((response) => {
        if (response.status) {
            members.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const approveMembers=()=>{
    let postData={
        products:selectedMember.value,
        status:"Active"
    }
    commonService.genericRequest('approve-batch-product', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getBatchMembers(route.params.id)
        } else {
            commonService.showError(toast, response.message);
        }
    })

}

onMounted(() => {
    getBatchMembers(route.params.id);
});

</script>

<template>
    <div class="grid">
        <div class="col-12">
            <div className="card">
                <h5>Approve Batch</h5>
                <Toast />
                <!--                <p>Use this page to start from scratch and place your custom content.</p>-->
                <Toolbar class="mb-4">
                    <template v-slot:start>
                        <div class="my-2">
                            <!--                            <Button label="New" icon="pi pi-plus" class="p-button-success mr-2" @click="openNew" />-->
                            <Button label="Approve" icon="pi pi-check" class="p-button-success" @click="approveMembers" :disabled="!selectedMember || !selectedMember.length" />
                        </div>
                    </template>

                    <template v-slot:end>
                        <!--                        <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" chooseLabel="Import" class="mr-2 inline-block" />-->
                        <!--                        <Button label="Export" icon="pi pi-upload" class="p-button-help" @click="exportCSV($event)" />-->
                    </template>
                </Toolbar>
                <DataTable :value="members" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                           :rowHover="true" filterDisplay="menu" responsiveLayout="scroll"
                           v-model:selection="selectedMember" :rowsPerPageOptions="[5, 10, 25]">
                    <Column field="name" header="Name" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                    </Column>
                    <Column header="Purchase Price" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.purchase_price }}
                        </template>
                    </Column>
                    <Column header="Selling Price" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.selling_price }}
                        </template>
                    </Column>
                    <Column header="Quantity" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.quantity }}
                        </template>
                    </Column>
                    <Column header="Description" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.description }}
                        </template>
                    </Column>
                    <Column header="Stock Date" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.stock_date }}
                        </template>
                    </Column>
                    <Column header="Status" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.status }}
                        </template>
                    </Column>
                    <Column header="Date" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.created_at }}
                        </template>
                    </Column>
                    <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                </DataTable>
            </div>
        </div>
    </div>

</template>
