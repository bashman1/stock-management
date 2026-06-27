<script setup>
import { ref, reactive, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const warehouses = ref([]);
const selectedWarehouse = ref(null);
const tree = ref([]);
const flat = ref([]);
const typeOptions = [{ label: 'Rack', value: 'RACK' }, { label: 'Shelf', value: 'SHELF' }, { label: 'Bin', value: 'BIN' }];
const form = reactive({ type: 'RACK', name: null, code: null, parent: null, capacity: null });
const formError = reactive({});

const validateField = (v, k) => { formError[k] = commonService.validateFormField(v); };

const getWarehouses = () => commonService.genericRequest('get-warehouses', 'post', true, {}).then((r) => { if (r.status) warehouses.value = r.data; });

const loadLocations = () => {
    if (!selectedWarehouse.value) return;
    commonService.genericRequest('get-warehouse-locations', 'post', true, { warehouse_id: selectedWarehouse.value.id }).then((r) => {
        if (r.status) {
            tree.value = r.data?.tree ?? [];
            // precompute a label so the parent dropdown uses a string optionLabel
            flat.value = (r.data?.flat ?? []).map((f) => ({ ...f, label: `${f.type}: ${f.name}` }));
        }
    });
};

const onSubmit = () => {
    validateField(form.name, 'name');
    if (!selectedWarehouse.value) { commonService.showError(toast, 'Select a warehouse first.'); return; }
    if (commonService.validateRequiredFields(formError)) { commonService.showError(toast, 'Location name is required.'); return; }
    const postData = {
        warehouse_id: selectedWarehouse.value.id, type: form.type, name: form.name,
        code: form.code, parent_id: form.parent?.id ?? null, capacity: form.capacity
    };
    commonService.genericRequest('create-warehouse-location', 'post', true, postData).then((r) => {
        if (r.status) {
            commonService.showSuccess(toast, r.message);
            form.name = null; form.code = null; form.parent = null; form.capacity = null;
            loadLocations();
        } else commonService.showError(toast, r.message);
    });
};

onMounted(() => getWarehouses());
</script>

<template>
    <div>
        <div class="card">
            <h5>Warehouse Locations (Rack / Shelf / Bin)</h5>
            <Toast />
            <div class="grid p-fluid mt-2">
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="wh" :options="warehouses" v-model="selectedWarehouse" optionLabel="name" @change="loadLocations" />
                        <label for="wh">Warehouse</label>
                    </span>
                </div>
            </div>

            <div class="grid p-fluid mt-2" v-if="selectedWarehouse">
                <div class="field col-12 md:col-2">
                    <span class="p-float-label">
                        <Dropdown id="type" :options="typeOptions" v-model="form.type" optionLabel="label" optionValue="value" />
                        <label for="type">Type</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText id="lname" v-model="form.name" @blur="validateField(form.name,'name')" :class="{ 'p-invalid': formError.name }" />
                        <label for="lname">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-2">
                    <span class="p-float-label">
                        <InputText id="lcode" v-model="form.code" />
                        <label for="lcode">Code</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="parent" :options="flat" v-model="form.parent" filter showClear optionLabel="label" />
                        <label for="parent">Inside (parent)</label>
                    </span>
                </div>
                <div class="field col-12 md:col-2">
                    <Button @click="onSubmit" label="Add" class="p-button-outlined" />
                </div>
            </div>
        </div>

        <div class="card" v-if="selectedWarehouse">
            <h5>Layout</h5>
            <p v-if="!tree.length" class="text-600">No locations yet. Add a Rack, then Shelves inside it, then Bins.</p>
            <ul class="list-none p-0">
                <li v-for="rack in tree" :key="rack.id" class="mb-2 text-800">
                    <span class="font-bold"><i class="pi pi-server mr-2" />{{ rack.type }}: {{ rack.name }}</span>
                    <ul class="list-none">
                        <li v-for="shelf in rack.children" :key="shelf.id" class="ml-4 mt-1 text-750">
                            <i class="pi pi-bars mr-2" />{{ shelf.type }}: {{ shelf.name }}
                            <ul class="list-none">
                                <li v-for="bin in shelf.children" :key="bin.id" class="ml-4 mt-1 text-600">
                                    <i class="pi pi-box mr-2" />{{ bin.type }}: {{ bin.name }}
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</template>
