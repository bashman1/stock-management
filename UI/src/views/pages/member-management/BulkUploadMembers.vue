<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();
const uploadedFile = ref(null);
const selectedFile = ref(null);

const onUpload = (event) => {

    const formData = new FormData();
    formData.append('file', event.target.files[0]);
    console.log(formData)
    alert("submitting");
    commonService.genericFormRequest('upload-members', 'post', true, formData).then((response) => {
        console.log(response);
    })
}


const onSubmit = () => {
    if (uploadedFile?.value == null) {
        commonService.showError(toast, "Please upload a file");
    }
    let postData = {
        excelData: uploadedFile.value,
        type: 'File'
    }
    commonService.genericRequest('upload-members', 'post', true, postData).then((response) => {
        console.log(response);
    })
}

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
        const response = await fetch(commonService.baseUrl+'upload-members', {
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
        router.push("/view-uploaded-members");
    } catch (error) {
        console.error(error.message);
        commonService.showError(toast, error.message);
    }
}

</script>

<template>
    <div class="card">
        <h5>Bulk Upload</h5>
        <a href="demo/documents/upload_bulk_members.xlsx">
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
        </div>
    </div>
</template>
