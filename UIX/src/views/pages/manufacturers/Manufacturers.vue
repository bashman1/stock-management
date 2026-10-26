<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Dialog from 'primevue/dialog';
import Rating from 'primevue/rating';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();

const manufacturers = ref([]);
const paymentTerms = ref([]);
const showDialog = ref(false);
const form = reactive({ id: null, name: null, email: null, phone_number: null, address: null, paymentTerm: null, credit_limit: null, tin: null, website: null });
const formError = reactive({});

const money = (v) => commonService.commaSeparator(v);
const validateField = (v, k) => { formError[k] = commonService.validateFormField(v); };

const getManufacturers = () => commonService.genericRequest('get-manufacturers', 'get', true, {}).then((r) => { if (r.status) manufacturers.value = r.data; });
const getPaymentTerms = () => commonService.genericRequest('get-payment-terms', 'get', true, {}).then((r) => { if (r.status) paymentTerms.value = r.data; });

const openCreate = () => {
    Object.assign(form, { id: null, name: null, email: null, phone_number: null, address: null, paymentTerm: null, credit_limit: null, tin: null, website: null });
    showDialog.value = true;
};
const openEdit = (m) => {
    Object.assign(form, {
        id: m.id, name: m.name, email: m.email, phone_number: m.phone_number, address: m.address,
        credit_limit: m.credit_limit, tin: m.tin, website: m.website,
        paymentTerm: paymentTerms.value.find((t) => t.id === m.payment_term_id) || null
    });
    showDialog.value = true;
};

const onSubmit = () => {
    validateField(form.name, 'name');
    if (commonService.validateRequiredFields(formError)) { commonService.showError(toast, 'Manufacturer name is required.'); return; }
    const postData = {
        id: form.id, name: form.name, email: form.email, phone_number: form.phone_number, website: form.website,
        address: form.address, payment_term_id: form.paymentTerm?.id ?? null,
        credit_limit: Number(form.credit_limit) || 0, tin: form.tin
    };
    const url = form.id ? 'update-manufacturer' : 'create-manufacturer';
    commonService.genericRequest(url, 'post', true, postData).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); showDialog.value = false; getManufacturers(); }
        else commonService.showError(toast, r.message);
    });
};

const openProfile = (m) => router.push('/manufacturer-profile/' + m.id);

onMounted(() => { getPaymentTerms(); getManufacturers(); });
</script>

<template>
    <div>
        <div class="card">
            <div class="flex align-items-center justify-content-between mb-3">
                <h5 class="m-0">Manufacturers</h5>
                <Button label="New Manufacturer" icon="pi pi-plus" @click="openCreate" />
            </div>
            <Toast />
            <DataTable :value="manufacturers" :paginator="true" class="p-datatable-gridlines" :rows="15" dataKey="id" responsiveLayout="scroll">
                <Column field="name" header="Name" />
                <Column header="Contact"><template #body="{ data }">{{ data.email || data.phone_number || '—' }}</template></Column>
                <Column header="Payment Term"><template #body="{ data }">{{ data.payment_term || '—' }}</template></Column>
                <Column header="Rating">
                    <template #body="{ data }">
                        <span v-if="data.rating"><Rating :modelValue="Math.round(data.rating)" readonly :cancel="false" /></span>
                        <span v-else class="text-600">—</span>
                    </template>
                </Column>
                <Column header="Outstanding"><template #body="{ data }">{{ money(data.outstanding) }}</template></Column>
                <Column header="">
                    <template #body="{ data }">
                        <Button icon="pi pi-eye" class="p-button-text p-button-sm" v-tooltip="'Profile'" @click="openProfile(data)" />
                        <Button icon="pi pi-pencil" class="p-button-text p-button-sm p-button-success" v-tooltip="'Edit'" @click="openEdit(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="showDialog" modal :header="form.id ? 'Edit Manufacturer' : 'New Manufacturer'" :style="{ width: '34rem' }">
            <div class="p-fluid formgrid grid">
                <div class="field col-12">
                    <label for="mn">Name</label>
                    <InputText id="mn" v-model="form.name" @blur="validateField(form.name,'name')" :class="{ 'p-invalid': formError.name }" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="me">Email</label>
                    <InputText id="me" v-model="form.email" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="mp">Phone</label>
                    <InputText id="mp" v-model="form.phone_number" />
                </div>
                <div class="field col-12">
                    <label for="ma">Address</label>
                    <InputText id="ma" v-model="form.address" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="mt">Payment Term</label>
                    <Dropdown id="mt" :options="paymentTerms" v-model="form.paymentTerm" optionLabel="name" showClear placeholder="Select term" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="mcl">Credit Limit</label>
                    <InputText id="mcl" v-model="form.credit_limit" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="mtin">TIN</label>
                    <InputText id="mtin" v-model="form.tin" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="mw">Website</label>
                    <InputText id="mw" v-model="form.website" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" class="p-button-text" @click="showDialog = false" />
                <Button :label="form.id ? 'Save' : 'Create'" icon="pi pi-check" @click="onSubmit" />
            </template>
        </Dialog>
    </div>
</template>
