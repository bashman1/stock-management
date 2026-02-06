<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const members = ref(null);
const commonService = new CommonService();

 const router = useRouter();
 const deleteMemberDialog = ref(false);
const selectedMember = ref(null);

 const toggleModal=(action)=>{
    deleteMemberDialog.value = action;
 }

const confirmDeleteMember =(data)=>{
    selectedMember.value = data;
    toggleModal(true);
}


const deleteMember =()=>{
    let postData = {
        id: selectedMember.value.id
    };

    commonService.genericRequest('delete-member', 'post', true, postData).then((response) => {
        if (response.status) {
            // members.value = response.data;
            commonService.showSuccess(toast, response.message);
            getAllMembers();
            toggleModal(false);
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const AddRolesPermissions=(role)=>{
    // alert(JSON.stringify(role))
}

const getAllMembers = () => {
    commonService.genericRequest('get-members', 'get', true, {}).then((response) => {
        if (response.status) {
            members.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const viewDetails = (member) => {
    commonService.redirect(router, 'member-profile/'+member.id);
}

onMounted(() => {
    getAllMembers();
});

</script>
<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <h5>Members</h5>
                <DataTable :value="members" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">

                    <Column field="name" header="Name" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.first_name+" "+data.last_name+" " }}
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
                    <Column header="Member No." style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.member_number }}
                        </template>
                    </Column>
                    <Column header="Date" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.created_at }}
                        </template>
                    </Column>
                    <Column headerStyle="min-width:10rem;">
                        <template #body="{ data }">
                            <Button icon="pi pi-eye" class="p-button p-button-success mr-2"
                                @click="viewDetails(data)" />
                            <Button icon="pi pi-pencil" class="p-button p-button-success mr-2"
                                @click="AddRolesPermissions(data)" />

                            <Button icon="pi pi-trash" class="p-button p-button-danger mt-2"
                                @click="confirmDeleteMember(data)" />
                        </template>
                    </Column>

                </DataTable>
            </div>
        </div>

        <Dialog v-model:visible="deleteMemberDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="selectedMember"
                            >Are you sure you want to delete <b>{{ selectedMember?.first_name }}</b
                            >?</span
                        >
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteMemberDialog = false" />
                        <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteMember" />
                    </template>
                </Dialog>
    </div>
</template>
