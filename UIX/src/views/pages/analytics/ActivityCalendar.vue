<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import multiMonthPlugin from '@fullcalendar/multimonth';
import interactionPlugin from '@fullcalendar/interaction';
import Dialog from 'primevue/dialog';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();

const legend = [
    { type: 'sale', label: 'Sales', color: '#22c55e' },
    { type: 'purchase', label: 'Purchases', color: '#3b82f6' },
    { type: 'expense', label: 'Expenses', color: '#ef4444' },
    { type: 'journal', label: 'Journals', color: '#a855f7' },
    { type: 'production', label: 'Production', color: '#f97316' },
    { type: 'adjustment', label: 'Adjustments', color: '#eab308' },
    { type: 'transfer', label: 'Transfers', color: '#14b8a6' },
    { type: 'asset', label: 'Assets', color: '#64748b' },
    { type: 'reminder', label: 'Reminders', color: '#6366f1' }
];

const pad = (n) => String(n).padStart(2, '0');
const fmt = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;

const window = reactive({ from: null, to: null });

const themeText = () => ((getComputedStyle(document.documentElement).getPropertyValue('--text-color') || '').trim() || '#495057');

const loadFeed = () => {
    if (!window.from || !window.to) return;
    const text = themeText();
    commonService.genericRequest('get-calendar-feed', 'post', true, { from: window.from, to: window.to }).then((r) => {
        if (!r.status) { commonService.showError(toast, r.message); return; }
        calendarOptions.events = (r.data || []).map((e) => {
            const color = e.color || '#6366f1';
            return {
                id: e.id ? `${e.type}-${e.id}` : undefined,
                title: e.title,
                start: e.start,
                end: e.end || e.start,
                backgroundColor: color + '33',
                borderColor: color,
                textColor: text, // adapt to the active theme (dark text in light mode, light in dark)
                extendedProps: { type: e.type, editable: e.editable, route: e.route, amount: e.amount, rawId: e.id, description: e.description }
            };
        });
    });
};

const onDatesSet = (arg) => {
    window.from = arg.startStr.slice(0, 10);
    window.to = arg.endStr.slice(0, 10);
    loadFeed();
};

const onEventClick = (arg) => {
    const p = arg.event.extendedProps;
    if (p.editable) {
        editing.value = true;
        form.id = p.rawId;
        form.title = arg.event.title;
        form.description = p.description;
        form.start = arg.event.start;
        form.end = arg.event.end;
        form.color = (arg.event.borderColor || '#6366f1').replace('#', '');
        showDialog.value = true;
    } else if (p.route) {
        router.push(p.route);
    }
};

const onDateClick = (arg) => openCreate(arg.date);

const calendarOptions = reactive({
    plugins: [dayGridPlugin, timeGridPlugin, multiMonthPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'timeGridDay,timeGridWeek,dayGridMonth,multiMonthYear'
    },
    buttonText: { today: 'Today', day: 'Day', week: 'Week', month: 'Month', year: 'Year' },
    views: { multiMonthYear: { type: 'multiMonthYear', multiMonthMaxColumns: 4 } },
    height: 720,
    dayMaxEvents: 3,
    nowIndicator: true,
    weekNumbers: false,
    events: [],
    datesSet: onDatesSet,
    eventClick: onEventClick,
    dateClick: onDateClick
});

// --- reminder dialog ---
const showDialog = ref(false);
const editing = ref(false);
const form = reactive({ id: null, title: null, description: null, start: null, end: null, color: '6366f1' });

const openCreate = (date) => {
    editing.value = false;
    form.id = null; form.title = null; form.description = null;
    form.start = date instanceof Date ? date : new Date();
    form.end = null; form.color = '6366f1';
    showDialog.value = true;
};

const saveEvent = () => {
    if (!form.title || !form.start) { commonService.showError(toast, 'Title and start are required.'); return; }
    const postData = {
        id: form.id, title: form.title, description: form.description,
        start: fmt(new Date(form.start)), end: form.end ? fmt(new Date(form.end)) : null,
        color: '#' + String(form.color).replace('#', '')
    };
    const url = editing.value ? 'update-calendar-event' : 'create-calendar-event';
    commonService.genericRequest(url, 'post', true, postData).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); showDialog.value = false; loadFeed(); }
        else commonService.showError(toast, r.message);
    });
};

const deleteEvent = () => {
    commonService.genericRequest('delete-calendar-event', 'post', true, { id: form.id }).then((r) => {
        if (r.status) { commonService.showSuccess(toast, r.message); showDialog.value = false; loadFeed(); }
        else commonService.showError(toast, r.message);
    });
};

onMounted(() => {
    const now = new Date();
    window.from = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10);
    window.to = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().slice(0, 10);
    loadFeed();
});
</script>

<template>
    <div>
        <div class="card">
            <div class="flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <h5 class="m-0">Activity Calendar</h5>
                <div class="flex align-items-center gap-3 flex-wrap">
                    <span v-for="l in legend" :key="l.type" class="flex align-items-center text-sm">
                        <span class="border-round mr-1" :style="{ background: l.color, width: '0.75rem', height: '0.75rem', display: 'inline-block' }" />
                        {{ l.label }}
                    </span>
                    <Button label="New Reminder" icon="pi pi-plus" class="p-button-sm" @click="openCreate(new Date())" />
                </div>
            </div>
            <Toast />

            <div class="fc-theme-wrap">
                <FullCalendar :options="calendarOptions" />
            </div>
        </div>

        <Dialog v-model:visible="showDialog" modal :header="editing ? 'Edit Reminder' : 'New Reminder'" :style="{ width: '30rem' }">
            <div class="p-fluid">
                <div class="field">
                    <label for="rt">Title</label>
                    <InputText id="rt" v-model="form.title" />
                </div>
                <div class="field">
                    <label for="rd">Description</label>
                    <Textarea id="rd" rows="3" v-model="form.description" />
                </div>
                <div class="formgrid grid">
                    <div class="field col">
                        <label for="rs">Start</label>
                        <Calendar id="rs" v-model="form.start" showTime hourFormat="24" dateFormat="yy-mm-dd" />
                    </div>
                    <div class="field col">
                        <label for="re">End</label>
                        <Calendar id="re" v-model="form.end" showTime hourFormat="24" dateFormat="yy-mm-dd" />
                    </div>
                </div>
                <div class="field">
                    <label for="rc">Colour</label>
                    <ColorPicker id="rc" v-model="form.color" />
                </div>
            </div>
            <template #footer>
                <Button v-if="editing" label="Delete" icon="pi pi-trash" class="p-button-text p-button-danger mr-auto" @click="deleteEvent" />
                <Button label="Cancel" class="p-button-text" @click="showDialog = false" />
                <Button :label="editing ? 'Save' : 'Create'" icon="pi pi-check" @click="saveEvent" />
            </template>
        </Dialog>
    </div>
</template>

<style>
/* -------- Theme FullCalendar to the app (light + dark) --------
   FullCalendar injects its own CSS at runtime, so we drive it through its
   official CSS variables AND back the visible bits with !important so its
   default blue buttons / yellow "today" / dark text never clash. */
.fc-theme-wrap {
    /* structural */
    --fc-border-color: var(--surface-border);
    --fc-page-bg-color: var(--surface-card);
    --fc-neutral-bg-color: var(--surface-100);
    --fc-neutral-text-color: var(--text-color-secondary);
    --fc-today-bg-color: rgba(99, 102, 241, 0.12);
    --fc-now-indicator-color: #ef4444;
    --fc-list-event-hover-bg-color: var(--surface-100);
    /* toolbar buttons */
    --fc-button-text-color: var(--text-color);
    --fc-button-bg-color: var(--surface-100);
    --fc-button-border-color: var(--surface-border);
    --fc-button-hover-bg-color: var(--surface-200);
    --fc-button-hover-border-color: var(--surface-border);
    --fc-button-active-bg-color: var(--primary-color);
    --fc-button-active-border-color: var(--primary-color);
    background: var(--surface-card);
    color: var(--text-color);
}

/* text elements — always the theme text colour (never white-on-white) */
.fc-theme-wrap .fc,
.fc-theme-wrap .fc a,
.fc-theme-wrap .fc .fc-col-header-cell-cushion,
.fc-theme-wrap .fc .fc-daygrid-day-number,
.fc-theme-wrap .fc .fc-daygrid-more-link,
.fc-theme-wrap .fc .fc-multimonth-title,
.fc-theme-wrap .fc .fc-timegrid-slot-label-cushion,
.fc-theme-wrap .fc .fc-timegrid-axis-cushion,
.fc-theme-wrap .fc .fc-list-day-text,
.fc-theme-wrap .fc .fc-toolbar-title { color: var(--text-color) !important; text-decoration: none; }
.fc-theme-wrap .fc .fc-toolbar-title { font-size: 1.15rem; }

/* backgrounds/borders onto theme surfaces */
.fc-theme-wrap .fc .fc-scrollgrid,
.fc-theme-wrap .fc td,
.fc-theme-wrap .fc th { border-color: var(--surface-border) !important; }
.fc-theme-wrap .fc .fc-col-header,
.fc-theme-wrap .fc .fc-multimonth-header { background: var(--surface-50) !important; }
.fc-theme-wrap .fc .fc-daygrid-day.fc-day-today,
.fc-theme-wrap .fc .fc-timegrid-col.fc-day-today { background: rgba(99, 102, 241, 0.12) !important; }

/* toolbar buttons — force theme (FC default is blue with white text) */
.fc-theme-wrap .fc .fc-button-primary {
    background: var(--surface-100) !important;
    border-color: var(--surface-border) !important;
    color: var(--text-color) !important;
    text-transform: capitalize; box-shadow: none !important;
}
.fc-theme-wrap .fc .fc-button-primary:not(:disabled).fc-button-active,
.fc-theme-wrap .fc .fc-button-primary:not(:disabled):hover {
    background: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    color: var(--primary-color-text) !important;
}
.fc-theme-wrap .fc .fc-button-primary:disabled { opacity: 0.5; }

.fc-theme-wrap .fc .fc-event { cursor: pointer; font-size: 0.75rem; padding: 0 2px; }
.fc-theme-wrap .fc .fc-multimonth { border-color: var(--surface-border); }
.fc-theme-wrap .fc .fc-multimonth-daygrid,
.fc-theme-wrap .fc .fc-daygrid-body { background: var(--surface-card); }
</style>
