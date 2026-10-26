import { toRefs, reactive, computed } from 'vue';

const STORAGE_KEY = 'layoutConfig';

// Persisted appearance settings (theme + dark mode + scale) survive reloads.
const loadPersisted = () => {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}') || {};
    } catch (e) {
        return {};
    }
};
const persisted = loadPersisted();

const layoutConfig = reactive({
    ripple: false,
    darkTheme: typeof persisted.darkTheme === 'boolean' ? persisted.darkTheme : false,
    inputStyle: 'outlined',
    menuMode: 'static',
    theme: persisted.theme || 'lara-light-indigo',
    scale: persisted.scale || 14,
    activeMenuItem: null
});

const savePersisted = () => {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
            theme: layoutConfig.theme,
            darkTheme: layoutConfig.darkTheme,
            scale: layoutConfig.scale
        }));
    } catch (e) {
        /* localStorage unavailable — ignore */
    }
};

// Apply the persisted theme + scale to the DOM on app start (called from App.vue).
export function applyPersistedAppearance() {
    document.documentElement.style.fontSize = layoutConfig.scale + 'px';
    const link = document.getElementById('theme-css');
    if (link) {
        const href = link.getAttribute('href') || '';
        const newHref = href.replace(/themes\/[^/]+\//, `themes/${layoutConfig.theme}/`);
        if (newHref !== href) {
            link.setAttribute('href', newHref);
        }
    }
}

const layoutState = reactive({
    staticMenuDesktopInactive: false,
    overlayMenuActive: false,
    profileSidebarVisible: false,
    configSidebarVisible: false,
    staticMenuMobileActive: false,
    menuHoverActive: false
});

export function useLayout() {
    const changeThemeSettings = (theme, darkTheme) => {
        layoutConfig.darkTheme = darkTheme;
        layoutConfig.theme = theme;
        savePersisted();
    };

    const setScale = (scale) => {
        layoutConfig.scale = scale;
        savePersisted();
    };

    const setActiveMenuItem = (item) => {
        layoutConfig.activeMenuItem = item.value || item;
    };

    const onMenuToggle = () => {
        if (layoutConfig.menuMode === 'overlay') {
            layoutState.overlayMenuActive = !layoutState.overlayMenuActive;
        }

        if (window.innerWidth > 991) {
            layoutState.staticMenuDesktopInactive = !layoutState.staticMenuDesktopInactive;
        } else {
            layoutState.staticMenuMobileActive = !layoutState.staticMenuMobileActive;
        }
    };

    const isSidebarActive = computed(() => layoutState.overlayMenuActive || layoutState.staticMenuMobileActive);

    const isDarkTheme = computed(() => layoutConfig.darkTheme);

    return { layoutConfig: toRefs(layoutConfig), layoutState: toRefs(layoutState), changeThemeSettings, setScale, onMenuToggle, isSidebarActive, isDarkTheme, setActiveMenuItem };
}
