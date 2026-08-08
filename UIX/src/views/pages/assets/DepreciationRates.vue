<script setup>
import { ref, reactive, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const rates = ref([]);
const durations = ref([]);
const form = reactive({ name: null, rate: null, duration: null, description: null });
const formError = reactive({});

const validateField = (v, k) => { formError[k] = commonService.validateFormField(v); };

const getRates = () => commonService.genericRequest('get-depreciation-rates', 'get', true, {}).then((r) => { if (r.status) rates.value = r.data; });
const getDurations = () => commonService.genericRequest('get-durations', 'get', true, {}).then((r) => { if (r.status) durations.value = r.data; });

const onSubmit = () => {
    validateField(form.name, 'name');
    validateField(form.rate, 'rate');
    if (commonService.validateRequiredFields(formError)) {
        commonService.showError(toast, 'Enter a name and rate.');
        return;
    }
    const postData = { name: form.name, rate: form.rate, duration_id: form.duration?.id, description: form.description };
    commonService.genericRequest('create-depreciation-rate', 'post', true, postData).then((r) => {
        if (r.status) {
            commonService.showSuccess(toast, r.message);
            form.name = null; form.rate = null; form.duration = null; form.description = null;
            getRates();
        } else commonService.showError(toast, r.message);
    });
};

onMounted(() => { getRates(); getDurations(); });
</script>

<template>
    <div>
        <div class="card">
            <h5>Depreciation Rates</h5>
            <Toast />
            <div class="grid p-fluid mt-3">
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText id="name" v-model="form.name" @blur="validateField(form.name,'name')" :class="{ 'p-invalid': formError.name }" />
                        <label for="name">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText id="rate" v-model="form.rate" @blur="validateField(form.rate,'rate')" :class="{ 'p-invalid': formError.rate }" />
                        <label for="rate">Annual Rate (%)</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="dur" :options="durations" v-model="form.duration" optionLabel="name" showClear />
                        <label for="dur">Duration</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <Button @click="onSubmit" label="Add Rate" class="p-button-outlined" />
                </div>
                <div class="field col-12">
                    <span class="p-float-label">
                        <InputText id="desc" v-model="form.description" />
                        <label for="desc">Description</label>
                    </span>
                </div>
            </div>

            <DataTable :value="rates" :paginator="true" class="p-datatable-gridlines" :rows="15" dataKey="id" responsiveLayout="scroll">
                <Column field="name" header="Name" />
                <Column header="Rate (%)"><template #body="{ data }">{{ data.rate }}</template></Column>
                <Column header="Duration"><template #body="{ data }">{{ data.duration?.name }}</template></Column>
                <Column field="description" header="Description" />
            </DataTable>
        </div>
    </div>
</template>
