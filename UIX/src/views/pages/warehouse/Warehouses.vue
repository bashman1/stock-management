<script setup>
import { ref, reactive, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const warehouses = ref([]);
const users = ref([]);
const form = reactive({ id: null, name: null, code: null, manager: null, capacity: null });
const formError = reactive({});

const validateField = (v, k) => { formError[k] = commonService.validateFormField(v); };

const getWarehouses = () => commonService.genericRequest('get-warehouses', 'post', true, {}).then((r) => { if (r.status) warehouses.value = r.data; });
const getUsers = () => commonService.genericRequest('get-users', 'get', true, {}).then((r) => {
    if (r.status) {
        const list = r.data?.data ?? r.data ?? [];
        users.value = list.map((u) => ({ ...u, full_name: `${u.first_name ?? ''} ${u.last_name ?? ''}`.trim() || u.email }));
    }
});

const resetForm = () => { form.id = null; form.name = null; form.code = null; form.manager = null; form.capacity = null; };
const editWarehouse = (w) => {
    form.id = w.id; form.name = w.name; form.code = w.code; form.capacity = w.capacity;
    form.manager = users.value.find((u) => u.id === w.manager_id) || null;
};

const onSubmit = () => {
    validateField(form.name, 'name');
    if (commonService.validateRequiredFields(formError)) {
        commonService.showError(toast, 'Warehouse name is required.');
        return;
    }
    const postData = {
        id: form.id, name: form.name, code: form.code,
        manager_id: form.manager?.id ?? null, capacity: form.capacity
    };
    const url = form.id ? 'update-warehouse' : 'create-warehouse';
    commonService.genericRequest(url, 'post', true, postData).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); resetForm(); getWarehouses(); }
        else commonService.showError(toast, r.message);
    });
};

onMounted(() => { getUsers(); getWarehouses(); });
</script>

<template>
    <div>
        <div class="card">
            <h5>{{ form.id ? 'Edit Warehouse' : 'Add Warehouse' }}</h5>
            <Toast />
            <div class="grid p-fluid mt-3">
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText id="name" v-model="form.name" @blur="validateField(form.name,'name')" :class="{ 'p-invalid': formError.name }" />
                        <label for="name">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-2">
                    <span class="p-float-label">
                        <InputText id="code" v-model="form.code" />
                        <label for="code">Code</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="mgr" :options="users" v-model="form.manager" filter optionLabel="full_name" showClear />
                        <label for="mgr">Manager</label>
                    </span>
                </div>
                <div class="field col-12 md:col-2">
                    <span class="p-float-label">
                        <InputText id="cap" v-model="form.capacity" />
                        <label for="cap">Capacity (units)</label>
                    </span>
                </div>
                <div class="field col-12 md:col-2 flex gap-2">
                    <Button @click="onSubmit" :label="form.id ? 'Update' : 'Add'" class="p-button-outlined" />
                    <Button v-if="form.id" @click="resetForm" label="Cancel" class="p-button-text" />
                </div>
            </div>
        </div>

        <div class="card">
            <h5>Warehouses</h5>
            <DataTable :value="warehouses" :paginator="true" class="p-datatable-gridlines" :rows="15" dataKey="id" responsiveLayout="scroll">
                <Column field="name" header="Name" />
                <Column field="code" header="Code" />
                <Column header="Manager"><template #body="{ data }">{{ data.manager_name || '—' }}</template></Column>
                <Column header="Default"><template #body="{ data }"><Tag v-if="data.is_default" value="Default" severity="info" /></template></Column>
                <Column header="On hand"><template #body="{ data }">{{ commonService.commaSeparator(data.used_quantity) }}</template></Column>
                <Column header="Capacity"><template #body="{ data }">{{ data.capacity ? commonService.commaSeparator(data.capacity) : '—' }}</template></Column>
                <Column header="Utilization" style="min-width: 12rem">
                    <template #body="{ data }">
                        <div v-if="data.utilization !== null">
                            <div style="background:#e9ecef;border-radius:6px;height:10px;overflow:hidden">
                                <div :style="{ width: Math.min(data.utilization,100)+'%', height:'100%', background: data.utilization>90?'#e24c4c':(data.utilization>70?'#f9a825':'#34a853') }"></div>
                            </div>
                            <small>{{ data.utilization }}%</small>
                        </div>
                        <span v-else>—</span>
                    </template>
                </Column>
                <Column header="">
                    <template #body="{ data }">
                        <Button icon="pi pi-pencil" class="p-button-text p-button-sm" @click="editWarehouse(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>
