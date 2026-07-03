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

const suppliers = ref([]);
const paymentTerms = ref([]);
const showDialog = ref(false);
const form = reactive({ id: null, name: null, email: null, phone_number: null, address: null, paymentTerm: null, credit_limit: null, tin: null, website: null });
const formError = reactive({});

const money = (v) => commonService.commaSeparator(v);
const validateField = (v, k) => { formError[k] = commonService.validateFormField(v); };

const getSuppliers = () => commonService.genericRequest('get-suppliers', 'get', true, {}).then((r) => { if (r.status) suppliers.value = r.data; });
const getPaymentTerms = () => commonService.genericRequest('get-payment-terms', 'get', true, {}).then((r) => { if (r.status) paymentTerms.value = r.data; });

const openCreate = () => {
    Object.assign(form, { id: null, name: null, email: null, phone_number: null, address: null, paymentTerm: null, credit_limit: null, tin: null, website: null });
    showDialog.value = true;
};
const openEdit = (s) => {
    Object.assign(form, {
        id: s.id, name: s.name, email: s.email, phone_number: s.phone_number, address: s.address,
        credit_limit: s.credit_limit, tin: s.tin, website: s.website,
        paymentTerm: paymentTerms.value.find((t) => t.id === s.payment_term_id) || null
    });
    showDialog.value = true;
};

const onSubmit = () => {
    validateField(form.name, 'name');
    if (commonService.validateRequiredFields(formError)) { commonService.showError(toast, 'Supplier name is required.'); return; }
    const postData = {
        id: form.id, name: form.name, email: form.email, phone_number: form.phone_number, website: form.website,
        address: form.address, payment_term_id: form.paymentTerm?.id ?? null,
        credit_limit: Number(form.credit_limit) || 0, tin: form.tin
    };
    const url = form.id ? 'update-supplier' : 'create-supplier';
    commonService.genericRequest(url, 'post', true, postData).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); showDialog.value = false; getSuppliers(); }
        else commonService.showError(toast, r.message);
    });
};

const openProfile = (s) => router.push('/supplier-profile/' + s.id);

onMounted(() => { getPaymentTerms(); getSuppliers(); });
</script>

<template>
    <div>
        <div class="card">
            <div class="flex align-items-center justify-content-between mb-3">
                <h5 class="m-0">Suppliers</h5>
                <Button label="New Supplier" icon="pi pi-plus" @click="openCreate" />
            </div>
            <Toast />
            <DataTable :value="suppliers" :paginator="true" class="p-datatable-gridlines" :rows="15" dataKey="id" responsiveLayout="scroll">
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

        <Dialog v-model:visible="showDialog" modal :header="form.id ? 'Edit Supplier' : 'New Supplier'" :style="{ width: '34rem' }">
            <div class="p-fluid formgrid grid">
                <div class="field col-12">
                    <label for="sn">Name</label>
                    <InputText id="sn" v-model="form.name" @blur="validateField(form.name,'name')" :class="{ 'p-invalid': formError.name }" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="se">Email</label>
                    <InputText id="se" v-model="form.email" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="sp">Phone</label>
                    <InputText id="sp" v-model="form.phone_number" />
                </div>
                <div class="field col-12">
                    <label for="sa">Address</label>
                    <InputText id="sa" v-model="form.address" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="st">Payment Term</label>
                    <Dropdown id="st" :options="paymentTerms" v-model="form.paymentTerm" optionLabel="name" showClear placeholder="Select term" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="scl">Credit Limit</label>
                    <InputText id="scl" v-model="form.credit_limit" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="stin">TIN</label>
                    <InputText id="stin" v-model="form.tin" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="sw">Website</label>
                    <InputText id="sw" v-model="form.website" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" class="p-button-text" @click="showDialog = false" />
                <Button :label="form.id ? 'Save' : 'Create'" icon="pi pi-check" @click="onSubmit" />
            </template>
        </Dialog>
    </div>
</template>
