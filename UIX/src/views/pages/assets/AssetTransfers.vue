<script setup>
import { ref, reactive, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const assets = ref([]);
const branches = ref([]);
const transfers = ref([]);
const form = reactive({ asset: null, toBranch: null, toLocation: null, reason: null, date: new Date() });
const formError = reactive({});

const validateField = (v, k) => { formError[k] = commonService.validateFormField(v); };
const fmtDate = (d) => { if (!d) return null; const dt = new Date(d); return isNaN(dt.getTime()) ? null : commonService.toApiDate(dt); };

const getAssets = () => commonService.genericRequest('get-fixed-assets', 'post', true, {}).then((r) => { if (r.status) assets.value = r.data; });
const getBranches = () => commonService.genericRequest('get-branches', 'post', true, {}).then((r) => { if (r.status) branches.value = r.data?.data ?? r.data; });
const getTransfers = () => commonService.genericRequest('get-asset-transfers', 'post', true, {}).then((r) => { if (r.status) transfers.value = r.data; });

const onSubmit = () => {
    validateField(form.asset, 'asset');
    validateField(form.toLocation, 'toLocation');
    if (commonService.validateRequiredFields(formError)) {
        commonService.showError(toast, 'Select an asset and destination.');
        return;
    }
    const postData = {
        asset_id: form.asset?.id,
        to_branch_id: form.toBranch?.id,
        to_location: form.toLocation,
        reason: form.reason,
        transfer_date: fmtDate(form.date)
    };
    commonService.genericRequest('create-asset-transfer', 'post', true, postData).then((r) => {
        if (r.status) {
            commonService.showSuccess(toast, r.message);
            form.asset = null; form.toBranch = null; form.toLocation = null; form.reason = null; form.date = new Date();
            getTransfers();
        } else commonService.showError(toast, r.message);
    });
};

onMounted(() => { getAssets(); getBranches(); getTransfers(); });
</script>

<template>
    <div>
        <div class="card">
            <h5>Transfer Asset</h5>
            <Toast />
            <div class="grid p-fluid mt-3">
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="asset" :options="assets" v-model="form.asset" filter optionLabel="name"
                            @blur="validateField(form.asset,'asset')" :class="{ 'p-invalid': formError.asset }" />
                        <label for="asset">Asset</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Dropdown id="tb" :options="branches" v-model="form.toBranch" filter optionLabel="name" showClear />
                        <label for="tb">To Branch (optional)</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <InputText id="tl" v-model="form.toLocation" @blur="validateField(form.toLocation,'toLocation')" :class="{ 'p-invalid': formError.toLocation }" />
                        <label for="tl">To Location</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Calendar id="d" v-model="form.date" dateFormat="yy-mm-dd" />
                        <label for="d">Transfer Date</label>
                    </span>
                </div>
                <div class="field col-12 md:col-9">
                    <span class="p-float-label">
                        <InputText id="reason" v-model="form.reason" />
                        <label for="reason">Reason</label>
                    </span>
                </div>
                <div class="field col-12 md:col-3">
                    <Button @click="onSubmit" label="Transfer" class="p-button-outlined" />
                </div>
            </div>
        </div>

        <div class="card">
            <h5>Transfer History</h5>
            <DataTable :value="transfers" :paginator="true" class="p-datatable-gridlines" :rows="15" dataKey="id" responsiveLayout="scroll">
                <Column field="asset_name" header="Asset" />
                <Column field="from_branch" header="From Branch" />
                <Column field="from_location" header="From Location" />
                <Column field="to_branch" header="To Branch" />
                <Column field="to_location" header="To Location" />
                <Column header="Date"><template #body="{ data }">{{ data.transfer_date }}</template></Column>
                <Column field="reason" header="Reason" />
            </DataTable>
        </div>
    </div>
</template>
