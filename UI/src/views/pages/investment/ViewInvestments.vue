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

const maturityDate = (duration, startDate)=>{
    if (!duration || !startDate) return '';

    let date = new Date(startDate);
    switch (duration) {
        case 'Daily':
            date.setDate(date.getDate() + 1);
            break;
        case 'Weekly':
            date.setDate(date.getDate() + 7);
            break;
        case 'Monthly':
            date.setMonth(date.getMonth() + 1);
            break;
        case 'Quarterly':
            date.setMonth(date.getMonth() + 3);
            break;
        case 'Yearly':
            date.setFullYear(date.getFullYear() + 1);
            break;
        default:
            return '';
    }
    return commonService.formatDate(date);
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
                     <Column field="quantity" header="Initial Investment" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ commonService.commaSeparator(data.quantity * data.monitory) }}
                        </template>
                    </Column>
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
                        <Column header="Return Of Investment" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ commonService.commaSeparator(data.benefit * data.quantity * data.monitory) }}
                        </template>
                    </Column>
                           <Column header="Maturity Date" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ maturityDate(data.duration, data.date) }}
                        </template>
                    </Column>
                    <Column header="Date" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.date	 }}
                        </template>
                    </Column>
                    <Column headerStyle="min-width:10rem;">
                        <template #body="{ data }">
                            <Button icon="pi pi-plus" class=" p-button-success mr-2"
                                @click="AddRolesPermissions(data)" />
                            <!-- <Button icon="pi pi-trash" class=" p-button-warning mt-2"
                                @click="confirmDeleteProduct(data)" /> -->
                        </template>
                    </Column>

                </DataTable>
            </div>
        </div>

    <!-- <Dialog v-model:visible="deleteProductsDialog" :style="{ width: '450px' }" :header="'Confirm'" :modal="true">
        <div class="flex align-items-center justify-content-center">
            <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
            <span>Are you sure you want to archive {{ selectedProduct?.name }}?</span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" class="p-button-outlined p-button-danger mr-2 mb-2" @click="deleteProductsDialog = false" />
            <Button label="Yes" icon="pi pi-check" class="p-button-outlined mr-2 mb-2" @click="archiveProduct" />
        </template>
    </Dialog> -->
    </div>
</template>
