<script setup>
import { ref, reactive, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const groups = ref([]);
const products = ref([]);
const form = reactive({ id: null, name: null, discount_percent: null, description: null });
const selectedGroup = ref(null);
const groupPrices = ref([]);
const priceForm = reactive({ product: null, price: null });

const money = (v) => commonService.commaSeparator(v);

const getGroups = () => commonService.genericRequest('get-customer-groups', 'get', true, {}).then((r) => { if (r.status) groups.value = r.data; });
const getProducts = () => commonService.genericRequest('get-products', 'post', true, { page: 1, rowPerPage: 1000 }).then((r) => { if (r.status) products.value = r.data?.data ?? r.data ?? []; });

const resetForm = () => { form.id = null; form.name = null; form.discount_percent = null; form.description = null; };
const editGroup = (g) => { form.id = g.id; form.name = g.name; form.discount_percent = g.discount_percent; form.description = g.description; };

const saveGroup = () => {
    if (!form.name) { commonService.showError(toast, 'Group name is required.'); return; }
    commonService.genericRequest('create-customer-group', 'post', true, {
        id: form.id, name: form.name, discount_percent: Number(form.discount_percent) || 0, description: form.description
    }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); resetForm(); getGroups(); }
        else commonService.showError(toast, r.message);
    });
};

const selectGroup = (g) => { selectedGroup.value = g; loadGroupPrices(); };
const loadGroupPrices = () => {
    if (!selectedGroup.value) return;
    commonService.genericRequest('get-customer-prices', 'post', true, { customer_group_id: selectedGroup.value.id }).then((r) => { if (r.status) groupPrices.value = r.data; });
};
const addGroupPrice = () => {
    if (!selectedGroup.value) { commonService.showError(toast, 'Select a group first.'); return; }
    if (!priceForm.product || priceForm.price === null || priceForm.price === '') { commonService.showError(toast, 'Pick a product and price.'); return; }
    commonService.genericRequest('create-customer-price', 'post', true, { customer_group_id: selectedGroup.value.id, product_id: priceForm.product.id, price: Number(priceForm.price) }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); priceForm.product = null; priceForm.price = null; loadGroupPrices(); }
        else commonService.showError(toast, r.message);
    });
};
const deleteGroupPrice = (p) => commonService.genericRequest('delete-customer-price', 'post', true, { id: p.id }).then((r) => { if (r.status) loadGroupPrices(); });

onMounted(() => { getGroups(); getProducts(); });
</script>

<template>
    <div class="grid">
        <Toast />
        <div class="col-12 lg:col-5">
            <div class="card">
                <h5>{{ form.id ? 'Edit Group' : 'New Customer Group' }}</h5>
                <div class="p-fluid formgrid grid">
                    <div class="field col-12 md:col-7"><label for="gn">Name</label><InputText id="gn" v-model="form.name" /></div>
                    <div class="field col-12 md:col-5"><label for="gd">Default Discount %</label><InputText id="gd" v-model="form.discount_percent" /></div>
                    <div class="field col-12"><label for="gde">Description</label><InputText id="gde" v-model="form.description" /></div>
                    <div class="col-12 flex gap-2">
                        <Button :label="form.id ? 'Update' : 'Create'" icon="pi pi-check" class="p-button-outlined" @click="saveGroup" />
                        <Button v-if="form.id" label="Cancel" class="p-button-text" @click="resetForm" />
                    </div>
                </div>
            </div>
            <div class="card">
                <h5>Groups</h5>
                <DataTable :value="groups" class="p-datatable-gridlines" responsiveLayout="scroll" selectionMode="single"
                    v-model:selection="selectedGroup" @rowSelect="loadGroupPrices" dataKey="id">
                    <Column field="name" header="Name" />
                    <Column header="Discount %"><template #body="{ data }">{{ data.discount_percent }}%</template></Column>
                    <Column header="">
                        <template #body="{ data }">
                            <Button icon="pi pi-tags" class="p-button-text p-button-sm" v-tooltip="'Price list'" @click="selectGroup(data)" />
                            <Button icon="pi pi-pencil" class="p-button-text p-button-sm p-button-success" @click="editGroup(data)" />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <div class="col-12 lg:col-7">
            <div class="card">
                <h5>Group Price List <span v-if="selectedGroup" class="text-primary">— {{ selectedGroup.name }}</span></h5>
                <p v-if="!selectedGroup" class="text-600">Select a group (price-tag icon) to manage its product prices.</p>
                <template v-else>
                    <div class="p-fluid formgrid grid mb-3">
                        <div class="field col-12 md:col-6"><Dropdown :options="products" v-model="priceForm.product" filter optionLabel="name" placeholder="Select product" /></div>
                        <div class="field col-12 md:col-4"><InputText v-model="priceForm.price" placeholder="Group price" /></div>
                        <div class="field col-12 md:col-2"><Button icon="pi pi-check" label="Add" @click="addGroupPrice" /></div>
                    </div>
                    <DataTable :value="groupPrices" class="p-datatable-gridlines" responsiveLayout="scroll">
                        <Column field="product_name" header="Product" />
                        <Column header="Price"><template #body="{ data }">{{ money(data.price) }}</template></Column>
                        <Column header=""><template #body="{ data }"><Button icon="pi pi-trash" class="p-button-text p-button-danger p-button-sm" @click="deleteGroupPrice(data)" /></template></Column>
                    </DataTable>
                </template>
            </div>
        </div>
    </div>
</template>
