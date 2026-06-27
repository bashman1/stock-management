<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useLayout } from '@/layout/composables/layout';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const { layoutConfig, onMenuToggle } = useLayout();

const outsideClickListener = ref(null);
const topbarMenuActive = ref(false);
const op2 = ref(null);
const router = useRouter();
const commonService = new CommonService();
const toast = useToast();
const storageData = ref({})


onMounted(() => {
    bindOutsideClickListener();
    getStorageData();
});

onBeforeUnmount(() => {
    unbindOutsideClickListener();
});

const logoUrl = computed(() => {
    // return `layout/images/${layoutConfig.darkTheme.value ? 'logo-white' : 'logo-dark'}.svg`;
    // return `demo/images/Smart-Collect-logo-black-removebg.png`;
    // return `demo/images/logo-sm-home-1.png`;
    return `demo/images/SmarCollectlogo-removebg-preview.png`;


});

const onTopBarMenuButton = () => {
    topbarMenuActive.value = !topbarMenuActive.value;
    op2.value.toggle(event);
};
const onSettingsClick = () => {
    topbarMenuActive.value = false;
    router.push('/documentation');
};
const topbarMenuClasses = computed(() => {
    return {
        'layout-topbar-menu-mobile-active': topbarMenuActive.value
    };
});

const bindOutsideClickListener = () => {
    if (!outsideClickListener.value) {
        outsideClickListener.value = (event) => {
            if (isOutsideClicked(event)) {
                topbarMenuActive.value = false;
            }
        };
        document.addEventListener('click', outsideClickListener.value);
    }
};
const unbindOutsideClickListener = () => {
    if (outsideClickListener.value) {
        document.removeEventListener('click', outsideClickListener);
        outsideClickListener.value = null;
    }
};
const isOutsideClicked = (event) => {
    if (!topbarMenuActive.value) return;

    const sidebarEl = document.querySelector('.layout-topbar-menu');
    const topbarEl = document.querySelector('.layout-topbar-menu-button');

    return !(sidebarEl.isSameNode(event.target) || sidebarEl.contains(event.target) || topbarEl.isSameNode(event.target) || topbarEl.contains(event.target));
};

const logOut = () => {
    commonService.genericRequest('log-out', 'get', true, {}).then((response) => {
        if (response.status) {
            commonService.removeStorage()
            commonService.redirect(router,"/auth/login");
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getStorageData=()=>{
    // alert(JSON.stringify(commonService.getStorage()))
    storageData.value = commonService.getStorage()
}

// --- Topbar context (user / role / business / branch / license) ----------
const userData = computed(() => storageData.value?.userData || {});
const license = computed(() => storageData.value?.license || {});

const userName = computed(() =>
    [userData.value.first_name, userData.value.last_name, userData.value.other_name]
        .filter(Boolean)
        .join(' ') || '—'
);
const roleName = computed(() => userData.value.role_name || '—');
const isLaraLightIndigo = computed(() => layoutConfig.theme.value === 'lara-light-indigo');
const businessName = computed(() => userData.value.institution_name || '—');
const branchName = computed(() => userData.value.branch_name || '—');
const licensePackage = computed(() => license.value.package_name || '—');

// License validity → status tag colour + label, with details for the tooltip
const licenseDaysLeft = computed(() => {
    if (!license.value.end_date) return null;
    const end = new Date(license.value.end_date);
    const now = new Date();
    if (isNaN(end.getTime())) return null;
    return Math.ceil((end - now) / (1000 * 60 * 60 * 24));
});
const licenseSeverity = computed(() => {
    const d = licenseDaysLeft.value;
    if (d === null) return 'info';
    if (d < 0) return 'danger';
    if (d <= 7) return 'warning';
    return 'success';
});
const licenseStatusLabel = computed(() => {
    const d = licenseDaysLeft.value;
    if (d === null) return 'N/A';
    if (d < 0) return 'Expired';
    if (d === 0) return 'Ends today';
    return `${d} day${d === 1 ? '' : 's'} left`;
});
const licenseTooltip = computed(() => {
    const parts = [];
    if (license.value.package_name) parts.push(`Package: ${license.value.package_name}`);
    if (license.value.duration) parts.push(`Plan: ${license.value.duration}`);
    if (license.value.start_date) parts.push(`Started: ${license.value.start_date}`);
    if (license.value.end_date) parts.push(`Ends: ${license.value.end_date}`);
    return parts.join('\n');
});


</script>

<template>
    <div class="layout-topbar">
        <router-link to="/admin" class="layout-topbar-logo">
<!--            <img :src="logoUrl" alt="logo" />-->
            <img :src="logoUrl" alt="logo" class="w-15rem h-4rem" />
<!--            <span>SAKAI</span>-->
        </router-link>

        <button class="p-link layout-menu-button layout-topbar-button" @click="onMenuToggle()">
            <i class="pi pi-bars"></i>
        </button>

        <div class="topbar-context hidden lg:flex align-items-center gap-2 ml-3 flex-1 min-w-0 overflow-x-auto">
            <div class="context-pill" title="Signed-in user">
                <i class="pi pi-user"></i>
                <span class="context-label">User</span>
                <span class="context-value"  :class="{ 'text-800': isLaraLightIndigo }">{{ userName }}</span>
            </div>

            <div class="context-pill" title="Role">
                <i class="pi pi-id-card"></i>
                <span class="context-label">Role</span>
                <span class="context-value" :class="{ 'text-800': isLaraLightIndigo }">{{ roleName }}</span>
            </div>

            <div class="context-pill" title="Business">
                <i class="pi pi-building"></i>
                <span class="context-label">Business</span>
                <span class="context-value" :class="{ 'text-800': isLaraLightIndigo }">{{ businessName }}</span>
            </div>

            <div class="context-pill" title="Branch">
                <i class="pi pi-sitemap"></i>
                <span class="context-label">Branch</span>
                <span class="context-value" :class="{ 'text-800': isLaraLightIndigo }">{{ branchName }}</span>
            </div>

            <div class="context-pill" :title="licenseTooltip">
                <i class="pi pi-verified"></i>
                <span class="context-label">License</span>
                <span class="context-value" :class="{ 'text-800': isLaraLightIndigo }">{{ licensePackage }}</span>
                <Tag :value="licenseStatusLabel" :severity="licenseSeverity" class="ml-1"></Tag>
            </div>
        </div>

        <button class="p-link layout-topbar-menu-button layout-topbar-button" @click="onTopBarMenuButton()">
            <i class="pi pi-ellipsis-v"></i>
        </button>

        <div class="layout-topbar-menu" :class="topbarMenuClasses">
            <!-- <button @click="onTopBarMenuButton()" class="p-link layout-topbar-button">
                <i class="pi pi-calendar"></i>
                <span>Calendar</span>
            </button> -->
            <!-- <button @click="onTopBarMenuButton()" class="p-link layout-topbar-button">
                <i class="pi pi-user"></i>
                <span>Profile</span>
            </button> -->
            <button @click="logOut()" class="p-link layout-topbar-button">
                <i class="pi pi-sign-out"></i>
                <span>Log out</span>
            </button>
            <OverlayPanel ref="op2" appendTo="body" :showCloseIcon="true" id="overlay_panel" style="width: 450px">
                            <!-- <DataTable :value="products" v-model:selection="selectedProduct" selectionMode="single" :paginator="true" :rows="5" @row-select="onProductSelect" responsiveLayout="scroll">
                                <Column field="name" header="Name" :sortable="true" headerStyle="min-width:12rem;"></Column>
                                <Column header="Image" headerStyle="min-width:5rem;">
                                    <template #body="slotProps">
                                        <img :src="'demo/images/product/' + slotProps.data.image" :alt="slotProps.data.image" width="50" class="shadow-2" />
                                    </template>
                                </Column>
                                <Column field="price" header="Price" :sortable="true" headerStyle="min-width:8rem;">
                                    <template #body="slotProps">
                                        {{ formatCurrency(slotProps.data.price) }}
                                    </template>
                                </Column>
                            </DataTable> -->
                            <Button @click="logOut" icon="pi pi-sign-out" label="Log out" class="p-button-outlined p-button-danger mr-2 mb-2" />
             </OverlayPanel>
            <!-- <button @click="onSettingsClick()" class="p-link layout-topbar-button">
                <i class="pi pi-cog"></i>
                <span>Settings</span>
            </button> -->
        </div>
        <Toast />
    </div>
</template>

<style lang="scss" scoped>
.topbar-context {
    scrollbar-width: thin;
}

.context-pill {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.75rem;
    border-radius: 8px;
    background: var(--surface-50);
    border: 1px solid var(--surface-border);
    white-space: nowrap;

    i {
        color: var(--primary-color);
        font-size: 1rem;
    }
}

.context-label {
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    color: var(--primary-color);
}

.context-value {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text-color);
    max-width: 14rem;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
