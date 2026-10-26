<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const accounts = ref([]);
const header = reactive({ journalDate: new Date(), narration: null });
const lines = ref([
    { account: null, debit: null, credit: null, description: null },
    { account: null, debit: null, credit: null, description: null }
]);

const num = (v) => Number(v) || 0;
const totalDr = computed(() => lines.value.reduce((s, l) => s + num(l.debit), 0));
const totalCr = computed(() => lines.value.reduce((s, l) => s + num(l.credit), 0));
const difference = computed(() => totalDr.value - totalCr.value);
const filledLines = computed(() => lines.value.filter((l) => l.account && (num(l.debit) > 0 || num(l.credit) > 0)));
const balanced = computed(() => totalDr.value > 0 && Math.abs(difference.value) < 0.01 && filledLines.value.length >= 2);

const fetchAccounts = () => {
    commonService.genericRequest('gl-search', 'post', true, {}).then((r) => {
        if (r.status) accounts.value = r.data;
    });
};

const addLine = () => lines.value.push({ account: null, debit: null, credit: null, description: null });
const removeLine = (i) => { if (lines.value.length > 2) lines.value.splice(i, 1); };

// keep a line one-sided: typing a debit clears the credit and vice-versa
const onDebit = (l) => { if (num(l.debit) > 0) l.credit = null; };
const onCredit = (l) => { if (num(l.credit) > 0) l.debit = null; };

const fmtDate = (d) => { if (!d) return null; const dt = new Date(d); return isNaN(dt.getTime()) ? null : commonService.toApiDate(dt); };
const show = (v) => commonService.commaSeparator(v);

const onSubmit = () => {
    if (!balanced.value) {
        commonService.showError(toast, 'The journal must have at least two lines and balance (Total Dr = Total Cr).');
        return;
    }
    const payloadLines = filledLines.value.map((l) => ({
        acct_no: l.account?.acct_no,
        dr_cr: num(l.debit) > 0 ? 'Dr' : 'Cr',
        amount: num(l.debit) > 0 ? num(l.debit) : num(l.credit),
        description: l.description
    }));
    const postData = { journal_date: fmtDate(header.journalDate), narration: header.narration, lines: payloadLines };

    commonService.genericRequest('create-journal-entry', 'post', true, postData).then((r) => {
        if (r.status) {
            commonService.showSuccess(toast, r.message);
            header.narration = null;
            header.journalDate = new Date();
            lines.value = [
                { account: null, debit: null, credit: null, description: null },
                { account: null, debit: null, credit: null, description: null }
            ];
        } else {
            commonService.showError(toast, r.message);
        }
    });
};

onMounted(() => fetchAccounts());
</script>

<template>
    <div>
        <div class="card">
            <h5>Journal Entry</h5>
            <Toast />
            <div class="grid p-fluid mt-2">
                <div class="field col-12 md:col-3">
                    <span class="p-float-label">
                        <Calendar inputId="jdate" v-model="header.journalDate" dateFormat="yy-mm-dd" />
                        <label for="jdate">Date</label>
                    </span>
                </div>
                <div class="field col-12 md:col-9">
                    <span class="p-float-label">
                        <InputText id="narr" v-model="header.narration" />
                        <label for="narr">Narration</label>
                    </span>
                </div>
            </div>

            <table class="w-full" style="border-collapse: collapse">
                <thead>
                    <tr class="text-left">
                        <th class="p-2" style="width: 30%">Account</th>
                        <th class="p-2" style="width: 25%">Description</th>
                        <th class="p-2 text-right" style="width: 17%">Debit</th>
                        <th class="p-2 text-right" style="width: 17%">Credit</th>
                        <th class="p-2" style="width: 6%"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(l, i) in lines" :key="i">
                        <td class="p-1">
                            <Dropdown :options="accounts" v-model="l.account" filter optionLabel="description"
                                placeholder="Select GL account" class="w-full" />
                        </td>
                        <td class="p-1"><InputText v-model="l.description" placeholder="Line note (optional)" class="w-full" /></td>
                        <td class="p-1"><InputText v-model="l.debit" @input="onDebit(l)" placeholder="Debit amount" class="w-full text-right" /></td>
                        <td class="p-1"><InputText v-model="l.credit" @input="onCredit(l)" placeholder="Credit amount" class="w-full text-right" /></td>
                        <td class="p-1 text-center">
                            <Button icon="pi pi-trash" class="p-button-text p-button-danger" @click="removeLine(i)"
                                :disabled="lines.length <= 2" />
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-bold">
                        <td class="p-2" colspan="2">
                            <Button label="Add Line" icon="pi pi-plus" class="p-button-text" @click="addLine" />
                        </td>
                        <td class="p-2 text-right">{{ show(totalDr) }}</td>
                        <td class="p-2 text-right">{{ show(totalCr) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <div class="flex align-items-center justify-content-between mt-3">
                <Tag v-if="balanced" severity="success" value="Balanced ✓" />
                <Tag v-else severity="danger" :value="'Out of balance by ' + show(difference)" />
                <Button label="Post Journal" icon="pi pi-check" class="p-button-success" :disabled="!balanced" @click="onSubmit" />
            </div>
        </div>
    </div>
</template>
