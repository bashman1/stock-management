<script setup>
import { ref } from 'vue';

import AppMenuItem from './AppMenuItem.vue';

const model = ref([
    {
        items: [
            {
                label: 'Dashboard',
                icon: 'pi pi-fw pi-desktop',
                to: '/admin'
            },
            {
                label: 'Institution Management',
                icon: 'pi pi-fw pi-building',
                items: [
                    {
                        label: 'Create Institution',
                        icon: 'pi pi-fw pi-circle',
                        to: '/create-institution'
                    },
                    {
                        label: 'View Institutions',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-institutions'
                    },

                ]
            },
            {
                label: 'User Management',
                icon: 'pi pi-fw pi-user-plus',
                items: [
                    {
                        label: 'Create User',
                        icon: 'pi pi-fw pi-circle',
                        to: '/create-user'
                    },
                    {
                        label: 'View Users',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-users'
                    },
                    {
                        label: 'Create Role',
                        icon: 'pi pi-fw pi-circle',
                        to: '/create-role'
                    },
                    {
                        label: 'View Roles',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-roles'
                    },

                ]
            },
            {
                label: 'Member Management',
                icon: 'pi pi-fw pi-users',
                items: [
                    {
                        label: 'Create Member',
                        icon: 'pi pi-fw pi-circle',
                        to: '/create-member'
                    },
                    {
                        label: 'View Members',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-members'
                    },

                ]
            },
            {
                label: 'Bulk Upload',
                icon: 'pi pi-fw pi-briefcase',
                items: [
                    {
                        label: 'Upload Members',
                        icon: 'pi pi-fw pi-circle',
                        to: '/upload-members'
                    },
                    {
                        label: 'View Uploaded Members',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-uploaded-members'
                    },

                ]
            },
            {
                label: 'Collections',
                icon: 'pi pi-fw pi-money-bill',
                items:[
                    {
                        label: 'Collect Money',
                        icon: 'pi pi-fw pi-circle',
                        to: '/collect-money'
                    },
                    {
                        label: 'View Collections',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-collected-money'
                    },
                    {
                        label: 'Approve Collections',
                        icon: 'pi pi-fw pi-circle',
                        to: '/approve-collections'
                    },
                    {
                        label: 'Approved Collections',
                        icon: 'pi pi-fw pi-circle',
                        to: '/approved-collections'
                    },
                ]
            },
            {
                label: 'Commissions',
                icon: 'pi pi-dollar',
                items:[
                    {
                        label: 'View Commission',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-commissions'
                    }
                ]
            },
            {
                label: 'Reports',
                icon: 'pi pi-fw pi-folder',
                items:[

                ]
            },
            {
                label: 'Stock Management',
                icon: 'pi pi-dollar',
                items:[
                    {
                        label: 'Create Product',
                        icon: 'pi pi-fw pi-circle',
                        to: '/create-product'
                    }
                ]
            },
            // {
            //     label: 'Sign Out',
            //     icon: 'pi pi-fw pi-desktop',
            //     to: '/admin'
            // },

            // {
            //     label: 'Roles Management',
            //     icon: 'pi pi-fw pi-shield'
            // }
        ]
    },
    // {
    //     label: 'UI Components',
    //     items: [
    //         { label: 'Form Layout', icon: 'pi pi-fw pi-id-card', to: '/uikit/formlayout' },
    //         { label: 'Input', icon: 'pi pi-fw pi-check-square', to: '/uikit/input' },
    //         { label: 'Float Label', icon: 'pi pi-fw pi-bookmark', to: '/uikit/floatlabel' },
    //         { label: 'Invalid State', icon: 'pi pi-fw pi-exclamation-circle', to: '/uikit/invalidstate' },
    //         { label: 'Button', icon: 'pi pi-fw pi-mobile', to: '/uikit/button', class: 'rotated-icon' },
    //         { label: 'Table', icon: 'pi pi-fw pi-table', to: '/uikit/table' },
    //         { label: 'List', icon: 'pi pi-fw pi-list', to: '/uikit/list' },
    //         { label: 'Tree', icon: 'pi pi-fw pi-share-alt', to: '/uikit/tree' },
    //         { label: 'Panel', icon: 'pi pi-fw pi-tablet', to: '/uikit/panel' },
    //         { label: 'Overlay', icon: 'pi pi-fw pi-clone', to: '/uikit/overlay' },
    //         { label: 'Media', icon: 'pi pi-fw pi-image', to: '/uikit/media' },
    //         { label: 'Menu', icon: 'pi pi-fw pi-bars', to: '/uikit/menu', preventExact: true },
    //         { label: 'Message', icon: 'pi pi-fw pi-comment', to: '/uikit/message' },
    //         { label: 'File', icon: 'pi pi-fw pi-file', to: '/uikit/file' },
    //         { label: 'Chart', icon: 'pi pi-fw pi-chart-bar', to: '/uikit/charts' },
    //         { label: 'Misc', icon: 'pi pi-fw pi-circle', to: '/uikit/misc' }
    //     ]
    // },
    // {
    //     label: 'Prime Blocks',
    //     items: [
    //         { label: 'Free Blocks', icon: 'pi pi-fw pi-eye', to: '/blocks', badge: 'NEW' },
    //         { label: 'All Blocks', icon: 'pi pi-fw pi-globe', url: 'https://www.primefaces.org/primeblocks-vue', target: '_blank' }
    //     ]
    // },
    // {
    //     label: 'Utilities',
    //     items: [
    //         { label: 'PrimeIcons', icon: 'pi pi-fw pi-prime', to: '/utilities/icons' },
    //         { label: 'PrimeFlex', icon: 'pi pi-fw pi-desktop', url: 'https://www.primefaces.org/primeflex/', target: '_blank' }
    //     ]
    // },
    // {
    //     label: 'Pages',
    //     icon: 'pi pi-fw pi-briefcase',
    //     to: '/pages',
    //     items: [
    //         {
    //             label: 'Landing',
    //             icon: 'pi pi-fw pi-globe',
    //             to: '/landing'
    //         },
    //         {
    //             label: 'Auth',
    //             icon: 'pi pi-fw pi-user',
    //             items: [
    //                 {
    //                     label: 'Login',
    //                     icon: 'pi pi-fw pi-sign-in',
    //                     to: '/auth/login'
    //                 },
    //                 {
    //                     label: 'Error',
    //                     icon: 'pi pi-fw pi-times-circle',
    //                     to: '/auth/error'
    //                 },
    //                 {
    //                     label: 'Access Denied',
    //                     icon: 'pi pi-fw pi-lock',
    //                     to: '/auth/access'
    //                 }
    //             ]
    //         },
    //         {
    //             label: 'Crud',
    //             icon: 'pi pi-fw pi-pencil',
    //             to: '/pages/crud'
    //         },
    //         {
    //             label: 'Timeline',
    //             icon: 'pi pi-fw pi-calendar',
    //             to: '/pages/timeline'
    //         },
    //         {
    //             label: 'Not Found',
    //             icon: 'pi pi-fw pi-exclamation-circle',
    //             to: '/pages/notfound'
    //         },
    //         {
    //             label: 'Empty',
    //             icon: 'pi pi-fw pi-circle-off',
    //             to: '/pages/empty'
    //         }
    //     ]
    // },
    // {
    //     label: 'Hierarchy',
    //     items: [
    //         {
    //             label: 'Submenu 1',
    //             icon: 'pi pi-fw pi-bookmark',
    //             items: [
    //                 {
    //                     label: 'Submenu 1.1',
    //                     icon: 'pi pi-fw pi-bookmark',
    //                     items: [
    //                         { label: 'Submenu 1.1.1', icon: 'pi pi-fw pi-bookmark' },
    //                         { label: 'Submenu 1.1.2', icon: 'pi pi-fw pi-bookmark' },
    //                         { label: 'Submenu 1.1.3', icon: 'pi pi-fw pi-bookmark' }
    //                     ]
    //                 },
    //                 {
    //                     label: 'Submenu 1.2',
    //                     icon: 'pi pi-fw pi-bookmark',
    //                     items: [{ label: 'Submenu 1.2.1', icon: 'pi pi-fw pi-bookmark' }]
    //                 }
    //             ]
    //         },
    //         {
    //             label: 'Submenu 2',
    //             icon: 'pi pi-fw pi-bookmark',
    //             items: [
    //                 {
    //                     label: 'Submenu 2.1',
    //                     icon: 'pi pi-fw pi-bookmark',
    //                     items: [
    //                         { label: 'Submenu 2.1.1', icon: 'pi pi-fw pi-bookmark' },
    //                         { label: 'Submenu 2.1.2', icon: 'pi pi-fw pi-bookmark' }
    //                     ]
    //                 },
    //                 {
    //                     label: 'Submenu 2.2',
    //                     icon: 'pi pi-fw pi-bookmark',
    //                     items: [{ label: 'Submenu 2.2.1', icon: 'pi pi-fw pi-bookmark' }]
    //                 }
    //             ]
    //         }
    //     ]
    // },
    // {
    //     label: 'Get Started',
    //     items: [
    //         {
    //             label: 'Documentation',
    //             icon: 'pi pi-fw pi-question',
    //             to: '/documentation'
    //         },
    //         {
    //             label: 'View Source',
    //             icon: 'pi pi-fw pi-search',
    //             url: 'https://github.com/primefaces/sakai-vue',
    //             target: '_blank'
    //         },
    //         {
    //             label: 'Nuxt Version',
    //             url: 'https://github.com/primefaces/sakai-nuxt',
    //             icon: 'pi pi-fw pi-star'
    //         }
    //     ]
    // }
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
<!--        <template>-->
<!--            <app-menu-item :item="item" :index="i"></app-menu-item>-->
<!--            <li class="menu-separator">Log Out</li>-->
<!--        </template>-->
<!--       <li>-->
<!--            <a href="https://www.primefaces.org/primeblocks-vue/#/" target="_blank">-->
<!--                <i class="pi pi-sign-out"></i>-->
<!--                Log out-->
<!--            </a>-->
<!--        </li>-->
    </ul>
</template>

<style lang="scss" scoped></style>
