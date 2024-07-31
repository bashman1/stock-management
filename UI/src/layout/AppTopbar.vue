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


onMounted(() => {
    bindOutsideClickListener();
});

onBeforeUnmount(() => {
    unbindOutsideClickListener();
});

const logoUrl = computed(() => {
    // return `layout/images/${layoutConfig.darkTheme.value ? 'logo-white' : 'logo-dark'}.svg`;
    // return `demo/images/Smart-Collect-logo-black-removebg.png`;
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

        <button class="p-link layout-topbar-menu-button layout-topbar-button" @click="onTopBarMenuButton()">
            <i class="pi pi-ellipsis-v"></i>
        </button>

        <div class="layout-topbar-menu" :class="topbarMenuClasses">
            <button @click="onTopBarMenuButton()" class="p-link layout-topbar-button">
                <i class="pi pi-calendar"></i>
                <span>Calendar</span>
            </button>
            <button @click="onTopBarMenuButton()" class="p-link layout-topbar-button">
                <i class="pi pi-user"></i>
                <span>Profile</span>
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

<style lang="scss" scoped></style>
