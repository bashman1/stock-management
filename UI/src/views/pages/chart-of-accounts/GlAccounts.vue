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
    toggleEditModal(true)

}

const toggleEditModal = (action) => {
    updateGlModal.value = action
}

const updateGlName = () => {
    formError.value.description = commonService.validateFormField(description.value);
    let invalid = commonService.validateRequiredFields(formError.value);
    if (invalid) {
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData={
        description:description.value,
        acctNo: acctNo.value
    }
    commonService.genericRequest('update-gl-account', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getGlAccounts();
            toggleEditModal(false)
        } else {
            commonService.showError(toast, response.message);
        }
    })
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
            <Column field="name" header="Acct Balance" style="min-width: 10rem">

                <template #body="{ data }">
                    {{ data.balance }}
                </template>
            </Column>
            <Column headerStyle="max-width:10rem;">
                <template #body="{ data }">
                    <Button icon="pi pi-file-edit" class="p-button-rounded p-button-success mr-2"
                        @click="updateGl(data)" />
                    <!-- <Button icon="pi pi-check" class="p-button-rounded p-button-warning mt-2"
                        @click="confirmDeleteProduct(data)" /> -->
                </template>
            </Column>
        </DataTable>

        <Dialog header="Update Ledger Name" v-model:visible="updateGlModal" :style="{ width: '30vw' }" :modal="true"
            class="p-fluid">
            <p style="padding-top: 20px;">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="description" @blur="onInputBlur(description, 'description')" rows="7"
                            v-model="description" :class="{ 'p-invalid': formError?.description }"></Textarea>
                        <label for="description">Description</label>
                    </span>
                </div>
            </div>
            </p>
            <template #footer>
                <Button label="Cancel" @click="toggleEditModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="updateGlName" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>

    </div>
</template>
