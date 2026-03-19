<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();
const uploadedFile = ref(null);
const receivers = ref(null);
const categories = ref([]);
const showUploadEmails = ref(false);
const showMessageInput = ref(false);
const selectedMember = ref([]);
const data = ref(null);
const subject = ref(null)
const composeByCategory = ref(false);

// Pagination variables
const currentPage = ref(1);
const rowsPerPage = ref(15);
const selectedCategory = ref(null);

// Get unique categories from selected members
const memberCategories = computed(() => {
    if (!selectedMember.value || selectedMember.value.length === 0) {
        return [];
    }
    const categorySet = new Set();
    selectedMember.value.forEach(member => {
        if (member.category) {
            categorySet.add(member.category);
        }
    });
    return Array.from(categorySet).map(cat => ({ name: cat }));
});

const selectedMessageCategory = ref(null);

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
    } catch (error) {
        console.error(error.message);
        commonService.showError(toast, error.message);
    }
}

const getReceivers = () => {
    // Build query string for GET request
    const params = new URLSearchParams();
    params.append('page', currentPage.value);
    params.append('per_page', rowsPerPage.value);
    if (selectedCategory.value?.name) {
        params.append('category', selectedCategory.value.name);
    }

    commonService.genericRequest(`get-mailReceivers?${params.toString()}`, 'get', true, {}).then((response) => {
        if (response.status) {
            receivers.value = response.data.mail_receivers;
            // Convert categories array from strings to objects with name property for Dropdown
            categories.value = (response.data.categories || []).map(cat => ({ name: cat }));
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onPageChange = (event) => {
    rowsPerPage.value = event.rows;
    currentPage.value = Number(event.page) + 1;
    getReceivers();
}

const onCategoryChange = () => {
    currentPage.value = 1;
    getReceivers();
}

const onSubmitMessage =()=>{
    let postData ={
        subject:subject.value,
        body:data.value,
        receivers: selectedMember.value,
        category: composeByCategory.value
    }
    commonService.genericRequest(`send-bulk-mails`, 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, data.message);
            toggleMessageModal(false)
            subject.value = null;
            data.value= null;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const updateContent = (newValue) => {
      data.value = newValue;
};

onMounted(() => {
    getReceivers();
});

</script>

<template>
    <div class="card">
        <h5>Bulk Email Receiver</h5>

        <Toast />

        <DataTable
            :value="receivers?.data"
            :paginator="true"
            class="p-datatable-gridlines"
            :rows="rowsPerPage"
            dataKey="id"
            :rowHover="true"
            filterDisplay="menu"
            responsiveLayout="scroll"
            v-model:selection="selectedMember"
            lazy
            @page="onPageChange($event)"
            :totalRecords="receivers?.total"
            :rowsPerPageOptions="[1,5, 10, 15, 20, 25, 30, 50, 100]"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            :currentPageReportTemplate="'Showing {first} to {last} of {totalRecords} records'"
        >
            <template #header>
                <div class="flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div class="flex gap-2">
                        <MultiSelect v-model="selectedMember" :options="receivers?.data" optionLabel="name" placeholder="Select Receivers"
                            class="w-full sm:w-20rem" :maxSelectedLabels="3" />
                        <Button icon="pi pi-filter" label="Filter" @click="getReceivers" />
                    </div>
                    <div class="flex gap-2">
                        <Dropdown v-model="selectedCategory" :options="categories" optionLabel="name" placeholder="Filter by Category"
                            @change="onCategoryChange" showClear class="w-full sm:w-14rem" />
                        <Button icon="pi pi-upload" label="Upload email address" @click="toggleModal(true)" class="mr-2" />
                        <Button icon="pi pi-inbox" label="Compose Message" @click="toggleMessageModal(true)" class="mr-2" :disabled="!selectedMember || !selectedMember.length" />
                    </div>
                </div>
            </template>
            <Column header="#" style="width: 3rem; text-align: center;">
                <template #body="{ index }">
                    {{ index + 1 }}
                </template>
            </Column>
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
                    {{ commonService.formatDate(data.created_at) }}
                </template>
            </Column>
            <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
        </DataTable>

        <!-- Modals -->
        <Dialog header="Upload Email Address" v-model:visible="showUploadEmails" :style="{ width: '30vw' }"
            :modal="true" class="p-fluid">
            <div class="grid p-fluid">
                <a href="demo/documents/Mail_Receivers.xlsx">
                    <i class="pi pi-cloud-download" style="font-size: 2rem"></i>
                    <p>Download template</p>
                </a>
                <div class="field col-12 md:col-12">
                    <input type="file" accept=".xlsx, .xls" @change="handleFileChange($event)" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" @click="toggleModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="uploadFile" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>

        <Dialog header="Create Message" v-model:visible="showMessageInput" :style="{ width: '90vw' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        :modal="true" class="p-fluid">
        <div class="grid p-fluid">
            <div class="field col-12 md:col-4 mt-10">
                <div class="flex align-items-center mb-3">
                    <span class="mr-2">Compose by Category:</span>
                    <InputSwitch v-model="composeByCategory" />
                </div>
            </div>
            <div class="field col-12 md:col-8 mt-10" v-if="composeByCategory">
                <div class="flex flex-wrap gap-2">
                    <Tag v-for="category in memberCategories" :key="category.name" :value="category.name"
                        class="p-2 text-lg" severity="info" rounded />
                </div>
            </div>
            <div class="field col-12 md:col-12 mt-10">
                <span class="p-float-label">
                    <InputText type="text" id="subject" v-model="subject"
                         />
                    <label for="subject">Subject</label>
                </span>
            </div>
            <div class="field col-12 md:col-12">
                <QuillEditor  v-model:content="data" contentType="html" toolbar="full" theme="snow" />
            </div>
        </div>

        <template #footer>
            <Button label="Cancel" @click="toggleMessageModal(false)" icon="pi pi-times"
                class="p-button-outlined p-button-danger mr-2 mb-2" />
            <Button @click="onSubmitMessage" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
        </template>
    </Dialog>

    </div>
</template>
