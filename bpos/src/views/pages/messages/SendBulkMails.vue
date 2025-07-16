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
const showUploadEmails = ref(false);
const showMessageInput = ref(false);
const selectedMember = ref([]);
const data = ref(null);
const subject = ref(null)

const handleFileChange = (event) => {
    uploadedFile.value = event.target.files[0];
};

const toggleModal = (action) => {
    showUploadEmails.value = action
}

const toggleMessageModal=(action)=>{
    showMessageInput.value = action
}

const uploadFile = async () => {
    if (!uploadedFile.value) {
        return;
    }
    const formData = new FormData();
    formData.append('file', uploadedFile.value);
    formData.append("name", uploadedFile.value.name);
    try {
        const response = await fetch(commonService.baseUrl + 'upload-mail-receivers', {
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
        toggleModal(false)
        getReceivers();
        // router.push("/view-uploaded-members");
    } catch (error) {
        console.error(error.message);
        commonService.showError(toast, error.message);
    }
}

const getReceivers = () => {
    commonService.genericRequest(`get-mailReceivers`, 'get', true, {}).then((response) => {
        if (response.status) {
            receivers.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onSubmitMessage =()=>{
    let postData ={
        subject:subject.value,
        body:data.value,
        receivers: selectedMember.value
    }
    commonService.genericRequest(`send-bulk-mails`, 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, data.message);
            toggleMessageModal(false)
            subject.value = null;
            data.value= null;
            // receivers.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const updateContent = (newValue) => {
      data.value = newValue; // Ensure that the data updates correctly
    };

onMounted(() => {
    getReceivers();
});

</script>

<template>
    <div class="card">
        <h5>Bulk Email Receiver</h5>

        <!-- <div class="grid p-fluid mt-3"> -->
        <Toast />


        <!-- <div class="field col-12 md:col-12"> -->
        <DataTable :value="receivers" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
            :rowHover="true" filterDisplay="menu" responsiveLayout="scroll" v-model:selection="selectedMember">
            <template #header>
                <div class="text-end pb-4">
                    <Button icon="pi pi-upload" label="Upload email address" @click="toggleModal(true)" class="mr-2" />
                    <Button icon="pi pi-inbox" label="Compose Message" @click="toggleMessageModal(true)" class="mr-2" :disabled="!selectedMember || !selectedMember.length" />
                </div>
            </template>
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
            <Column header="Date" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.created_at }}
                </template>
            </Column>
            <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
        </DataTable>

        <!-- </div> -->
        <!-- </div> -->

        <!-- Modals -->
        <Dialog header="Upload Email Address" v-model:visible="showUploadEmails" :style="{ width: '30vw' }"
            :modal="true" class="p-fluid">
            <!-- <p style="padding-top: 20px;"> -->
            <div class="grid p-fluid">
                <a href="demo/documents/Mail_Receivers.xlsx">
                    <i class="pi pi-cloud-download" style="font-size: 2rem"></i>
                    <p>Download template</p>
                </a>
                <div class="field col-12 md:col-12">
                    <input type="file" accept=".xlsx, .xls" @change="handleFileChange($event)" />
                </div>
            </div>
            <!-- </p> -->
            <template #footer>
                <Button label="Cancel" @click="toggleModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="uploadFile" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>

        <Dialog header="Create Message" v-model:visible="showMessageInput" :style="{ width: '90vw' }"
        :modal="true" class="p-fluid">
        <div class="grid p-fluid">
            <div class="field col-12 md:col-12 mt-10">
                <span class="p-float-label">
                    <InputText type="text" id="subject" v-model="subject"
                         /> <!-- class="p-invalid"-->
                    <label for="subject">Subject</label>
                </span>
            </div>
            <!-- <div class="field col-12 md:col-12">

            </div> -->
            <div class="field col-12 md:col-12">
                <QuillEditor  v-model:content="data" contentType="html" toolbar="full" theme="snow" />
            </div>

            <!-- <Ckeditor :editor="ClassicEditor" v-model="data"></Ckeditor> -->
        </div>

        <template #footer>
            <Button label="Cancel" @click="toggleMessageModal(false)" icon="pi pi-times"
                class="p-button-outlined p-button-danger mr-2 mb-2" />
            <Button @click="onSubmitMessage" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
        </template>
    </Dialog>

        <!-- End Modals -->

    </div>
</template>
