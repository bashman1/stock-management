<script setup>
import { ref, onMounted } from 'vue';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();

const loading = ref(false);
const kpis = ref({});
const range = ref({});
const period = ref('month');
const from = ref(null);
const to = ref(null);

const trendData = ref(null);
const trendOptions = ref({});
const topProductsData = ref(null);
const categoryData = ref(null);
const expenseData = ref(null);
const volumeData = ref(null);
const chartOptions = ref({});
const hbarOptions = ref({});
const pieOptions = ref({});

// build chart option sets from the CURRENT theme's CSS variables (adapts light/dark)
const applyThemeToCharts = () => {
    const cs = getComputedStyle(document.documentElement);
    const text = (cs.getPropertyValue('--text-color') || '').trim() || '#495057';
    const grid = (cs.getPropertyValue('--surface-border') || '').trim() || '#dfe7ef';
    chartOptions.value = {
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: text } } },
        scales: { x: { ticks: { color: text }, grid: { color: grid } }, y: { ticks: { color: text }, grid: { color: grid } } }
    };
    hbarOptions.value = { ...chartOptions.value, indexAxis: 'y' };
    pieOptions.value = { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: text } } } };
    trendOptions.value = { ...chartOptions.value };
};

const periods = [
    { label: 'Today', value: 'day' },
    { label: 'This Week', value: 'week' },
    { label: 'This Month', value: 'month' },
    { label: 'This Year', value: 'year' },
    { label: 'Custom', value: 'custom' }
];

const money = (v) => commonService.commaSeparator(Number(v || 0).toFixed(0));

const palette = ['#6366f1', '#22c55e', '#f97316', '#ef4444', '#14b8a6', '#a855f7', '#eab308', '#3b82f6'];

const kpiCards = ref([]);
const buildKpiCards = () => {
    const k = kpis.value;
    kpiCards.value = [
        { label: 'Revenue', value: money(k.revenue), icon: 'pi-dollar', color: '#22c55e' },
        { label: 'COGS', value: money(k.cogs), icon: 'pi-shopping-cart', color: '#f97316' },
        { label: 'Gross Profit', value: money(k.gross_profit), icon: 'pi-chart-line', color: '#14b8a6' },
        { label: 'Operating Exp.', value: money(k.operating_expenses), icon: 'pi-wallet', color: '#ef4444' },
        { label: 'Net Profit', value: money(k.net_profit), icon: 'pi-percentage', color: '#6366f1' },
        { label: 'Inventory Value', value: money(k.inventory_value), icon: 'pi-box', color: '#3b82f6' },
        { label: 'Receivables', value: money(k.receivables), icon: 'pi-arrow-down-left', color: '#0ea5e9' },
        { label: 'Payables', value: money(k.payables), icon: 'pi-arrow-up-right', color: '#f43f5e' },
        { label: 'Orders', value: k.orders_count ?? 0, icon: 'pi-receipt', color: '#a855f7' },
        { label: 'Low Stock', value: k.low_stock ?? 0, icon: 'pi-exclamation-triangle', color: '#eab308' }
    ];
};

const computeRange = () => {
    const now = new Date();
    // local calendar day: toISOString() is UTC, a day early for East Africa
    const iso = (d) => commonService.toApiDate(d);
    if (period.value === 'custom') {
        return { from: from.value ? iso(new Date(from.value)) : null, to: to.value ? iso(new Date(to.value)) : null };
    }
    let start = new Date();
    if (period.value === 'day') start = now;
    else if (period.value === 'week') start = new Date(now.getTime() - 6 * 864e5);
    else if (period.value === 'month') start = new Date(now.getFullYear(), now.getMonth(), 1);
    else if (period.value === 'year') start = new Date(now.getFullYear(), 0, 1);
    return { from: iso(start), to: iso(now) };
};

const loadAnalytics = () => {
    loading.value = true;
    applyThemeToCharts();
    const r = computeRange();
    commonService.genericRequest('get-analytics', 'post', true, r).then((res) => {
        loading.value = false;
        if (!res.status) { commonService.showError(toast, res.message); return; }
        const d = res.data;
        kpis.value = d.kpis || {};
        range.value = d.range || {};
        buildKpiCards();

        const labels = (d.series || []).map((s) => s.bucket);
        trendData.value = {
            labels,
            datasets: [
                { type: 'bar', label: 'Revenue', backgroundColor: '#22c55e', data: (d.series || []).map((s) => Number(s.revenue)) },
                { type: 'bar', label: 'COGS', backgroundColor: '#f97316', data: (d.series || []).map((s) => Number(s.cogs)) },
                { type: 'line', label: 'Expenses', borderColor: '#ef4444', tension: 0.4, fill: false, data: (d.series || []).map((s) => Number(s.expenses)) }
            ]
        };
        topProductsData.value = {
            labels: (d.top_products || []).map((p) => p.name),
            datasets: [{ label: 'Revenue', backgroundColor: '#6366f1', data: (d.top_products || []).map((p) => Number(p.revenue)) }]
        };
        categoryData.value = {
            labels: (d.by_category || []).map((c) => c.category),
            datasets: [{ backgroundColor: palette, data: (d.by_category || []).map((c) => Number(c.revenue)) }]
        };
        expenseData.value = {
            labels: (d.expense_breakdown || []).map((e) => e.account),
            datasets: [{ backgroundColor: palette, data: (d.expense_breakdown || []).map((e) => Number(e.amount)) }]
        };
        const vol = d.activity_volume || {};
        volumeData.value = {
            labels: ['Sales', 'Purchases', 'Expenses', 'Journals', 'Production', 'Adjustments', 'Transfers'],
            datasets: [{ label: 'Count', backgroundColor: '#0ea5e9', data: [vol.sales, vol.purchases, vol.expenses, vol.journals, vol.production, vol.adjustments, vol.transfers].map(Number) }]
        };
    }).catch(() => { loading.value = false; });
};

onMounted(() => {
    applyThemeToCharts();
    loadAnalytics();
});
</script>

<template>
    <div>
        <div class="card">
            <div class="flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="m-0">Analytics Dashboard</h5>
                <div class="flex align-items-center gap-2 flex-wrap">
                    <Dropdown :options="periods" v-model="period" optionLabel="label" optionValue="value" @change="loadAnalytics" class="w-10rem" />
                    <template v-if="period === 'custom'">
                        <Calendar v-model="from" dateFormat="yy-mm-dd" placeholder="From" showIcon />
                        <Calendar v-model="to" dateFormat="yy-mm-dd" placeholder="To" showIcon />
                        <Button icon="pi pi-search" label="Apply" @click="loadAnalytics" />
                    </template>
                    <Tag v-if="range.from" :value="range.from + ' → ' + range.to" severity="info" />
                </div>
            </div>
        </div>

        <!-- KPI cards -->
        <div class="grid">
            <div v-for="c in kpiCards" :key="c.label" class="col-12 sm:col-6 lg:col-3 xl:col-2">
                <div class="card mb-0 h-full">
                    <div class="flex align-items-center gap-3">
                        <span class="flex align-items-center justify-content-center border-round"
                            :style="{ background: c.color + '22', color: c.color, width: '2.75rem', height: '2.75rem' }">
                            <i :class="'pi ' + c.icon" style="font-size: 1.25rem" />
                        </span>
                        <div>
                            <div class="text-600 text-sm">{{ c.label }}</div>
                            <div class="text-900 font-bold text-lg">{{ c.value }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid">
            <div class="col-12 lg:col-8">
                <div class="card">
                    <h6>Revenue · COGS · Expenses</h6>
                    <Chart type="bar" :data="trendData" :options="trendOptions" style="height: 22rem" />
                </div>
            </div>
            <div class="col-12 lg:col-4">
                <div class="card">
                    <h6>Sales by Category</h6>
                    <Chart type="doughnut" :data="categoryData" :options="pieOptions" style="height: 22rem" />
                </div>
            </div>
            <div class="col-12 lg:col-6">
                <div class="card">
                    <h6>Top Products (revenue)</h6>
                    <Chart type="bar" :data="topProductsData" :options="hbarOptions" style="height: 20rem" />
                </div>
            </div>
            <div class="col-12 lg:col-6">
                <div class="card">
                    <h6>Expense Breakdown</h6>
                    <Chart type="pie" :data="expenseData" :options="pieOptions" style="height: 20rem" />
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <h6>Activity Volume</h6>
                    <Chart type="bar" :data="volumeData" :options="chartOptions" style="height: 18rem" />
                </div>
            </div>
        </div>
    </div>
</template>
