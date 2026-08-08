<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import Rating from 'primevue/rating';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const route = useRoute();
const id = route.params.id;

const supplier = ref(null);
const ratingsSummary = ref({});
const summary = ref({});
const products = ref([]);
const deliveries = ref([]);
const payables = ref([]);
const ratings = ref([]);

const money = (v) => commonService.commaSeparator(v);
const fmt = (d) => (d || '').toString().slice(0, 10) || '—';
const initials = (n) => (n || '?').split(' ').map((x) => x[0]).slice(0, 2).join('').toUpperCase();

const loadProfile = () => commonService.genericRequest('get-supplier-profile', 'post', true, { id }).then((r) => {
    if (r.status) { supplier.value = r.data.supplier; ratingsSummary.value = r.data.ratings_summary || {}; summary.value = r.data.summary || {}; }
    else commonService.showError(toast, r.message);
});
const loadProducts = () => commonService.genericRequest('get-supplier-products', 'post', true, { id }).then((r) => { if (r.status) products.value = r.data; });
const loadDeliveries = () => commonService.genericRequest('get-supplier-deliveries', 'post', true, { id }).then((r) => {
    if (r.status) { deliveries.value = r.data.deliveries || []; payables.value = r.data.payables || []; }
});
const loadRatings = () => commonService.genericRequest('get-supplier-ratings', 'post', true, { supplier_id: id }).then((r) => { if (r.status) ratings.value = r.data; });

// --- contacts ---
const contact = reactive({ name: null, title: null, email: null, phone: null, is_primary: false });
const addContact = () => {
    if (!contact.name) { commonService.showError(toast, 'Contact name is required.'); return; }
    commonService.genericRequest('create-supplier-contact', 'post', true, { supplier_id: id, ...contact }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); Object.assign(contact, { name: null, title: null, email: null, phone: null, is_primary: false }); loadProfile(); }
        else commonService.showError(toast, r.message);
    });
};
const deleteContact = (c) => commonService.genericRequest('delete-supplier-contact', 'post', true, { id: c.id }).then((r) => { if (r.status) loadProfile(); });

// --- ratings entry ---
const rating = reactive({ quality: 0, delivery: 0, price: 0, communication: 0, comment: null });
const submitRating = () => {
    if (!rating.quality || !rating.delivery || !rating.price || !rating.communication) { commonService.showError(toast, 'Rate all four criteria.'); return; }
    commonService.genericRequest('add-supplier-rating', 'post', true, { supplier_id: id, ...rating }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); Object.assign(rating, { quality: 0, delivery: 0, price: 0, communication: 0, comment: null }); loadProfile(); loadRatings(); }
        else commonService.showError(toast, r.message);
    });
};

// --- file helper ---
const toBase64 = (file) => new Promise((resolve) => { const r = new FileReader(); r.onload = (e) => resolve(e.target.result); r.readAsDataURL(file); });

// --- contracts ---
const contract = reactive({ title: null, contract_no: null, start_date: null, end_date: null, value: null, file: null });
const onContractFile = async (e) => { contract.file = await toBase64(e.files[0]); };
const addContract = () => {
    if (!contract.title) { commonService.showError(toast, 'Contract title is required.'); return; }
    commonService.genericRequest('create-supplier-contract', 'post', true, {
        supplier_id: id, title: contract.title, contract_no: contract.contract_no,
        start_date: contract.start_date ? fmt(contract.start_date) : null, end_date: contract.end_date ? fmt(contract.end_date) : null,
        value: Number(contract.value) || 0, file: contract.file
    }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); Object.assign(contract, { title: null, contract_no: null, start_date: null, end_date: null, value: null, file: null }); loadProfile(); }
        else commonService.showError(toast, r.message);
    });
};
const deleteContract = (c) => commonService.genericRequest('delete-supplier-contract', 'post', true, { id: c.id }).then((r) => { if (r.status) loadProfile(); });

// --- documents ---
const document = reactive({ name: null, type: null, expiry_date: null, file: null });
const docTypes = [{ label: 'License', value: 'License' }, { label: 'Certificate', value: 'Certificate' }, { label: 'Insurance', value: 'Insurance' }, { label: 'Other', value: 'Other' }];
const onDocFile = async (e) => { document.file = await toBase64(e.files[0]); };
const addDocument = () => {
    if (!document.name) { commonService.showError(toast, 'Document name is required.'); return; }
    commonService.genericRequest('create-supplier-document', 'post', true, {
        supplier_id: id, name: document.name, type: document.type,
        expiry_date: document.expiry_date ? fmt(document.expiry_date) : null, file: document.file
    }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); Object.assign(document, { name: null, type: null, expiry_date: null, file: null }); loadProfile(); }
        else commonService.showError(toast, r.message);
    });
};
const deleteDocument = (d) => commonService.genericRequest('delete-supplier-document', 'post', true, { id: d.id }).then((r) => { if (r.status) loadProfile(); });

onMounted(() => { loadProfile(); loadProducts(); loadDeliveries(); loadRatings(); });
</script>

<template>
    <div v-if="supplier">
        <Toast />
        <!-- Header -->
        <div class="card">
            <div class="flex flex-column md:flex-row align-items-center gap-4">
                <Avatar :label="initials(supplier.name)" shape="circle" size="xlarge"
                    :style="{ background: 'var(--primary-color)', color: 'var(--primary-color-text)', width: '4.5rem', height: '4.5rem', fontSize: '1.5rem' }" />
                <div class="flex-1 text-center md:text-left">
                    <div class="flex align-items-center justify-content-center md:justify-content-start gap-2 flex-wrap">
                        <h4 class="m-0 text-900">{{ supplier.name }}</h4>
                        <Rating v-if="supplier.rating" :modelValue="Math.round(supplier.rating)" readonly :cancel="false" />
                        <Tag v-if="supplier.payment_term" :value="supplier.payment_term.name" severity="info" />
                    </div>
                    <div class="text-600 mt-1">
                        <span v-if="supplier.email"><i class="pi pi-envelope mr-1" />{{ supplier.email }}</span>
                        <span v-if="supplier.phone_number" class="ml-3"><i class="pi pi-phone mr-1" />{{ supplier.phone_number }}</span>
                        <span v-if="supplier.address" class="ml-3"><i class="pi pi-map-marker mr-1" />{{ supplier.address }}</span>
                    </div>
                </div>
                <div class="flex gap-4 text-center">
                    <div><div class="text-600 text-sm">Products</div><div class="text-900 font-bold text-xl">{{ summary.products }}</div></div>
                    <div><div class="text-600 text-sm">Purchased</div><div class="text-900 font-bold text-xl">{{ money(summary.total_purchased) }}</div></div>
                    <div><div class="text-600 text-sm">Outstanding</div><div class="text-900 font-bold text-xl">{{ money(summary.outstanding) }}</div></div>
                    <div><div class="text-600 text-sm">Credit Limit</div><div class="text-900 font-bold text-xl">{{ money(supplier.credit_limit) }}</div></div>
                </div>
            </div>
        </div>

        <div class="card">
            <TabView>
                <!-- Contacts -->
                <TabPanel header="Contacts">
                    <div class="p-fluid formgrid grid mb-3">
                        <div class="field col-12 md:col-3"><InputText v-model="contact.name" placeholder="Name" /></div>
                        <div class="field col-12 md:col-2"><InputText v-model="contact.title" placeholder="Title" /></div>
                        <div class="field col-12 md:col-3"><InputText v-model="contact.email" placeholder="Email" /></div>
                        <div class="field col-12 md:col-2"><InputText v-model="contact.phone" placeholder="Phone" /></div>
                        <div class="field col-12 md:col-1 flex align-items-center"><Checkbox v-model="contact.is_primary" :binary="true" /> <span class="ml-2 text-sm">Primary</span></div>
                        <div class="field col-12 md:col-1"><Button icon="pi pi-plus" @click="addContact" /></div>
                    </div>
                    <DataTable :value="supplier.contacts" class="p-datatable-gridlines" responsiveLayout="scroll">
                        <Column field="name" header="Name" />
                        <Column field="title" header="Title" />
                        <Column field="email" header="Email" />
                        <Column field="phone" header="Phone" />
                        <Column header="Primary"><template #body="{ data }"><Tag v-if="data.is_primary" value="Primary" severity="success" /></template></Column>
                        <Column header=""><template #body="{ data }"><Button icon="pi pi-trash" class="p-button-text p-button-danger p-button-sm" @click="deleteContact(data)" /></template></Column>
                    </DataTable>
                </TabPanel>

                <!-- Products Supplied -->
                <TabPanel header="Products Supplied">
                    <DataTable :value="products" :paginator="true" :rows="10" class="p-datatable-gridlines" responsiveLayout="scroll">
                        <Column field="name" header="Product" />
                        <Column field="ref_no" header="Ref" />
                        <Column field="category" header="Category" />
                        <Column header="On hand"><template #body="{ data }">{{ money(data.quantity) }}</template></Column>
                        <Column header="Cost"><template #body="{ data }">{{ money(data.purchase_price) }}</template></Column>
                        <Column header="Price"><template #body="{ data }">{{ money(data.selling_price) }}</template></Column>
                    </DataTable>
                </TabPanel>

                <!-- Ratings -->
                <TabPanel header="Ratings">
                    <div class="grid">
                        <div class="col-12 md:col-4">
                            <h6>Add rating</h6>
                            <div class="mb-2 flex align-items-center justify-content-between"><span>Quality</span><Rating v-model="rating.quality" :cancel="false" /></div>
                            <div class="mb-2 flex align-items-center justify-content-between"><span>Delivery</span><Rating v-model="rating.delivery" :cancel="false" /></div>
                            <div class="mb-2 flex align-items-center justify-content-between"><span>Price</span><Rating v-model="rating.price" :cancel="false" /></div>
                            <div class="mb-2 flex align-items-center justify-content-between"><span>Communication</span><Rating v-model="rating.communication" :cancel="false" /></div>
                            <Textarea v-model="rating.comment" rows="2" placeholder="Comment" class="w-full mt-2" />
                            <Button label="Submit rating" icon="pi pi-star" class="mt-2" @click="submitRating" />
                        </div>
                        <div class="col-12 md:col-8">
                            <h6>Averages ({{ ratingsSummary.c || 0 }} ratings)</h6>
                            <div class="grid text-center mb-3">
                                <div class="col"><div class="text-600 text-sm">Quality</div><div class="text-900 font-bold">{{ Number(ratingsSummary.quality || 0).toFixed(1) }}</div></div>
                                <div class="col"><div class="text-600 text-sm">Delivery</div><div class="text-900 font-bold">{{ Number(ratingsSummary.delivery || 0).toFixed(1) }}</div></div>
                                <div class="col"><div class="text-600 text-sm">Price</div><div class="text-900 font-bold">{{ Number(ratingsSummary.price || 0).toFixed(1) }}</div></div>
                                <div class="col"><div class="text-600 text-sm">Comm.</div><div class="text-900 font-bold">{{ Number(ratingsSummary.communication || 0).toFixed(1) }}</div></div>
                                <div class="col"><div class="text-600 text-sm">Overall</div><div class="text-900 font-bold">{{ Number(ratingsSummary.overall || 0).toFixed(2) }}</div></div>
                            </div>
                            <DataTable :value="ratings" :paginator="true" :rows="8" class="p-datatable-gridlines" responsiveLayout="scroll">
                                <Column header="Overall"><template #body="{ data }"><Rating :modelValue="Math.round(data.overall)" readonly :cancel="false" /></template></Column>
                                <Column field="comment" header="Comment" />
                                <Column field="rated_by_name" header="By" />
                                <Column header="Date"><template #body="{ data }">{{ fmt(data.rating_date) }}</template></Column>
                            </DataTable>
                        </div>
                    </div>
                </TabPanel>

                <!-- Delivery History -->
                <TabPanel header="Delivery History">
                    <h6>Goods received</h6>
                    <DataTable :value="deliveries" :paginator="true" :rows="8" class="p-datatable-gridlines mb-4" responsiveLayout="scroll">
                        <Column header="Date"><template #body="{ data }">{{ fmt(data.stock_date) }}</template></Column>
                        <Column field="product" header="Product" />
                        <Column header="Qty"><template #body="{ data }">{{ money(data.quantity) }}</template></Column>
                        <Column header="Unit Cost"><template #body="{ data }">{{ money(data.purchase_price) }}</template></Column>
                        <Column header="Cost"><template #body="{ data }">{{ money(data.cost) }}</template></Column>
                    </DataTable>
                    <h6>Payables</h6>
                    <DataTable :value="payables" :paginator="true" :rows="8" class="p-datatable-gridlines" responsiveLayout="scroll">
                        <Column field="ref_no" header="Ref" />
                        <Column header="Amount"><template #body="{ data }">{{ money(data.amount) }}</template></Column>
                        <Column header="Paid"><template #body="{ data }">{{ money(data.amount_paid) }}</template></Column>
                        <Column header="Outstanding"><template #body="{ data }">{{ money(data.outstanding) }}</template></Column>
                        <Column header="Status"><template #body="{ data }"><Tag :value="data.status" :severity="data.status === 'Active' ? 'warning' : 'success'" /></template></Column>
                    </DataTable>
                </TabPanel>

                <!-- Contracts -->
                <TabPanel header="Contracts">
                    <div class="p-fluid formgrid grid mb-3">
                        <div class="field col-12 md:col-3"><InputText v-model="contract.title" placeholder="Title" /></div>
                        <div class="field col-12 md:col-2"><InputText v-model="contract.contract_no" placeholder="Contract No" /></div>
                        <div class="field col-12 md:col-2"><Calendar v-model="contract.start_date" dateFormat="yy-mm-dd" placeholder="Start" /></div>
                        <div class="field col-12 md:col-2"><Calendar v-model="contract.end_date" dateFormat="yy-mm-dd" placeholder="End" /></div>
                        <div class="field col-12 md:col-2"><InputText v-model="contract.value" placeholder="Value" /></div>
                        <div class="field col-12 md:col-1"><Button icon="pi pi-check" @click="addContract" /></div>
                        <div class="field col-12"><FileUpload mode="basic" :auto="true" chooseLabel="Attach file" customUpload @uploader="onContractFile" /></div>
                    </div>
                    <DataTable :value="supplier.contracts" class="p-datatable-gridlines" responsiveLayout="scroll">
                        <Column field="title" header="Title" />
                        <Column field="contract_no" header="No" />
                        <Column header="Start"><template #body="{ data }">{{ fmt(data.start_date) }}</template></Column>
                        <Column header="End"><template #body="{ data }">{{ fmt(data.end_date) }}</template></Column>
                        <Column header="Value"><template #body="{ data }">{{ money(data.value) }}</template></Column>
                        <Column field="status" header="Status" />
                        <Column header="File"><template #body="{ data }"><a v-if="data.file_path" :href="data.file_path" target="_blank"><i class="pi pi-download" /></a><span v-else>—</span></template></Column>
                        <Column header=""><template #body="{ data }"><Button icon="pi pi-trash" class="p-button-text p-button-danger p-button-sm" @click="deleteContract(data)" /></template></Column>
                    </DataTable>
                </TabPanel>

                <!-- Documents -->
                <TabPanel header="Documents">
                    <div class="p-fluid formgrid grid mb-3">
                        <div class="field col-12 md:col-4"><InputText v-model="document.name" placeholder="Document name" /></div>
                        <div class="field col-12 md:col-3"><Dropdown :options="docTypes" v-model="document.type" optionLabel="label" optionValue="value" placeholder="Type" /></div>
                        <div class="field col-12 md:col-3"><Calendar v-model="document.expiry_date" dateFormat="yy-mm-dd" placeholder="Expiry" /></div>
                        <div class="field col-12 md:col-2"><Button icon="pi pi-check" label="Add" @click="addDocument" /></div>
                        <div class="field col-12"><FileUpload mode="basic" :auto="true" chooseLabel="Attach file" customUpload @uploader="onDocFile" /></div>
                    </div>
                    <DataTable :value="supplier.documents" class="p-datatable-gridlines" responsiveLayout="scroll">
                        <Column field="name" header="Name" />
                        <Column field="type" header="Type" />
                        <Column header="Expiry"><template #body="{ data }">{{ fmt(data.expiry_date) }}</template></Column>
                        <Column header="File"><template #body="{ data }"><a v-if="data.file_path" :href="data.file_path" target="_blank"><i class="pi pi-download" /></a><span v-else>—</span></template></Column>
                        <Column header=""><template #body="{ data }"><Button icon="pi pi-trash" class="p-button-text p-button-danger p-button-sm" @click="deleteDocument(data)" /></template></Column>
                    </DataTable>
                </TabPanel>
            </TabView>
        </div>
    </div>
</template>
