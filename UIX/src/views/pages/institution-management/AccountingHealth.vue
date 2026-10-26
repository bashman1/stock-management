<script setup>
import { ref, computed, onMounted } from 'vue';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const businesses = ref([]);
const selectedBusiness = ref(null);
const scan = ref(null);
const scanning = ref(false);
const fixing = ref(false);
const logs = ref([]);
// per branch: the issue codes ticked for repair, and which rows show their samples
const selections = ref({});
const expanded = ref({});
const confirmBranch = ref(null);

const severityTag = { error: 'danger', warning: 'warning', info: 'info' };

const money = (value) => commonService.formatMoney(value);

const getBusinesses = () => {
    commonService.genericRequest('accounting-health/businesses', 'post', true, {}).then((response) => {
        if (response.status) {
            businesses.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getLogs = () => {
    if (!selectedBusiness.value) return;
    commonService.genericRequest('accounting-health/logs', 'post', true, { institution_id: selectedBusiness.value.id }).then((response) => {
        if (response.status) {
            logs.value = response.data;
        }
    })
}

// tick every fixable issue except the optional ones, which need a decision
const defaultSelection = (branch) => branch.issues.filter((i) => i.fixable && !i.optional).map((i) => i.code);

const scanBusiness = () => {
    if (!selectedBusiness.value) {
        commonService.showWarning(toast, 'Choose a business first');
        return;
    }
    scanning.value = true;
    scan.value = null;
    commonService.genericRequest('accounting-health/scan', 'post', true, { institution_id: selectedBusiness.value.id }).then((response) => {
        scanning.value = false;
        if (response.status) {
            scan.value = response.data;
            selections.value = {};
            expanded.value = {};
            response.data.branches.forEach((b) => { selections.value[b.branch_id] = defaultSelection(b); });
            getLogs();
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

// step through the businesses one by one
const scanNext = () => {
    const list = businesses.value;
    if (!list.length) return;
    const index = selectedBusiness.value ? list.findIndex((b) => b.id === selectedBusiness.value.id) : -1;
    selectedBusiness.value = list[(index + 1) % list.length];
    scanBusiness();
}

const fixableIssues = (branch) => branch.issues.filter((i) => i.fixable);
const selectedFor = (branch) => branch.issues.filter((i) => (selections.value[branch.branch_id] || []).includes(i.code));

const applyFixes = () => {
    const branch = confirmBranch.value;
    const codes = selectedFor(branch).map((i) => i.code);
    fixing.value = true;
    commonService.genericRequest('accounting-health/fix', 'post', true, {
        institution_id: scan.value.institution.id,
        branch_id: branch.branch_id,
        codes: codes,
    }).then((response) => {
        fixing.value = false;
        confirmBranch.value = null;
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            const index = scan.value.branches.findIndex((b) => b.branch_id === branch.branch_id);
            scan.value.branches[index] = response.data.branch;
            selections.value[branch.branch_id] = defaultSelection(response.data.branch);
            getLogs();
            getBusinesses();
        } else {
            commonService.showError(toast, response.message, 10000);
        }
    })
}

const branchHealthy = (branch) => branch.issues.length === 0;
const errorCount = (branch) => branch.issues.filter((i) => i.severity === 'error').length;

const sampleColumns = (samples) => (samples && samples.length ? Object.keys(samples[0]) : []);
const formatCell = (value) => {
    if (value === null || value === undefined) return '';
    if (typeof value === 'object') return JSON.stringify(value);
    return value;
}

const totalIssues = computed(() => (scan.value ? scan.value.branches.reduce((n, b) => n + b.issues.length, 0) : 0));

onMounted(() => {
    getBusinesses();
});
</script>
<template>
    <div class="grid">
        <Toast />
        <div class="col-12">
            <div class="card">
                <h5>Accounting Health</h5>
                <p class="text-600 mt-0">
                    Scan a business's books branch by branch for known accounting problems, then apply the repairs you select.
                    Every repair runs in a single database transaction (all or nothing) and is recorded in the fix history below.
                </p>
                <div class="flex flex-wrap gap-2 align-items-center">
                    <Dropdown v-model="selectedBusiness" :options="businesses" optionLabel="name" filter placeholder="Choose a business"
                        class="w-full md:w-25rem">
                        <template #option="{ option }">
                            <div class="flex justify-content-between w-full gap-3">
                                <span>{{ option.name }} <span class="text-500">({{ option.status }})</span></span>
                                <span class="text-500 text-sm">{{ option.last_fixed_on ? 'fixed ' + option.last_fixed_on.substring(0, 10) : option.ledger_rows + ' ledger rows' }}</span>
                            </div>
                        </template>
                    </Dropdown>
                    <Button label="Scan" icon="pi pi-search" :loading="scanning" @click="scanBusiness" />
                    <Button label="Scan next business" icon="pi pi-arrow-right" class="p-button-outlined" :disabled="scanning" @click="scanNext" />
                </div>
            </div>
        </div>

        <div class="col-12" v-if="scan">
            <div class="card">
                <div class="flex justify-content-between align-items-center">
                    <h5 class="m-0">{{ scan.institution.name }}</h5>
                    <span class="text-500 text-sm">Scanned {{ scan.scanned_on }} · {{ scan.branches.length }} branch(es) · {{ totalIssues }} issue(s)</span>
                </div>
            </div>

            <div class="card" v-for="branch in scan.branches" :key="branch.branch_id">
                <div class="flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0">{{ branch.branch }} <span class="text-500 text-base">(branch {{ branch.code }})</span></h5>
                    <Tag v-if="branchHealthy(branch)" severity="success" value="Healthy"></Tag>
                    <Tag v-else :severity="errorCount(branch) ? 'danger' : 'warning'" :value="branch.issues.length + ' issue(s)'"></Tag>
                </div>

                <div class="grid">
                    <div class="col-12 md:col-4">
                        <div class="text-500 text-sm">Balance sheet (ledger)</div>
                        <div class="font-bold" :class="Math.abs(branch.summary.balance_check_ledger) < 0.01 ? 'text-green-600' : 'text-red-500'">
                            {{ Math.abs(branch.summary.balance_check_ledger) < 0.01 ? 'Balances' : 'Out by ' + money(branch.summary.balance_check_ledger) }}
                        </div>
                    </div>
                    <div class="col-12 md:col-4">
                        <div class="text-500 text-sm">Balance sheet (cached balances)</div>
                        <div class="font-bold" :class="Math.abs(branch.summary.balance_check_stored) < 0.01 ? 'text-green-600' : 'text-red-500'">
                            {{ Math.abs(branch.summary.balance_check_stored) < 0.01 ? 'Balances' : 'Out by ' + money(branch.summary.balance_check_stored) }}
                        </div>
                    </div>
                    <div class="col-12 md:col-4">
                        <div class="text-500 text-sm">Cash in hand</div>
                        <div class="font-bold" :class="{ 'text-red-500': branch.summary.cash_in_hand < 0 }">{{ money(branch.summary.cash_in_hand) }}</div>
                    </div>
                    <div class="col-12 md:col-4">
                        <div class="text-500 text-sm">Inventories account / stock on hand</div>
                        <div class="font-bold">{{ money(branch.summary.inventory_gl) }} / {{ money(branch.summary.inventory_layers) }}</div>
                    </div>
                    <div class="col-12 md:col-4">
                        <div class="text-500 text-sm">Assets / liabilities / equity</div>
                        <div class="font-bold">{{ money(branch.summary.total_assets) }} / {{ money(branch.summary.total_liabilities) }} / {{ money(branch.summary.total_equity) }}</div>
                    </div>
                    <div class="col-12 md:col-4">
                        <div class="text-500 text-sm">Income / expenses (incl. cost of sales)</div>
                        <div class="font-bold">{{ money(branch.summary.total_income) }} / {{ money(branch.summary.total_expense) }}</div>
                    </div>
                </div>

                <DataTable v-if="!branchHealthy(branch)" :value="branch.issues" dataKey="code" class="p-datatable-sm mt-3"
                    v-model:expandedRows="expanded[branch.branch_id]"
                    :rowClass="(row) => (row.fixable ? '' : 'opacity-80')" responsiveLayout="scroll">
                    <Column expander style="width: 3rem" />
                    <Column style="width: 3rem">
                        <template #body="{ data }">
                            <Checkbox v-if="data.fixable" v-model="selections[branch.branch_id]" :value="data.code" />
                        </template>
                    </Column>
                    <Column header="Severity" style="width: 7rem">
                        <template #body="{ data }">
                            <Tag :severity="severityTag[data.severity]" :value="data.severity"></Tag>
                        </template>
                    </Column>
                    <Column header="Issue" style="min-width: 22rem">
                        <template #body="{ data }">
                            <div class="font-semibold">{{ data.title }}</div>
                            <div class="text-600 text-sm mt-1">{{ data.description }}</div>
                        </template>
                    </Column>
                    <Column header="Count" style="width: 6rem">
                        <template #body="{ data }">{{ data.count }}</template>
                    </Column>
                    <Column header="Amount" style="width: 10rem">
                        <template #body="{ data }">{{ data.amount ? money(data.amount) : '' }}</template>
                    </Column>
                    <Column header="Repair" style="width: 14rem">
                        <template #body="{ data }">
                            <span v-if="data.fixable">{{ data.fix_label }}<Tag v-if="data.optional" class="ml-2" severity="warning" value="your call"></Tag></span>
                            <span v-else class="text-500">Manual review</span>
                        </template>
                    </Column>
                    <template #expansion="{ data }">
                        <div v-if="data.samples.length" class="p-2">
                            <div class="text-500 text-sm mb-2">First {{ data.samples.length }} of {{ data.count }}</div>
                            <table class="health-samples">
                                <tr><th v-for="col in sampleColumns(data.samples)" :key="col">{{ col }}</th></tr>
                                <tr v-for="(row, i) in data.samples" :key="i">
                                    <td v-for="col in sampleColumns(data.samples)" :key="col">{{ formatCell(row[col]) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div v-else class="p-2 text-500">No details.</div>
                    </template>
                </DataTable>

                <div v-if="fixableIssues(branch).length" class="flex justify-content-end mt-3">
                    <Button :label="'Apply ' + selectedFor(branch).length + ' selected repair(s)'" icon="pi pi-wrench" class="p-button-danger"
                        :disabled="!selectedFor(branch).length || fixing" @click="confirmBranch = branch" />
                </div>
            </div>

            <div class="card">
                <h5>Fix history</h5>
                <DataTable :value="logs" :paginator="true" :rows="10" class="p-datatable-sm" responsiveLayout="scroll">
                    <Column field="created_on" header="When" style="min-width: 10rem" />
                    <Column field="branch" header="Branch" />
                    <Column field="issue_code" header="Repair" />
                    <Column field="summary" header="Summary" style="min-width: 20rem" />
                    <Column header="Amount">
                        <template #body="{ data }">{{ Number(data.amount) ? money(data.amount) : '' }}</template>
                    </Column>
                    <Column field="created_by" header="By" />
                    <template #empty>No repairs applied to this business yet.</template>
                </DataTable>
            </div>
        </div>

        <Dialog :visible="!!confirmBranch" @update:visible="confirmBranch = null" header="Apply repairs" :modal="true" :style="{ width: '34rem' }">
            <div v-if="confirmBranch">
                <p>These repairs will change the books of <b>{{ scan.institution.name }}</b>, branch <b>{{ confirmBranch.branch }}</b>:</p>
                <ul>
                    <li v-for="issue in selectedFor(confirmBranch)" :key="issue.code">{{ issue.fix_label }} <span class="text-500">({{ issue.title }})</span></li>
                    <li>Recalculate cached balances from the ledger <span class="text-500">(always runs)</span></li>
                </ul>
                <p class="text-600 text-sm">They run together in one transaction: if any step fails nothing is changed. Each repair is logged with the rows it touched.</p>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="confirmBranch = null" />
                <Button label="Apply" icon="pi pi-check" class="p-button-danger" :loading="fixing" @click="applyFixes" />
            </template>
        </Dialog>
    </div>
</template>

<style scoped>
.health-samples {
    border-collapse: collapse;
    font-size: 0.85rem;
}
.health-samples th,
.health-samples td {
    border: 1px solid var(--surface-border);
    padding: 0.25rem 0.5rem;
    text-align: left;
}
</style>
