<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Dialog from 'primevue/dialog';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();

const customers = ref([]);
const groups = ref([]);
const paymentTerms = ref([]);
const showDialog = ref(false);
const form = reactive({ id: null, name: null, email: null, phone_number: null, address: null, group: null, credit_limit: null, discount_percent: null, paymentTerm: null, tin: null });
const formError = reactive({});

const money = (v) => commonService.commaSeparator(v);
const validateField = (v, k) => { formError[k] = commonService.validateFormField(v); };

const getCustomers = () => commonService.genericRequest('get-customers', 'post', true, {}).then((r) => { if (r.status) customers.value = r.data; });
const getGroups = () => commonService.genericRequest('get-customer-groups', 'get', true, {}).then((r) => { if (r.status) groups.value = r.data; });
const getPaymentTerms = () => commonService.genericRequest('get-payment-terms', 'get', true, {}).then((r) => { if (r.status) paymentTerms.value = r.data; });

const openCreate = () => {
    Object.assign(form, { id: null, name: null, email: null, phone_number: null, address: null, group: null, credit_limit: null, discount_percent: null, paymentTerm: null, tin: null });
    showDialog.value = true;
};
const openEdit = (c) => {
    Object.assign(form, {
        id: c.id, name: c.name, email: c.email, phone_number: c.phone_number, address: c.address,
        credit_limit: c.credit_limit, discount_percent: c.discount_percent, tin: c.tin,
        group: groups.value.find((g) => g.id === c.customer_group_id) || null,
        paymentTerm: paymentTerms.value.find((t) => t.id === c.payment_term_id) || null
    });
    showDialog.value = true;
};

const onSubmit = () => {
    validateField(form.name, 'name');
    if (commonService.validateRequiredFields(formError)) { commonService.showError(toast, 'Customer name is required.'); return; }
    const postData = {
        id: form.id, name: form.name, email: form.email, phone_number: form.phone_number, address: form.address,
        customer_group_id: form.group?.id ?? null, credit_limit: Number(form.credit_limit) || 0,
        discount_percent: form.discount_percent === null || form.discount_percent === '' ? null : Number(form.discount_percent),
        payment_term_id: form.paymentTerm?.id ?? null, tin: form.tin
    };
    const url = form.id ? 'update-customer' : 'create-customer';
    commonService.genericRequest(url, 'post', true, postData).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); showDialog.value = false; getCustomers(); }
        else commonService.showError(toast, r.message);
    });
};

const openProfile = (c) => router.push('/customer-profile/' + c.id);

onMounted(() => { getGroups(); getPaymentTerms(); getCustomers(); });
</script>

<template>
    <div>
        <div class="card">
            <div class="flex align-items-center justify-content-between mb-3">
                <h5 class="m-0">Customers</h5>
                <Button label="New Customer" icon="pi pi-plus" @click="openCreate" />
            </div>
            <Toast />
            <DataTable :value="customers" :paginator="true" class="p-datatable-gridlines" :rows="15" dataKey="id" responsiveLayout="scroll">
                <Column field="name" header="Name" />
                <Column header="Contact"><template #body="{ data }">{{ data.email || data.phone_number || '—' }}</template></Column>
                <Column header="Group"><template #body="{ data }">{{ data.group_name || '—' }}</template></Column>
                <Column header="Discount %"><template #body="{ data }">{{ data.effective_discount ?? 0 }}%</template></Column>
                <Column header="Credit Limit"><template #body="{ data }">{{ money(data.credit_limit) }}</template></Column>
                <Column header="Outstanding"><template #body="{ data }">{{ money(data.outstanding) }}</template></Column>
                <Column header="">
                    <template #body="{ data }">
                        <Button icon="pi pi-eye" class="p-button-text p-button-sm" v-tooltip="'Profile'" @click="openProfile(data)" />
                        <Button icon="pi pi-pencil" class="p-button-text p-button-sm p-button-success" v-tooltip="'Edit'" @click="openEdit(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="showDialog" modal :header="form.id ? 'Edit Customer' : 'New Customer'" :style="{ width: '34rem' }">
            <div class="p-fluid formgrid grid">
                <div class="field col-12">
                    <label for="cn">Name</label>
                    <InputText id="cn" v-model="form.name" @blur="validateField(form.name,'name')" :class="{ 'p-invalid': formError.name }" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="ce">Email</label>
                    <InputText id="ce" v-model="form.email" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="cp">Phone</label>
                    <InputText id="cp" v-model="form.phone_number" />
                </div>
                <div class="field col-12">
                    <label for="ca">Address</label>
                    <InputText id="ca" v-model="form.address" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="cg">Customer Group</label>
                    <Dropdown id="cg" :options="groups" v-model="form.group" optionLabel="name" showClear placeholder="Select group" />
                </div>
                <div class="field col-12 md:col-6">
                    <label for="ct">Payment Term</label>
                    <Dropdown id="ct" :options="paymentTerms" v-model="form.paymentTerm" optionLabel="name" showClear placeholder="Select term" />
                </div>
                <div class="field col-12 md:col-4">
                    <label for="ccl">Credit Limit</label>
                    <InputText id="ccl" v-model="form.credit_limit" />
                </div>
                <div class="field col-12 md:col-4">
                    <label for="cd">Discount % (override)</label>
                    <InputText id="cd" v-model="form.discount_percent" placeholder="inherits group" />
                </div>
                <div class="field col-12 md:col-4">
                    <label for="ctin">TIN</label>
                    <InputText id="ctin" v-model="form.tin" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" class="p-button-text" @click="showDialog = false" />
                <Button :label="form.id ? 'Save' : 'Create'" icon="pi pi-check" @click="onSubmit" />
            </template>
        </Dialog>
    </div>
</template>
