<script setup>
import {ref, onMounted} from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

const router = useRouter();

const transactions = ref(null);

const getOfficerTransactions=()=>{
    commonService.genericRequest('get-officer-collection', 'get', true, {}).then((response)=>{
        if(response.status){
            transactions.value = response.data
        }else{

        }
    })
}

onMounted(() => {
    getOfficerTransactions();
});

</script>
<template>
    <div className="card">
        <h5>View Officers Collections</h5>
<!--        <p>Use this page to start from scratch and place your custom content.</p>-->


<!--        {"status":true,"code":201,"message":"Collected successfully","data":[{"id":2,"amount":"10000","description":"Depositing for the first time","tran_date":"2023-10-23 21:00:00","member_number":"b10011034","member_id":39,"institution_id":1,"branch_id":1,"user_id":1,"tran_id":"TRN1001","status":"Pending","created_by":1,"updated_by":null,"created_on":"2023-10-24 06:46:24","updated_on":null,"created_at":"2023-10-24 06:46:24","updated_at":"2023-10-24 06:46:24","member_name":"Bashir Saidi Wamula","institution_name":"bash","branch_name":"Main","officer_name":"bash saidi 12345"}]}-->
        <div class="grid p-fluid mt-3">
            <div class="field col-12 md:col-12" >
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
                            {{ data.amount }}
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
                </DataTable>
            </div>
        </div>
    </div>
</template>
