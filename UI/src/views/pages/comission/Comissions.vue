<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';
const router = useRouter();
const route = useRoute()
const toast = useToast();
const commonService = new CommonService();
const commissions = ref(null);
const selectedTransactions = ref(null);

const getCommissions=()=>{
     commonService.genericRequest('get-commissions', 'get', true, {}).then((response) => {
         if (response.status) {
            commissions.value = response.data;
         } else {
             commonService.showError(toast, response.message);
         }
     })
 }

 const approvePayment=()=>{

 }

onMounted(() => {
    getCommissions()
});


</script>
<template>
    <div class="grid">
        <div class="col-12">
            <div className="card">
                <h5>View Commission</h5>
                <Toast />
                <!-- <p>Use this page to start from scratch and place your custom content.</p> -->
                <Toolbar class="mb-4">
                    <template v-slot:start>
                        <div class="my-2">
<!--                            <Button label="New" icon="pi pi-plus" class="p-button-success mr-2" @click="openNew" />-->
                            <Button label="Approve Payment" icon="pi pi-check" class="p-button-success" @click="approvePayment" :disabled="!selectedTransactions || !selectedTransactions.length" />
                        </div>
                    </template>

                    <template v-slot:end>
<!--                        <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" chooseLabel="Import" class="mr-2 inline-block" />-->
<!--                        <Button label="Export" icon="pi pi-upload" class="p-button-help" @click="exportCSV($event)" />-->
                    </template>
                </Toolbar>

                <DataTable :value="commissions" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll" v-model:selection="selectedTransactions"
                    :rowsPerPageOptions="[5, 10, 25]">
                    <Column field="name" header="Name" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.institution_name }}
                        </template>
                    </Column>
                    <Column header="Rate" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.rate }}
                        </template>
                    </Column>
                    <Column header="Commission" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.commision }}
                        </template>
                    </Column>
                    <Column header="Member" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.member_name }}
                        </template>
                    </Column>
                    <Column header="Officer" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.officer_name }}
                        </template>
                    </Column>
                    <Column header="Tran Id" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.tran_id }}
                        </template>
                    </Column>
                    <Column header="Phone Number" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.phone_number }}
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
