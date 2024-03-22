<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const glAccounts = ref(null);
const commonService = new CommonService();
const router = useRouter();
const route = useRoute();

const glOverView = ref([]);

const getGlAccountsOverView = () => {
    commonService.genericRequest('get-gl-overview', 'post', true, {}).then((response) => {
        if (response.status) {
            glOverView.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getAccountTotal=(acctArray)=>{
    let total = 0;
    acctArray.forEach(element => {
        total=Number(total)+Number(element.balance);
    });
    return total;
}


onMounted(() => {
    getGlAccountsOverView();
});

</script>

<template>
    <div className="card" v-for="(gl, index) in glOverView ">
        <h5>{{gl[0]?.acct_type+',    Total '+commonService.commaSeparator(getAccountTotal(gl))}}</h5>
        <DataTable :value="gl"  class="p-datatable-gridlines"  dataKey="id"
            :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
            <Column field="name" header="Acct No." style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.acct_no }}
                </template>
            </Column>
            <Column field="name" header="Ledger No." style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.gl_no }}
                </template>
            </Column>
            <Column field="name" header="Name" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.description }}
                </template>
            </Column>
            <Column field="name" header="Acct Balance" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.balance) }}
                </template>
            </Column>

  
        </DataTable>
    </div>
</template>
