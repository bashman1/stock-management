<script setup>
import { ref, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const entries = ref([]);
const fromDate = ref(null);
const toDate = ref(null);
const search = ref(null);

const expanded = ref([]);
const lines = ref({}); // entryId -> lines

const fmtDate = (d) => { if (!d) return null; const dt = new Date(d); return isNaN(dt.getTime()) ? null : dt.toISOString().slice(0, 10); };
const show = (v) => commonService.commaSeparator(v);

const getEntries = () => {
    const postData = { from_date: fmtDate(fromDate.value), to_date: fmtDate(toDate.value), searchKey: search.value };
    commonService.genericRequest('get-journal-entries', 'post', true, postData).then((r) => {
        if (r.status) entries.value = r.data; else commonService.showError(toast, r.message);
    });
};

const onRowExpand = (event) => {
    const id = event.data.id;
    commonService.genericRequest('get-journal-entry', 'post', true, { id }).then((r) => {
        if (r.status) lines.value = { ...lines.value, [id]: r.data?.lines ?? [] };
    });
};

const reverse = (row) => {
    commonService.genericRequest('reverse-journal-entry', 'post', true, { id: row.id }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); getEntries(); }
        else commonService.showError(toast, r.message);
    });
};

onMounted(() => getEntries());
</script>

<template>
    <div>
        <div class="card">
            <h5>Journal Register</h5>
            <Toast />
            <Toolbar class="mb-4">
                <template v-slot:start>
                    <div class="flex flex-wrap align-items-center gap-2">
                        <span class="p-float-label">
                            <Calendar inputId="from" v-model="fromDate" dateFormat="yy-mm-dd" />
                            <label for="from">From</label>
                        </span>
                        <span class="p-float-label">
                            <Calendar inputId="to" v-model="toDate" dateFormat="yy-mm-dd" />
                            <label for="to">To</label>
                        </span>
                        <InputText v-model="search" placeholder="Search ref/narration" />
                        <Button label="Search" icon="pi pi-search" class="p-button-success" @click="getEntries" />
                    </div>
                </template>
            </Toolbar>

            <DataTable :value="entries" v-model:expandedRows="expanded" @rowExpand="onRowExpand" dataKey="id"
                :paginator="true" :rows="20" class="p-datatable-gridlines" responsiveLayout="scroll">
                <Column :expander="true" headerStyle="width: 3rem" />
                <Column field="ref_no" header="Ref" />
                <Column header="Date"><template #body="{ data }">{{ fmtDate(data.journal_date) }}</template></Column>
                <Column field="narration" header="Narration" />
                <Column header="Lines"><template #body="{ data }">{{ data.lines_count }}</template></Column>
                <Column header="Amount"><template #body="{ data }">{{ show(data.total_amount) }}</template></Column>
                <Column header="Status">
                    <template #body="{ data }">
                        <Tag :value="data.status" :severity="data.status === 'Reversed' ? 'danger' : 'success'" />
                    </template>
                </Column>
                <Column header="">
                    <template #body="{ data }">
                        <Button v-if="data.status === 'Active'" label="Reverse" icon="pi pi-replay"
                            class="p-button-sm p-button-outlined p-button-danger" @click="reverse(data)" />
                    </template>
                </Column>
                <template #expansion="{ data }">
                    <div class="p-3">
                        <h6>Lines</h6>
                        <table class="w-full" style="border-collapse: collapse">
                            <thead>
                                <tr class="text-left">
                                    <th class="p-2">Account</th>
                                    <th class="p-2">Description</th>
                                    <th class="p-2 text-right">Debit</th>
                                    <th class="p-2 text-right">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(l, i) in (lines[data.id] || [])" :key="i">
                                    <td class="p-2">{{ l.account_name || l.acct_no }}</td>
                                    <td class="p-2">{{ l.description }}</td>
                                    <td class="p-2 text-right">{{ l.dr_cr === 'Dr' ? show(l.amount) : '' }}</td>
                                    <td class="p-2 text-right">{{ l.dr_cr === 'Cr' ? show(l.amount) : '' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </DataTable>
        </div>
    </div>
</template>
