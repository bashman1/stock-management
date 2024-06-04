<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const memberBatch = ref(null);
const commonService = new CommonService();
const router = useRouter();
const route = useRoute()


const getMemberBatch = () => {
    commonService.genericRequest('get-chart-accounts', 'post', true, {}).then((response) => {
        if (response.status) {
            memberBatch.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const goToApprove = (event) => {
    router.push("/get-products" + event?.id);
}

const goToDetails = (event) => {
    router.push("/gl-statement/" + event?.acct_no);
}

onMounted(() => {
    getMemberBatch();
});

</script>

<template>
    <div className="card">
        <h5>Chart Of Accounts</h5>

        <TabView>
            <TabPanel v-for="tab in memberBatch" :key="tab.id" :header="tab.description">
                <TabView>
                    <TabPanel v-for="sub in tab.subCats" :key="sub.id" :header="sub.description">

                        <TabView>
                            <TabPanel v-for="type in sub.types" :key="type.id" :header="type.description">

                                <DataTable :value="type.accts" :paginator="true" class="p-datatable-gridlines"
                                    :rows="10" dataKey="id" :rowHover="true" filterDisplay="menu"
                                    responsiveLayout="scroll">
                                    <Column header="Created Date" style="min-width: 10rem">
                                        <template #body="{ data }">
                                            {{ data.created_at }}
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
                                    <Column field="name" header="Acct No." style="min-width: 10rem">
                                        <template #body="{ data }">
                                            {{ data.acct_no }}
                                        </template>
                                    </Column>
                                    <!-- <Column field="name" header="Acct Type" style="min-width: 10rem">
                                        <template #body="{ data }">
                                            {{ data.acct_type }}
                                        </template>
                                    </Column> -->
                                    <Column field="name" header="Ledger No." style="min-width: 10rem">
                                        <template #body="{ data }">
                                            {{ data.gl_no }}
                                        </template>
                                    </Column>
                                    <Column headerStyle="max-width:10rem;">
                                        <template #body="{ data }">
                                                <Button icon="pi pi-eye" @click="goToDetails(data)" class="p-button-primary mr-2"  v-tooltip="'Gl. account statement'"/>
                                                <!-- <Button icon="pi pi-pencil" @click="updateGl(data)" class="p-button-success mr-2" /> -->
                                        </template>
                                    </Column>

                                </DataTable>

                            </TabPanel>
                        </TabView>

                    </TabPanel>
                </TabView>

            </TabPanel>
        </TabView>
    </div>
</template>
