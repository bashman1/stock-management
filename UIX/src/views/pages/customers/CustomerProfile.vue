<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import Dialog from 'primevue/dialog';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const route = useRoute();
const id = route.params.id;

const customer = ref(null);
const summary = ref({});
const effectiveDiscount = ref(0);
const sales = ref([]);
const receivables = ref([]);
const prices = ref([]);
const products = ref([]);

const money = (v) => commonService.commaSeparator(v);
const fmt = (d) => (d || '').toString().slice(0, 10) || '—';
const initials = (n) => (n || '?').split(' ').map((x) => x[0]).slice(0, 2).join('').toUpperCase();

const loadProfile = () => commonService.genericRequest('get-customer-profile', 'post', true, { id }).then((r) => {
    if (r.status) { customer.value = r.data.customer; summary.value = r.data.summary || {}; effectiveDiscount.value = r.data.effective_discount ?? 0; }
    else commonService.showError(toast, r.message);
});
const loadSales = () => commonService.genericRequest('get-customer-sales', 'post', true, { id }).then((r) => { if (r.status) sales.value = r.data; });
const loadReceivables = () => commonService.genericRequest('get-customer-receivables', 'post', true, { id }).then((r) => { if (r.status) receivables.value = r.data; });
const loadPrices = () => commonService.genericRequest('get-customer-prices', 'post', true, { customer_id: id }).then((r) => { if (r.status) prices.value = r.data; });
const loadProducts = () => commonService.genericRequest('get-products', 'post', true, { page: 1, rowPerPage: 1000 }).then((r) => { if (r.status) products.value = r.data?.data ?? r.data ?? []; });

// --- record payment ---
const showPay = ref(false);
const pay = reactive({ receivable: null, amount: null });
const openPay = (r) => { pay.receivable = r; pay.amount = r.outstanding; showPay.value = true; };
const submitPay = () => {
    if (!(Number(pay.amount) > 0)) { commonService.showError(toast, 'Enter an amount.'); return; }
    commonService.genericRequest('pay-receivable', 'post', true, { receivable_id: pay.receivable.id, amount_paid: Number(pay.amount) }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); showPay.value = false; loadReceivables(); loadProfile(); }
        else commonService.showError(toast, r.message);
    });
};

// --- custom pricing ---
const priceForm = reactive({ product: null, price: null });
const addPrice = () => {
    if (!priceForm.product || priceForm.price === null || priceForm.price === '') { commonService.showError(toast, 'Pick a product and price.'); return; }
    commonService.genericRequest('create-customer-price', 'post', true, { customer_id: id, product_id: priceForm.product.id, price: Number(priceForm.price) }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); priceForm.product = null; priceForm.price = null; loadPrices(); }
        else commonService.showError(toast, r.message);
    });
};
const deletePrice = (p) => commonService.genericRequest('delete-customer-price', 'post', true, { id: p.id }).then((r) => { if (r.status) loadPrices(); });

onMounted(() => { loadProfile(); loadSales(); loadReceivables(); loadPrices(); loadProducts(); });
</script>

<template>
    <div v-if="customer">
        <Toast />
        <div class="card">
            <div class="flex flex-column md:flex-row align-items-center gap-4">
                <Avatar :label="initials(customer.name)" shape="circle" size="xlarge"
                    :style="{ background: 'var(--primary-color)', color: 'var(--primary-color-text)', width: '4.5rem', height: '4.5rem', fontSize: '1.5rem' }" />
                <div class="flex-1 text-center md:text-left">
                    <div class="flex align-items-center justify-content-center md:justify-content-start gap-2 flex-wrap">
                        <h4 class="m-0 text-900">{{ customer.name }}</h4>
                        <Tag v-if="customer.group" :value="customer.group.name" severity="info" />
                        <Tag :value="'Discount ' + effectiveDiscount + '%'" />
                    </div>
                    <div class="text-600 mt-1">
                        <span v-if="customer.email"><i class="pi pi-envelope mr-1" />{{ customer.email }}</span>
                        <span v-if="customer.phone_number" class="ml-3"><i class="pi pi-phone mr-1" />{{ customer.phone_number }}</span>
                        <span v-if="customer.address" class="ml-3"><i class="pi pi-map-marker mr-1" />{{ customer.address }}</span>
                    </div>
                </div>
                <div class="flex gap-4 text-center">
                    <div><div class="text-600 text-sm">Total Sales</div><div class="text-900 font-bold text-xl">{{ money(summary.total_sales) }}</div></div>
                    <div><div class="text-600 text-sm">Orders</div><div class="text-900 font-bold text-xl">{{ summary.orders }}</div></div>
                    <div><div class="text-600 text-sm">Outstanding</div><div class="text-900 font-bold text-xl">{{ money(summary.outstanding) }}</div></div>
                    <div><div class="text-600 text-sm">Credit Available</div><div class="text-900 font-bold text-xl">{{ money(summary.credit_available) }}</div></div>
                </div>
            </div>
        </div>

        <div class="card">
            <TabView>
                <!-- Overview -->
                <TabPanel header="Overview">
                    <div class="grid">
                        <div class="col-12 md:col-6"><b>Credit limit:</b> {{ money(customer.credit_limit) }}</div>
                        <div class="col-12 md:col-6"><b>Effective discount:</b> {{ effectiveDiscount }}%</div>
                        <div class="col-12 md:col-6"><b>Group:</b> {{ customer.group?.name || '—' }}</div>
                        <div class="col-12 md:col-6"><b>Payment term:</b> {{ customer.payment_term?.name || '—' }}</div>
                        <div class="col-12 md:col-6"><b>TIN:</b> {{ customer.tin || '—' }}</div>
                        <div class="col-12"><b>Description:</b> {{ customer.description || '—' }}</div>
                    </div>
                </TabPanel>

                <!-- Sales History -->
                <TabPanel header="Sales History">
                    <DataTable :value="sales" :paginator="true" :rows="10" class="p-datatable-gridlines" responsiveLayout="scroll">
                        <Column field="ref_no" header="Ref" />
                        <Column header="Date"><template #body="{ data }">{{ fmt(data.tran_date || data.created_on) }}</template></Column>
                        <Column header="Total"><template #body="{ data }">{{ money(data.total) }}</template></Column>
                        <Column header="Discount"><template #body="{ data }">{{ money(data.discount) }}</template></Column>
                        <Column header="Paid"><template #body="{ data }">{{ money(data.amount_paid) }}</template></Column>
                        <Column field="payment_method" header="Method" />
                    </DataTable>
                </TabPanel>

                <!-- Outstanding -->
                <TabPanel header="Outstanding">
                    <DataTable :value="receivables" :paginator="true" :rows="10" class="p-datatable-gridlines" responsiveLayout="scroll">
                        <Column field="ref_no" header="Ref" />
                        <Column header="Date"><template #body="{ data }">{{ fmt(data.created_on) }}</template></Column>
                        <Column header="Amount"><template #body="{ data }">{{ money(data.amount) }}</template></Column>
                        <Column header="Paid"><template #body="{ data }">{{ money(data.amount_paid) }}</template></Column>
                        <Column header="Outstanding"><template #body="{ data }">{{ money(data.outstanding) }}</template></Column>
                        <Column header="Status"><template #body="{ data }"><Tag :value="data.status" :severity="data.status === 'Active' ? 'warning' : 'success'" /></template></Column>
                        <Column header="">
                            <template #body="{ data }">
                                <Button v-if="data.status === 'Active' && data.outstanding > 0" label="Record payment" class="p-button-sm p-button-outlined" @click="openPay(data)" />
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>

                <!-- Custom Pricing -->
                <TabPanel header="Custom Pricing">
                    <div class="p-fluid formgrid grid mb-3">
                        <div class="field col-12 md:col-6"><Dropdown :options="products" v-model="priceForm.product" filter optionLabel="name" placeholder="Select product" /></div>
                        <div class="field col-12 md:col-4"><InputText v-model="priceForm.price" placeholder="Custom price" /></div>
                        <div class="field col-12 md:col-2"><Button icon="pi pi-check" label="Add" @click="addPrice" /></div>
                    </div>
                    <DataTable :value="prices" class="p-datatable-gridlines" responsiveLayout="scroll">
                        <Column field="product_name" header="Product" />
                        <Column field="ref_no" header="Ref" />
                        <Column header="Custom Price"><template #body="{ data }">{{ money(data.price) }}</template></Column>
                        <Column header=""><template #body="{ data }"><Button icon="pi pi-trash" class="p-button-text p-button-danger p-button-sm" @click="deletePrice(data)" /></template></Column>
                    </DataTable>
                    <small class="text-600">Customer-specific prices override the group price and the normal selling price at the POS.</small>
                </TabPanel>
            </TabView>
        </div>

        <Dialog v-model:visible="showPay" modal header="Record Payment" :style="{ width: '24rem' }">
            <div class="p-fluid" v-if="pay.receivable">
                <p class="mb-3">Outstanding on <b>{{ pay.receivable.ref_no }}</b>: {{ money(pay.receivable.outstanding) }}</p>
                <div class="field">
                    <label for="pa">Amount</label>
                    <InputText id="pa" v-model="pay.amount" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" class="p-button-text" @click="showPay = false" />
                <Button label="Pay" icon="pi pi-check" @click="submitPay" />
            </template>
        </Dialog>
    </div>
</template>
