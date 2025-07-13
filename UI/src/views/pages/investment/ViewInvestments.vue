<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const members = ref(null);
const commonService = new CommonService();

//  const router = useRouter();


const getAllMembers = () => {
    commonService.genericRequest('get-investments', 'get', true, {}).then((response) => {
        if (response.status) {
            members.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

onMounted(() => {
    getAllMembers();
});

</script>
<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <h5>Projects</h5>
                <DataTable :value="members" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">

                    <Column field="customer" header="Name" style="min-width: 10rem">
                        <!-- <template #body="{ data }">
                            {{ data.first_name+" "+data.last_name+" " }}
                        </template> -->
                    </Column>
                    <Column field="project_name" header="Project" style="min-width: 10rem">

                        <!-- <template #body="{ data }">
                            {{ data.gender }}
                        </template> -->
                    </Column>
                     <Column field="quantity" header="Quantity" style="min-width: 10rem"></Column>
                    <Column field="benefit" header="Benefit" style="min-width: 12rem">
                        <!-- <template #body="{ data }">
                            {{ data.benefit +' '+ data.benefit_type }}
                        </template> -->
                    </Column>
                    <Column field="benefit_type" header="Benefit Type" style="min-width: 10rem" />
                    <Column header="Duration" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.duration }}
                        </template>
                    </Column>
                    <Column field="status" header="Status" style="min-width: 10rem" />
                    <!-- <Column header="Member No." style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.member_number }}
                        </template>
                    </Column> -->
                    <Column header="Date" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.created_at }}
                        </template>
                    </Column>
                    <!-- <Column headerStyle="min-width:10rem;">
                        <template #body="{ data }">
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-success mr-2"
                                @click="AddRolesPermissions(data)" />
                            <Button icon="pi pi-trash" class="p-button-rounded p-button-warning mt-2"
                                @click="confirmDeleteProduct(data)" />
                        </template>
                    </Column> -->

                </DataTable>
            </div>
        </div>
    </div>
</template>
