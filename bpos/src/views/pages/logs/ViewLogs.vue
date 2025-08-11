<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const logs = ref(null);
const commonService = new CommonService();
//  const router = useRouter();

const getSystemLogs = (currentPage) => {
    commonService.genericRequest(`get-audit-trail?page=${currentPage}`, 'get', true, {}).then((response) => {
        if (response.status) {
            logs.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onPageChange =(event)=>{
    getSystemLogs(event.page +1)
    // {"originalEvent":{"page":0,"first":0,"rows":15,"pageCount":32},"first":0,"rows":15,"sortField":null,"sortOrder":null,"multiSortMeta":[],"filters":{},"pageCount":32,"page":0}
}

onMounted(() => {
    getSystemLogs(1);
});

</script>
<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <h5>Audit Logs</h5>
                <DataTable :value="logs?.data"
                :paginator="true"
                class="p-datatable-gridlines"
                lazy
                :totalRecords="logs?.total"
                @page="onPageChange($event)"
                :size="'small'"
                :rows="15" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">

                    <Column field="action" header="Action" style="min-width: 8rem">
                        <template #body="{ data }">
                            {{ data.action }}
                        </template>
                    </Column>
                    <Column header="IP" style="min-width: 7rem">
                        <template #body="{ data }">
                            {{ data.ip }}
                        </template>
                    </Column>
                    <Column header="Message" style="min-width: 14rem">
                        <template #body="{ data }">
                            {{ data.return_message }}
                        </template>
                    </Column>
                    <Column header="HTTP" style="min-width: 4rem">
                        <template #body="{ data }">
                            {{ data.http_code }}
                        </template>
                    </Column>
                    <Column header="Status" style="min-width: 9rem">
                        <template #body="{ data }">
                            <span>
                                {{ data.return_status?"Successful":"Failed" }}
                                <i class="pi pi-circle-fill" :style="{'color': data.return_status?'green':'red'}"></i>
                            </span>
                        </template>
                    </Column>
                    <Column header="Role" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.role }}
                        </template>
                    </Column>
                    <Column header="User" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.user }}
                        </template>
                    </Column>
                    <Column header="User type" style="min-width: 8rem">
                        <template #body="{ data }">
                            {{ data.user_type }}
                        </template>
                    </Column>
                    <Column header="Institution" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.institution }}
                        </template>
                    </Column>
                    <Column header="Branch" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.branch }}
                        </template>
                    </Column>
                    <Column header="Date" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ commonService.formatDate(new Date(data.created_at)) }}
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
                <!-- <Paginator :rows="15" :totalRecords="logs?.total"></Paginator> -->
            </div>
        </div>
    </div>
</template>
