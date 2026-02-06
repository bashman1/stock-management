<script setup>

import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();

const transactions = ref(null);

const getApprovedTransactions = () => {
    commonService.genericRequest('get-collected-transactions', 'get', true, {}).then((response) => {
        if (response.status) {
            transactions.value = response.data
        } else {

        }
    })
}

onMounted(() => {
    getApprovedTransactions();
});

</script>
<template>
    <div className="card">
        <h5>Approved Collections</h5>
        <div class="grid p-fluid mt-3">
            <div class="field col-12 md:col-12">
                <DataTable :value="transactions" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
                <Column field="name" header="Name" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.member_name }}
                    </template>
                </Column>
                <Column header="Member No." style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.member_number }}
                    </template>
                </Column>
                <Column header="Amount" style="min-width: 12rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.amount) }}
                    </template>
                </Column>
                <Column header="Status" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.status }}
                    </template>
                </Column>
                <Column header="Date" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.tran_date }}
                    </template>
                </Column>
                <Column header="Officer" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.officer_name }}
                    </template>
                </Column>
                <Column header="Transaction ID" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.tran_id }}
                    </template>
                </Column>
                <!-- <Column header="Receipt" headerStyle="min-width:5rem;">
                    <template #body="{ data }">
                        <Button icon="pi pi-print" class="p-button-rounded p-button-success mr-2"
                            @click="showModal(data)" />
                    </template>
                </Column> -->
            </DataTable>
            </div>
        </div>
    </div>
</template>
