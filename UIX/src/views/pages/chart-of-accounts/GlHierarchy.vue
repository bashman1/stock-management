<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const commonService = new CommonService();
const router = useRouter();
const route = useRoute()
const toast = useToast();
const chartOfAccountData = ref(null);
const hierarchyData = ref(null);

const tabs = ref([
    { title: 'Title 1', content: 'Content 1', value: '0' },
    { title: 'Title 2', content: 'Content 2', value: '1' },
    { title: 'Title 3', content: 'Content 3', value: '2' }
]);


const getChartOfAccount = () => {
    commonService.genericRequest('get-chart-accounts', 'post', true, {}).then((response) => {
        if (response.status) {
            chartOfAccountData.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getGlHierarchy = () => {
    commonService.genericRequest('get-gl-hierarchy', 'post', true, {status:"Active"}).then((response) => {
        if (response.status) {
            hierarchyData.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const stringify=(data)=>{
    return JSON.stringify(data)
}

// get-gl-hierarchy

onMounted(() => {
    getChartOfAccount();
    getGlHierarchy()
});


</script>

<template>
<div className="card">
    <h5>Chart Of Accounts </h5>
               <Accordion :activeIndex="0">
                    <AccordionTab v-for="tab in chartOfAccountData" :key="tab.description" :header="tab.description">
                        <!-- <p class="m-0">{{stringify(tab)}}</p> -->
                        <!-- <p class="m-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed nec mauris ut turpis malesuada ultricies eu porttitor nibh. Nunc in ornare velit. Vestibulum accumsan mattis varius. Maecenas lacinia leo vel risus bibendum sodales. Morbi eros augue, lacinia eget massa eu, laoreet ornare mi. Pellentesque aliquam mauris eget hendrerit volutpat. Quisque semper sapien et interdum lacinia. Vivamus imperdiet laoreet sem, ut iaculis orci malesuada quis</p> -->

                        <Accordion :activeIndex="0">
                            <AccordionTab v-for="subCat in tab.subCats" :key="subCat.description" :header="subCat.description">
                                <!-- <p class="m-0">{{stringify(tab)}}</p> -->
                                <!-- <p class="m-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed nec mauris ut turpis malesuada ultricies eu porttitor nibh. Nunc in ornare velit. Vestibulum accumsan mattis varius. Maecenas lacinia leo vel risus bibendum sodales. Morbi eros augue, lacinia eget massa eu, laoreet ornare mi. Pellentesque aliquam mauris eget hendrerit volutpat. Quisque semper sapien et interdum lacinia. Vivamus imperdiet laoreet sem, ut iaculis orci malesuada quis</p> -->
                                <Accordion :activeIndex="0">
                                    <AccordionTab v-for="type in subCat.types" :key="type.description" :header="type.description">
                                        <!-- <p class="m-0">{{stringify(tab)}}</p> -->
                                        <!-- <p class="m-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed nec mauris ut turpis malesuada ultricies eu porttitor nibh. Nunc in ornare velit. Vestibulum accumsan mattis varius. Maecenas lacinia leo vel risus bibendum sodales. Morbi eros augue, lacinia eget massa eu, laoreet ornare mi. Pellentesque aliquam mauris eget hendrerit volutpat. Quisque semper sapien et interdum lacinia. Vivamus imperdiet laoreet sem, ut iaculis orci malesuada quis</p> -->
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
                                    </AccordionTab>
                                </Accordion>

                            </AccordionTab>
                        </Accordion>

                    </AccordionTab>
                </Accordion>

</div>
</template>
