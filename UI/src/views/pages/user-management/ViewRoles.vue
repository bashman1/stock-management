<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const roles = ref(null);
const commonService = new CommonService();

 const router = useRouter();

const AddRolesPermissions=(role)=>{
    alert(JSON.stringify(role))
}


const getAllRoles = () => {
    commonService.genericRequest('get-roles', 'get', true, {}).then((response) => {
        if (response.status) {
            // roles.value = {data:response.data};
            roles.value = response.data;
            // alert(JSON.stringify(roles))
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const goToAssignRolePermissions=(event)=>{
    router.push("/role-permission/"+event?.id);
}

onMounted(() => {
    getAllRoles();
});

</script>
<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <h5>Roles</h5>
                <DataTable :value="roles" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                    :rowHover="true"  filterDisplay="menu"
                    responsiveLayout="scroll">

                    <Column field="name" header="Name" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                    </Column>
                    <Column header="Type"  style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.type }}
                        </template>
                    </Column>
                    <Column header="Description"  style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.description }}
                        </template>
                    </Column>
                    <Column header="Status"  style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.status }}
                        </template>
                    </Column>
                    <Column header="Created By"  style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.created_by }}
                        </template>
                    </Column>
                    <Column header="Date"  style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.created_at }}
                        </template>
                    </Column>
                    <Column headerStyle="min-width:10rem;">
                        <template #body="{data}">
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-outlined-success  p-button-outlined mr-2" @click="AddRolesPermissions(data)" />
                            <Button icon="pi pi-arrow-right-arrow-left" class="p-button-rounded p-button-outlined mr-2 mt-2" @click="goToAssignRolePermissions(data)" />
                            <!-- <Button icon="pi pi-check" class="p-button-rounded p-button-outlined mr-2" /> -->
                        </template>

                    </Column>

                </DataTable>
            </div>
        </div>
    </div>
</template>
