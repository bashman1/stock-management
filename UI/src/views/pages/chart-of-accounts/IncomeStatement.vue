<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

 const router = useRouter();
const cntrlAccts = ref(null);

const getCntrlLedger=()=>{
    commonService.genericRequest('get-control-accts', 'get', true, {}).then((response)=>{
        if(response.status){
            cntrlAccts.value = response.data
        }else{

        }
    })
}

 onMounted(() => {
    getCntrlLedger();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>Income Statement</h5>
            <!-- <div class="grid p-fluid mt-3"> -->
                <Toast />

                <DataTable :value="cntrlAccts" :paginator="true" class="p-datatable-gridlines" :rows="20" dataKey="id"
                :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
                <Column field="name" header="Name" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.param_name }}
                    </template>
                </Column>
                <Column field="name" header="Code" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.param_cd) }}
                    </template>
                </Column>
                <Column field="name" header="Acct No." style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.param_value }}
                    </template>
                </Column>
                <!-- <Column headerStyle="max-width:10rem;">
                    <template #body="{ data }">
                            <Button icon="pi pi-eye" @click="goToDetails(data)" class="p-button-primary mr-2" />
                            <Button icon="pi pi-pencil" @click="updateGl(data)" class="p-button-success mr-2" />
                    </template>
                </Column> -->
            </DataTable>
            <!-- </div> -->
        </div>
    </div>
</template>
