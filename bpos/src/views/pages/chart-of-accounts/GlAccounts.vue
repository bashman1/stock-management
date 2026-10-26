<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const glAccounts = ref(null);
const commonService = new CommonService();
const router = useRouter();
const route = useRoute()

const updateGlModal = ref(false)
const description = ref(null);
const acctNo = ref(null);
const formError = ref({});
const debitSelected = ref(null);
const isAdd = ref(false);

const getGlAccounts = () => {
    commonService.genericRequest('get-gl-accounts', 'get', true, {}).then((response) => {
        if (response.status) {
            glAccounts.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onInputBlur = (value, key) => {
    formError.value[key] = commonService.validateFormField(value);
}

const updateGl = (event) => {
    description.value = event.description
    acctNo.value = event.acct_no
    isAdd.value = false;
    toggleEditModal(true)
}


const addSubAccount = (event)=>{
    description.value = 'Sub account for '+event.description
    isAdd.value = true;
    acctNo.value = event.acct_no
    toggleEditModal(true)
}

const toggleEditModal = (action) => {
    updateGlModal.value = action
}

const updateGlName = () => {


    formError.value.description = commonService.validateFormField(description.value);
    let invalid = commonService.validateRequiredFields(formError.value);
    if (invalid) {
        commonService.showError(toast, "Please fill in the missing field");   // GlHierarchy
        return
    }

    let postData={
        description:description.value,
        acctNo: acctNo.value
    }

    let url = "update-gl-account"

    if (isAdd.value){
        url = "create-sub-account"
    }

    commonService.genericRequest(url, 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getGlAccounts();
            toggleEditModal(false)
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const goToDetails = (event) => {
    router.push("/gl-statement/" + event?.acct_no);
}


onMounted(() => {
    getGlAccounts();
});

</script>

<template>
    <div className="card">
        <h5>Chart Of Accounts</h5>
        <DataTable :value="glAccounts" :paginator="true" class="p-datatable-gridlines" :rows="20" dataKey="id"
            :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
            <Column field="name" header="Name" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.description }}
                </template>
            </Column>
            <Column field="name" header="Acct Balance" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.balance) }}
                </template>
            </Column>
            <Column field="name" header="Acct No." style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.acct_no }}
                </template>
            </Column>
            <Column field="name" header="Acct Type" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.acct_type }}
                </template>
            </Column>
            <Column field="name" header="Ledger No." style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.gl_no }}
                </template>
            </Column>
            <Column headerStyle="max-width:10rem;">
                <template #body="{ data }">
                        <Button icon="pi pi-eye" @click="goToDetails(data)" class="p-button-primary mr-2"  v-tooltip="'Gl. account statement'"/>
                        <Button icon="pi pi-pencil" @click="updateGl(data)" class="p-button-success mr-2" v-tooltip="'Update gl account description'" />
                        <Button icon="pi pi-plus" @click="addSubAccount(data)" class="p-button-info mr-2" v-tooltip="'Create sub account'" />
                </template>
            </Column>
        </DataTable>

        <Dialog header="Update Ledger Name" v-model:visible="updateGlModal" :style="{ width: '30vw' }" :modal="true"
            class="p-fluid">
            <!-- <p style="padding-top: 20px;"> -->
            <div class="grid p-fluid">
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="description" @blur="onInputBlur(description, 'description')" rows="7"
                            v-model="description" :class="{ 'p-invalid': formError?.description }"></Textarea>
                        <label for="description">Description</label>
                    </span>
                </div>
            </div>
            <!-- </p> -->
            <template #footer>
                <Button label="Cancel" @click="toggleEditModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="updateGlName" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>

    </div>
</template>
