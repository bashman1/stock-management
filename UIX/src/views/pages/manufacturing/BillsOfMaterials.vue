<script setup>
import { ref, reactive, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const boms = ref([]);
const products = ref([]);
const form = reactive({ product: null, name: null, output_quantity: 1 });
const items = ref([{ material: null, quantity: null, scrap_percent: 0 }]);
const formError = reactive({});

const validateField = (v, k) => { formError[k] = commonService.validateFormField(v); };

const getProducts = () => commonService.genericRequest('get-products', 'post', true, { page: 1, rowPerPage: 1000 }).then((r) => {
    if (r.status) products.value = r.data?.data ?? r.data ?? [];
});
const getBoms = () => commonService.genericRequest('get-boms', 'post', true, {}).then((r) => { if (r.status) boms.value = r.data; });

const addItem = () => items.value.push({ material: null, quantity: null, scrap_percent: 0 });
const removeItem = (i) => { if (items.value.length > 1) items.value.splice(i, 1); };

const onSubmit = () => {
    validateField(form.product, 'product');
    if (commonService.validateRequiredFields(formError)) { commonService.showError(toast, 'Select the finished product.'); return; }
    const rows = items.value.filter((r) => r.material && Number(r.quantity) > 0);
    if (!rows.length) { commonService.showError(toast, 'Add at least one material with a quantity.'); return; }
    const postData = {
        product_id: form.product?.id,
        name: form.name,
        output_quantity: Number(form.output_quantity) || 1,
        items: rows.map((r) => ({ material_product_id: r.material.id, quantity: Number(r.quantity), scrap_percent: Number(r.scrap_percent) || 0 }))
    };
    commonService.genericRequest('create-bom', 'post', true, postData).then((r) => {
        if (r.status) {
            commonService.showSuccess(toast, r.message);
            form.product = null; form.name = null; form.output_quantity = 1;
            items.value = [{ material: null, quantity: null, scrap_percent: 0 }];
            getBoms();
        } else commonService.showError(toast, r.message);
    });
};

onMounted(() => { getProducts(); getBoms(); });
</script>

<template>
    <div>
        <div class="card">
            <h5>New Bill of Materials</h5>
            <Toast />
            <div class="grid p-fluid mt-2">
                <div class="field col-12 md:col-5">
                    <span class="p-float-label">
                        <Dropdown id="prod" :options="products" v-model="form.product" filter optionLabel="name"
                            @blur="validateField(form.product,'product')" :class="{ 'p-invalid': formError.product }" />
                        <label for="prod">Finished Product</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText id="bname" v-model="form.name" />
                        <label for="bname">BOM Name (optional)</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText id="oq" v-model="form.output_quantity" />
                        <label for="oq">Output Qty / batch</label>
                    </span>
                </div>
            </div>

            <h6>Materials</h6>
            <table class="w-full" style="border-collapse: collapse">
                <thead>
                    <tr class="text-left">
                        <th class="p-2" style="width: 50%">Material</th>
                        <th class="p-2" style="width: 22%">Quantity</th>
                        <th class="p-2" style="width: 20%">Scrap %</th>
                        <th class="p-2" style="width: 8%"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, i) in items" :key="i">
                        <td class="p-1"><Dropdown :options="products" v-model="row.material" filter optionLabel="name" placeholder="Select material" class="w-full" /></td>
                        <td class="p-1"><InputText v-model="row.quantity" placeholder="Qty per batch" class="w-full" /></td>
                        <td class="p-1"><InputText v-model="row.scrap_percent" placeholder="0" class="w-full" /></td>
                        <td class="p-1 text-center"><Button icon="pi pi-trash" class="p-button-text p-button-danger" @click="removeItem(i)" :disabled="items.length <= 1" /></td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-2 flex justify-content-between">
                <Button label="Add Material" icon="pi pi-plus" class="p-button-text" @click="addItem" />
                <Button label="Save BOM" icon="pi pi-check" class="p-button-outlined" @click="onSubmit" />
            </div>
        </div>

        <div class="card">
            <h5>Bills of Materials</h5>
            <DataTable :value="boms" :paginator="true" class="p-datatable-gridlines" :rows="15" dataKey="id" responsiveLayout="scroll">
                <Column field="product_name" header="Finished Product" />
                <Column field="name" header="Name" />
                <Column header="Output Qty"><template #body="{ data }">{{ data.output_quantity }}</template></Column>
                <Column header="Materials"><template #body="{ data }">{{ data.item_count }}</template></Column>
                <Column header="Items">
                    <template #body="{ data }">
                        <span v-for="(it, idx) in data.items" :key="idx" class="mr-2 text-600">
                            {{ it.material?.name }} ×{{ it.quantity }}<span v-if="idx < data.items.length - 1">,</span>
                        </span>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>
