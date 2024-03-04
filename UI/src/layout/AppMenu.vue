<script setup>
import { ref } from 'vue';
import CommonService from '@/service/CommonService'

import AppMenuItem from './AppMenuItem.vue';

const commonService = new CommonService();

const institutionMenu = ref(
    {
        label: 'Institution Management',
        icon: 'pi pi-fw pi-building',
        items: [

        ]
    },
);

const userMenu = ref({
    label: 'User Management',
    icon: 'pi pi-fw pi-user-plus',
    items: []
});

const memberMenu = ref(
    {
        label: 'Member Management',
        icon: 'pi pi-fw pi-users',
        items: []
    },
);

const bulkMenu = ref(
    {
        label: 'Bulk Upload',
        icon: 'pi pi-fw pi-briefcase',
        items: []
    }
)

const collectionMenu = ref(
    {
        label: 'Collections',
        icon: 'pi pi-fw pi-money-bill',
        items: []
    }
)

const commissionMenu = ref(
    {
        label: 'Commissions',
        icon: 'pi pi-dollar',
        items: []
    }
)

const reportMenu = ref(
    {
        label: 'Reports',
        icon: 'pi pi-fw pi-folder',
        items: []
    }
)

const stockMenu = ref(
    {
        label: 'Inventory Management',
        icon: 'pi pi-dollar',
        items: []
    }
)


if (commonService.checkPermissions('CanCreateInstitution')) {
    institutionMenu.value.items.push({ label: 'Create Institution', icon: 'pi pi-fw pi-circle', to: '/create-institution' });
}
if (commonService.checkPermissions('CanViewInstitution')) {
    institutionMenu.value.items.push({
        label: 'View Institutions',
        icon: 'pi pi-fw pi-circle',
        to: '/view-institutions'
    },);
}

if (commonService.checkPermissions('CreateUser')) {
    userMenu.value.items.push({
        label: 'Create User',
        icon: 'pi pi-fw pi-circle',
        to: '/create-user'
    });
}

if (commonService.checkPermissions('ViewUsers')) {
    userMenu.value.items.push({
                        label: 'View Users',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-users'
                    });
}

if (commonService.checkPermissions('CreatedRole')) {
    userMenu.value.items.push(                    {
                        label: 'Create Role',
                        icon: 'pi pi-fw pi-circle',
                        to: '/create-role'
                    });
}

if (commonService.checkPermissions('ViewRoles')) {
    userMenu.value.items.push(
        {
                        label: 'View Roles',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-roles'
                    }
    );
}
if (commonService.checkPermissions('CreateMember')) {
    memberMenu.value.items.push(
        {
                        label: 'Create Member',
                        icon: 'pi pi-fw pi-circle',
                        to: '/create-member'
                    }
    );
}

if (commonService.checkPermissions('ViewMember')) {
    memberMenu.value.items.push(
        {
                        label: 'View Members',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-members'
                    }
    );
}

if (commonService.checkPermissions('UploadMembers')) {
    bulkMenu.value.items.push(
        {
                        label: 'Upload Members',
                        icon: 'pi pi-fw pi-circle',
                        to: '/upload-members'
                    }
    );
}

if (commonService.checkPermissions('ViewUploadedMembers')) {
    bulkMenu.value.items.push(
        {
                        label: 'View Uploaded Members',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-uploaded-members'
                    }
    );
}

if (commonService.checkPermissions('CollectMoney')) {
    collectionMenu.value.items.push(
        {
                        label: 'Collect Money',
                        icon: 'pi pi-fw pi-circle',
                        to: '/collect-money'
                    }
    );
}

if (commonService.checkPermissions('ViewCollection')) {
    collectionMenu.value.items.push(
        {
                        label: 'View Collections',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-collected-money'
                    }
    );
}

if (commonService.checkPermissions('ApproveCollection')) {
    collectionMenu.value.items.push(
        {
                        label: 'Approve Collections',
                        icon: 'pi pi-fw pi-circle',
                        to: '/approve-collections'
                    }
    );
}

if (commonService.checkPermissions('ApprovedCollection')) {
    collectionMenu.value.items.push(
        {
                        label: 'Approved Collections',
                        icon: 'pi pi-fw pi-circle',
                        to: '/approved-collections'
                    }
    );
}

if (commonService.checkPermissions('ViewCommission')) {
    commissionMenu.value.items.push(
        {
                        label: 'View Commission',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-commissions'
                    }
    );
}

if (commonService.checkPermissions('CreateProduct')) {
    stockMenu.value.items.push(
        {
                        label: 'Create Product',
                        icon: 'pi pi-fw pi-circle',
                        to: '/create-product'
                    }
    );
}

if (commonService.checkPermissions('ViewProducts')) {
    stockMenu.value.items.push(
        {
                        label: 'View Products',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-product'
                    }
    );
}

if (commonService.checkPermissions('PointOfSale')) {
    stockMenu.value.items.push(
        {
                        label: 'Point Of Sale',
                        icon: 'pi pi-fw pi-circle',
                        to: '/point-of-sale'
                    }
    );
}

if (commonService.checkPermissions('ViewSales')) {
    stockMenu.value.items.push(
        {
                        label: 'Sales',
                        icon: 'pi pi-fw pi-circle',
                        to: '/view-sales'
                    }
    );
}



const model = ref([
    {
        items: [
            {
                label: 'Dashboard',
                icon: 'pi pi-fw pi-desktop',
                to: '/admin'
            },
            // {
            //     label: 'Institution Management',
            //     icon: 'pi pi-fw pi-building',
            //     items: [
            //         {
            //             label: 'Create Institution',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/create-institution'
            //         },
            //         {
            //             label: 'View Institutions',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/view-institutions'
            //         },

            //     ]
            // },
            // {
            //     label: 'User Management',
            //     icon: 'pi pi-fw pi-user-plus',
            //     items: [
            //         {
            //             label: 'Create User',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/create-user'
            //         },
            //         {
            //             label: 'View Users',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/view-users'
            //         },
            //         {
            //             label: 'Create Role',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/create-role'
            //         },
            //         {
            //             label: 'View Roles',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/view-roles'
            //         },

            //     ]
            // },
            // {
            //     label: 'Member Management',
            //     icon: 'pi pi-fw pi-users',
            //     items: [
            //         {
            //             label: 'Create Member',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/create-member'
            //         },
            //         {
            //             label: 'View Members',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/view-members'
            //         },

            //     ]
            // },
            // {
            //     label: 'Bulk Upload',
            //     icon: 'pi pi-fw pi-briefcase',
            //     items: [
            //         {
            //             label: 'Upload Members',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/upload-members'
            //         },
            //         {
            //             label: 'View Uploaded Members',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/view-uploaded-members'
            //         },

            //     ]
            // },
            // {
            //     label: 'Collections',
            //     icon: 'pi pi-fw pi-money-bill',
            //     items: [
            //         {
            //             label: 'Collect Money',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/collect-money'
            //         },
            //         {
            //             label: 'View Collections',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/view-collected-money'
            //         },
            //         {
            //             label: 'Approve Collections',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/approve-collections'
            //         },
            //         {
            //             label: 'Approved Collections',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/approved-collections'
            //         },
            //     ]
            // },
            // {
            //     label: 'Commissions',
            //     icon: 'pi pi-dollar',
            //     items: [
            //         {
            //             label: 'View Commission',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/view-commissions'
            //         }
            //     ]
            // },
            // {
            //     label: 'Reports',
            //     icon: 'pi pi-fw pi-folder',
            //     items: [

            //     ]
            // },
            // {
            //     label: 'Stock Management',
            //     icon: 'pi pi-dollar',
            //     items: [
            //         {
            //             label: 'Create Product',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/create-product'
            //         },
            //         {
            //             label: 'View Products',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/view-product'
            //         },
            //         {
            //             label: 'Point Of Sale',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/point-of-sale'
            //         },
            //         {
            //             label: 'Sales',
            //             icon: 'pi pi-fw pi-circle',
            //             to: '/view-sales'
            //         }


            //     ]
            // },
        ]
    },
]);

if(institutionMenu?.value?.items?.length>0){
    model.value[0].items.push(institutionMenu.value);
}

if(userMenu?.value?.items?.length>0){
    model.value[0].items.push(userMenu.value);
}

if(memberMenu?.value?.items?.length>0){
    model.value[0].items.push(memberMenu.value);
}
if(bulkMenu?.value?.items?.length>0){
    model.value[0].items.push(bulkMenu.value);
}

if(collectionMenu?.value?.items?.length>0){
    model.value[0].items.push(collectionMenu.value);
}

if(commissionMenu?.value?.items?.length>0){
    model.value[0].items.push(commissionMenu.value);
}

if(reportMenu?.value?.items?.length>0){
    model.value[0].items.push(reportMenu.value);
}

if(stockMenu?.value?.items?.length>0){
    model.value[0].items.push(stockMenu.value);
}

// alert(JSON.stringify(model.value[0]))

</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
        <!-- <template>
           <app-menu-item ></app-menu-item>
           <li class="menu-separator">Log Out</li>
       </template> -->
        <!-- <li>
           <a href="https://www.primefaces.org/primeblocks-vue/#/" target="_blank">
               <i class="pi pi-sign-out"></i>
               Log out
           </a>
      </li>  -->
    </ul>
</template>

<style lang="scss" scoped></style>
