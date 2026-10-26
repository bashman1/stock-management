<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const users = ref(null);
const commonService = new CommonService();
const displayConfirmation = ref(false);
const selectedUser = ref({});

// filters + lazy-load state
const searchKeyWord = ref(null);
const statusFilter = ref(null);
const startDate = ref(null);
const endDate = ref(null);
const rowsPerPage = ref(15);
const currentPage = ref(1);
const loading = ref(false);
const statusOptions = ref([
    { label: 'Active', value: 'Active' },
    { label: 'Archived', value: 'Archived' },
    { label: 'Inactive', value: 'Inactive' }
]);

const router = useRouter();

const fmtDate = (d) => { if (!d) return null; const dt = new Date(d); return isNaN(dt.getTime()) ? null : commonService.toApiDate(dt); };

const getAllUsers = () => {
    loading.value = true;
    const postData = {
        page: currentPage.value,
        rowPerPage: rowsPerPage.value,
        search: searchKeyWord.value,
        status: statusFilter.value,
        startDate: fmtDate(startDate.value),
        endDate: fmtDate(endDate.value),
    }
    commonService.genericRequest('get-users-list', 'post', true, postData).then((response) => {
        loading.value = false;
        if (response.status) {
            users.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    }).catch(() => { loading.value = false; })
}

const onPageChange = (event) => {
    rowsPerPage.value = event.rows;
    currentPage.value = Number(event.page) + 1;
    getAllUsers();
}

const applyFilters = () => {
    currentPage.value = 1;
    getAllUsers();
}

const resetFilters = () => {
    searchKeyWord.value = null;
    statusFilter.value = null;
    startDate.value = null;
    endDate.value = null;
    currentPage.value = 1;
    getAllUsers();
}

const editUser = (event) => {
    router.push("/create-user/" + event?.id);
}


const ViewUserDetails = (event) => {
    commonService.redirect(router, "/user-profile/" + event?.id)
}

const archiveUser = (event) => {
    let postData = {
        userId: event?.id,
        status: 'Archived',
    }
    commonService.genericRequest('update-user-status', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getAllUsers();
            displayConfirmation.value = false;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const confirmDeleting = (event) => {
 displayConfirmation.value = true;
 selectedUser.value = event;
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
                <Toolbar class="mb-3">
                    <template #start>
                        <span class="p-float-label mr-2">
                            <InputText id="search" type="text" v-model="searchKeyWord" @keyup.enter="applyFilters" />
                            <label for="search">Search name / email / phone</label>
                        </span>
                        <span class="p-float-label mr-2">
                            <Dropdown id="statusF" :options="statusOptions" v-model="statusFilter" optionLabel="label" optionValue="value" showClear class="w-10rem" />
                            <label for="statusF">Status</label>
                        </span>
                        <span class="p-float-label mr-2">
                            <Calendar id="sd" showIcon v-model="startDate" dateFormat="yy-mm-dd" />
                            <label for="sd">From</label>
                        </span>
                        <span class="p-float-label mr-2">
                            <Calendar id="ed" showIcon v-model="endDate" dateFormat="yy-mm-dd" />
                            <label for="ed">To</label>
                        </span>
                    </template>
                    <template #end>
                        <Button label="Search" icon="pi pi-search" class="mr-2" @click="applyFilters" />
                        <Button label="Reset" icon="pi pi-times" severity="secondary" outlined @click="resetFilters" />
                    </template>
                </Toolbar>
                <DataTable :value="users?.data" :paginator="true" class="p-datatable-gridlines" :rows="rowsPerPage" dataKey="id"
                    :rowHover="true" :totalRecords="users?.total" :loading="loading" lazy @page="onPageChange($event)"
                    :rowsPerPageOptions="[10, 15, 20, 25, 50, 100]" responsiveLayout="scroll">

                    <Column field="name" header="Name" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.first_name + " " + data.last_name + " " + data.other_name }}
                        </template>
                    </Column>
                    <Column header="Email / Username" style="min-width: 14rem">
                        <template #body="{ data }">
                            {{ data.email }}
                        </template>
                    </Column>
                    <Column header="Institution" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.institution }}
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
                    <Column header="Role" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ data.role }}
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
                            <Button icon="pi pi-eye" @click="ViewUserDetails(data)" class="p-button-primary mr-2"
                                v-tooltip="'View user details'" />
                            <Button icon="pi pi-pencil" @click="editUser(data)" class="p-button-success mr-2"
                                v-tooltip="'Edit user details'" />
                            <Button icon="pi pi-trash" class="p-button-danger mr-2" @click="confirmDeleting(data)"
                                v-tooltip="'Archive user'" />
                            <!-- <Button icon="pi pi-pencil" class="p-button-rounded p-button-success mr-2"
                                @click="AddRolesPermissions(data)" />
                            <Button icon="pi pi-trash" class="p-button-rounded p-button-warning mt-2"
                                @click="confirmDeleteProduct(data)" /> -->
                        </template>
                    </Column>

                </DataTable>
            </div>

            <div class="card p-fluid">
                <!-- <h5>Confirmation</h5> -->
                <!-- <Button label="Delete" icon="pi pi-trash" class="p-button-danger" style="width: auto" @click="openConfirmation" /> -->
                <Dialog header="Confirmation" v-model:visible="displayConfirmation" :style="{ width: '350px' }"
                    :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>Are you sure you want to archive?</span>
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" @click="displayConfirmation = false" class="p-button-text" />
                        <Button label="Yes" icon="pi pi-check" @click="archiveUser(selectedUser)" class="p-button-text" autofocus />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>
