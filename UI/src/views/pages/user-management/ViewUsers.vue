<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const users = ref(null);
const commonService = new CommonService();

//  const router = useRouter(); 

const AddRolesPermissions=(role)=>{
    alert(JSON.stringify(role))
}

const getAllUsers = () => {
    commonService.genericRequest('get-users', 'get', false, {}).then((response) => {
        if (response.status) {
            users.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

onMounted(() => {
    getAllUsers();
});

</script>
<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <h5>Users</h5>
                <DataTable :value="users" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">

                    <Column field="name" header="Name" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.first_name+" "+data.last_name+" "+data.other_name }}
                        </template>
                    </Column>
                    <Column header="Gender" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.gender }}
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
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-success mr-2"
                                @click="AddRolesPermissions(data)" />
                            <Button icon="pi pi-trash" class="p-button-rounded p-button-warning mt-2"
                                @click="confirmDeleteProduct(data)" />
                        </template>
                    </Column>

                </DataTable>
            </div>
        </div>
    </div>
</template>
