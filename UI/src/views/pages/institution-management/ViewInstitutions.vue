<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const router = useRouter();
const route = useRoute()

const toast = useToast();
const institution = ref(null);
const commonService = new CommonService();

const getAllInstitutions = () => {
    commonService.genericRequest('get-institutions', 'get', true, {}).then((response) => {
        if (response.status) {
            institution.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const editInstitution = (event) => {
    router.push("/create-institution/" + event?.id);
}

onMounted(() => {
    getAllInstitutions();
});

</script>
<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <h5>Roles</h5>
                <DataTable :value="institution" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">

                    <Column field="name" header="Name" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                    </Column>
                    <Column header="Type" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.type_name }}
                        </template>
                    </Column>
                    <Column header="Description" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.description }}
                        </template>
                    </Column>
                    <Column header="Status" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.status }}
                        </template>
                    </Column>
                    <Column header="Created By" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.created_by }}
                        </template>
                    </Column>
                    <Column header="Date" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.created_at }}
                        </template>
                    </Column>
                    <Column headerStyle="min-width:10rem;">
                        <template #body="{ data }">
                            <Button icon="pi pi-eye" @click="editInstitution(data)" class="p-button-primary mr-2"  v-tooltip="'View institution details'" />
                            <Button icon="pi pi-pencil" @click="editInstitution(data)" class="p-button-success mr-2"  v-tooltip="'Edit institution details'" />
                            <Button icon="pi pi-trash" class="p-button-danger mr-2" @click="editInstitution(data)"  v-tooltip="'Archive institution'" />
                        </template>
                    </Column>

                </DataTable>
            </div>
        </div>
    </div>
</template>
