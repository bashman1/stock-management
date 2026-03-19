<script setup>
import { ref, computed } from 'vue';
import CommonService from '@/service/CommonService';
import AppMenuItem from './AppMenuItem.vue';

const commonService = new CommonService();

// Cache permissions as a Set for O(1) lookups instead of O(n) array iteration
// Load once during component initialization (uses CommonService's internal cache)
const permissionsSet = new Set(
    commonService.getPermissions().map(p => p.name)
);

// Optimized permission check using cached Set
const hasPermission = (permission) => {
    // If no permission required, allow access
    if (!permission) return true;
    return permissionsSet.has(permission);
};

// Get user data for dynamic routes
const userData = commonService.getStorage()?.userData;

// Declarative menu configuration - keeps all menu items in one place
const menuConfig = [
    {
        key: 'pointOfSale',
        label: 'Point Of Sale',
        icon: 'pi-credit-card',
        to: '/point-of-sale',
        permission: 'PointOfSale',
        isSubmenu: false
    },
    {
        key: 'sales',
        label: 'sales',
        icon: 'pi-flag',
        to: '/view-sales',
        permission: 'ViewSales',
        isSubmenu: false
    },
    {
        key: 'institution',
        label: 'Institution Management',
        icon: 'pi pi-fw pi-building',
        permission: null, // Always show if has items
        items: [
            { label: 'Create Institution', permission: 'CanCreateInstitution', to: '/create-institution' },
            { label: 'View Institutions', permission: 'CanViewInstitution', to: '/view-institutions' },
            { label: 'Approve Institutions', permission: 'ApproveInstitution', to: '/approve-business' },
            { label: 'Create Branch', permission: 'CanCreateBranch', to: '/create-branch' },
            { label: 'View Branches', permission: 'ViewBranches', to: '/view-branches' }
        ]
    },
    {
        key: 'user',
        label: 'User Management',
        icon: 'pi pi-fw pi-user-plus',
        permission: null,
        items: [
            { label: 'Create User', permission: 'CreateUser', to: '/create-user' },
            { label: 'View Users', permission: 'ViewUsers', to: '/view-users' },
            { label: 'Create Role', permission: 'CreatedRole', to: '/create-role' },
            { label: 'View Roles', permission: 'ViewRoles', to: '/view-roles' },
            { label: 'Create Default Role', permission: 'CreateDefaultRoles', to: '/create-default-role' },
            { label: 'View Default Roles', permission: 'ViewDefaultRoles', to: '/view-default-roles' }
        ]
    },
    {
        key: 'member',
        label: 'Member Management',
        icon: 'pi pi-fw pi-users',
        permission: null,
        items: [
            { label: 'Create Member', permission: 'CreateMember', to: '/create-member' },
            { label: 'View Members', permission: 'ViewMember', to: '/view-members' }
        ]
    },
    {
        key: 'bulk',
        label: 'Bulk Upload',
        icon: 'pi pi-fw pi-briefcase',
        permission: null,
        items: [
            { label: 'Upload Members', permission: 'UploadMembers', to: '/upload-members' },
            { label: 'View Uploaded Members', permission: 'ViewUploadedMembers', to: '/view-uploaded-members' },
            { label: 'Upload Products', permission: 'CanUploadBulkProducts', to: '/upload-products' },
            { label: 'View Upload Products', permission: 'ViewUploadBulkProductsBatch', to: '/view-uploaded-products' },
            { label: 'Upload Sales', permission: 'CanUploadBulkSales', to: '/upload-sales' },
            { label: 'View Uploaded Sales', permission: 'ViewUploadBulkSalesBatch', to: '/view-uploaded-sales' },
            { label: 'Upload Buildings', permission: 'BulkUploadBuildings', to: '/bulk-upload-buildings' },
            { label: 'Upload Building Units', permission: 'BulkUploadBuildingUnits', to: '/bulk-upload-building-units' },
            { label: 'Upload Tenants Units', permission: 'BulkUploadTenantsUnits', to: '/bulk-upload-tenants-room' },
            { label: 'Approve Buildings Batch', permission: 'ApproveBuildingBatch', to: '/view-building-uploaded-batches' },
            { label: 'Approve Building Units Batch', permission: 'ApproveBuildingUnitBatch', to: '/view-building-units-uploaded-batches' },
            { label: 'Approve Tenants Units Batch', permission: 'ApproveTenantUnitBatch', to: '/view-tenants-rooms-uploaded-batches' }
        ]
    },
    {
        key: 'collection',
        label: 'Collections',
        icon: 'pi pi-fw pi-money-bill',
        permission: null,
        items: [
            { label: 'Collect Money', permission: 'CollectMoney', to: '/collect-money' },
            { label: 'View Collections', permission: 'ViewCollection', to: '/view-collected-money' },
            { label: 'Approve Collections', permission: 'ApproveCollection', to: '/approve-collections' },
            { label: 'Approved Collections', permission: 'ApprovedCollection', to: '/approved-collections' }
        ]
    },
    {
        key: 'commission',
        label: 'Commissions',
        icon: 'pi-dollar',
        permission: null,
        items: [
            { label: 'View Commission', permission: 'ViewCommission', to: '/view-commissions' }
        ]
    },
    {
        key: 'report',
        label: 'Reports',
        icon: 'pi pi-fw pi-folder',
        permission: null,
        items: [
            { label: 'Products Report', permission: 'ViewProductsReport', to: '/products-report' },
            { label: 'Sales Transaction Report', permission: 'ViewSalesReport', to: '/sales-report' },
            { label: 'Sales Report', permission: 'ViewSalesReport', to: '/sales-item-report' },
            { label: 'Cash Book', permission: 'ViewCashBook', to: '/cash-book' },
            { label: 'VAT Payable', permission: 'ViewVatPayable', to: '/vat-payable-report' },
            { label: 'Rent Arrears Report', permission: 'RentArrearsReport', to: '/rental-arrears-report' },
            { label: 'Land Load Payment Report', permission: 'LandLoadPaymentReport', to: '/land-load-payment-report' }
        ]
    },
    {
        key: 'invoice',
        label: 'Invoices',
        icon: 'pi pi-file',
        permission: null,
        items: [
            { label: 'Create Invoice', permission: 'CanCreateInvoice', to: '/proforma-invoice' },
            { label: 'View Invoices', permission: 'ViewInvoice', to: '/View-proforma-invoices' }
        ]
    },
    {
        key: 'stock',
        label: 'Inventory Management',
        icon: 'pi-flag',
        permission: null,
        items: [
            { label: 'Create Product', permission: 'CreateProduct', to: '/create-product' },
            { label: 'View Products', permission: 'ViewProducts', to: '/view-product' }
        ]
    },
    {
        key: 'chartOfAccount',
        label: 'Chart Of Accounts',
        icon: 'pi-bitcoin',
        permission: null,
        items: [
            { label: 'View Chart Of Accounts', permission: 'ViewChartOfAccounts', to: '/chart-of-accounts' },
            { label: 'Create Ledger', permission: 'CreateLedger', to: '/create-ledger' },
            { label: 'GL accounts', permission: 'ViewGlAccounts', to: '/gl-accounts' },
            { label: 'GL Debit/Credit', permission: 'DebitCredit', to: '/debit-credit-gl' },
            { label: 'GL overview', permission: 'GlOverView', to: '/gl-overview' },
            { label: 'Control Accounts', permission: 'ViewCntrlParams', to: '/cntrl-params' },
            { label: 'Income Statement', permission: 'ViewIncomeStatement', to: '/income-statement' },
            { label: 'Balance Sheet', permission: 'ViewBalanceSheet', to: '/gl-balance-sheet' },
            { label: 'Expenses', permission: 'CreateExpenses', to: '/create-expense' }
        ]
    },
    {
        key: 'payableReceivable',
        label: 'Payable/Receivable',
        icon: 'pi-wallet',
        permission: null,
        items: [
            { label: 'Receivable', permission: 'ViewReceivables', to: '/view-receivables' },
            { label: 'Payable', permission: 'ViewPayables', to: '/view-payable' }
        ]
    },
    {
        key: 'investment',
        label: 'Investment',
        icon: 'pi-box',
        permission: null,
        items: [
            { label: 'Create Project', permission: 'CanCreateProject', to: '/create-project' },
            { label: 'View Project', permission: 'ViewProjects', to: '/view-projects' },
            { label: 'Invest', permission: 'CreateInvestment', to: '/invest' },
            { label: 'View Investments', permission: 'ViewInvestment', to: '/view-invest' }
        ]
    },
    {
        key: 'property',
        label: 'Estate Management',
        icon: 'pi-building',
        permission: null,
        items: [
            { label: 'Create Building', permission: 'CanCreateBuilding', to: '/create-building' },
            { label: 'View Building', permission: 'ViewBuildings', to: '/view-buildings' },
            { label: 'Create Building Unit', permission: 'CanCreateBuildingUnit', to: '/create-building-unit' },
            { label: 'View Building Units', permission: 'ViewBuildingUnits', to: '/view-building-units' },
            { label: 'Rent Building Units', permission: 'RentBuildingUnits', to: '/rent-building-units' },
            { label: 'View Rent Building Units', permission: 'ViewRentBuildingUnits', to: '/view-building-rentals' }
        ]
    },
    {
        key: 'license',
        label: 'License',
        icon: 'pi-verified',
        permission: null,
        items: [
            { label: 'Create License Package', permission: 'CanCreateLicensePackage', to: '/create-license-package' },
            { label: 'View License Packages', permission: 'CanViewLicensePackages', to: '/view-license-packages' }
        ]
    },
    {
        key: 'account',
        label: 'Account',
        icon: 'pi-user',
        permission: null,
        items: [
            { label: 'Institution Profile', permission: 'ViewInstitutionProfile', to: '/institution-profile/' + (userData?.institution_id || '') },
            { label: 'User Profile', permission: 'ViewUserProfile', to: '/user-profile/' + (userData?.id || '') },
            { label: 'System Logs', permission: 'ViewSystemLogs', to: '/audit-logs' }
        ]
    },
    {
        key: 'message',
        label: 'Messages',
        icon: 'pi-envelope',
        permission: null,
        items: [
            { label: 'Compose Email', permission: 'ComposeMessage', to: '/upload-mail-receivers' },
            { label: 'Compose SMS', permission: 'ComposeMessage', to: '/upload-mail-receivers' }
        ]
    },
    {
        key: 'logout',
        label: 'Log Out',
        icon: 'pi-sign-out',
        to: '/logout',
        permission: null,
        isSubmenu: false
    }
];

// Build menu items with permission filtering
const buildMenuItems = (config) => {
    const items = [];

    // Always add Dashboard first
    items.push({ label: 'Dashboard', icon: 'pi pi-fw pi-desktop', to: '/admin' });

    // Process each menu config in order
    for (const menu of config) {
        // Handle direct menu items (non-submenus)
        if (!menu.items) {
            if (!menu.permission || hasPermission(menu.permission)) {
                items.push({
                    label: menu.label,
                    icon: menu.icon.startsWith('pi ') ? menu.icon : `pi pi-fw ${menu.icon}`,
                    to: menu.to
                });
            }
            continue;
        }

        // Handle submenus - filter items by permission
        const filteredItems = menu.items
            .filter(item => hasPermission(item.permission))
            .map(item => ({
                label: item.label,
                icon: 'pi pi-fw pi-circle',
                to: item.to
            }));

        // Only add submenu if it has visible items
        if (filteredItems.length > 0) {
            items.push({
                label: menu.label,
                icon: menu.icon.startsWith('pi ') ? menu.icon : `pi pi-fw ${menu.icon}`,
                items: filteredItems
            });
        }
    }

    return items;
};

// Computed menu model - rebuilds only when needed
const model = computed(() => {
    return [{ items: buildMenuItems(menuConfig) }];
});
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>
