<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();
const uploadedFile = ref(null);
const receivers = ref(null);

const handleFileChange = (event) => {
    uploadedFile.value = event.target.files[0];
};


const uploadFile = async () => {
    if (!uploadedFile.value) {
        return;
    }
    const formData = new FormData();
    formData.append('file', uploadedFile.value);
    formData.append("name", uploadedFile.value.name);
    try {
        const response = await fetch(commonService.baseUrl+'upload-mail-receivers', {
            method: 'POST',
            body: formData,
            headers: {
                Authorization: `Bearer ${commonService.getStorage().token}`,
            },
        });
        if (!response.ok) {
            throw new Error('Upload failed');
        }
        const data = await response.json();
        commonService.showSuccess(toast, data.message);
        // router.push("/view-uploaded-members");
    } catch (error) {
        console.error(error.message);
        commonService.showError(toast, error.message);
    }
}

const getReceivers=()=>{
    commonService.genericRequest(`get-mailReceivers`, 'get', true, {}).then((response) => {
        if (response.status) {
            receivers.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

onMounted(() => {
    getReceivers(1);
});

</script>

<template>
    <div class="card">
        <h5>Bulk Email Receiver</h5>
        <a href="demo/documents/Mail_Receivers.xlsx">
            <i class="pi pi-cloud-download" style="font-size: 2rem"></i>
            <p>Download template</p>
        </a>
        <div class="grid p-fluid mt-3">
            <Toast />
            <div class="field col-12 md:col-12">
                <input type="file" accept=".xlsx, .xls" @change="handleFileChange($event)" />
            </div>
            <div class="field col-12 md:col-8"></div>
            <div class="field col-12 md:col-4">
                <Button @click="uploadFile" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </div>

            <div class="field col-12 md:col-12">
                <DataTable :value="receivers" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">

                <Column field="name" header="Name" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.name }}
                    </template>
                </Column>
                <Column header="Email" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.email }}
                    </template>
                </Column>
                <Column header="Category" style="min-width: 12rem">
                    <template #body="{ data }">
                        {{ data.category }}
                    </template>
                </Column>
                <Column header="Institution" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.institution_name }}
                    </template>
                </Column>
                <!-- <Column header="Created By" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.created_at }}
                    </template>
                </Column> -->
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
