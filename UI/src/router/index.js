import { ref } from 'vue';
import { createRouter, createWebHashHistory } from 'vue-router';
import AppLayout from '@/layout/AppLayout.vue';
import CommonService from '@/service/CommonService'

const commonService = new CommonService();

export const isAuthenticated = ref(commonService.checkingAuthentication());
// export const isAuthenticated = ref(commonService.loggedIn);

const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        {
            path: '/admin',
            component: AppLayout,
            children: [
                {
                    path: '/admin',
                    name: 'dashboard',
                    component: () => import('@/views/Dashboard.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                // {
                //     path: '/uikit/formlayout',
                //     name: 'formlayout',
                //     component: () => import('@/views/uikit/FormLayout.vue')
                // },
                // {
                //     path: '/uikit/input',
                //     name: 'input',
                //     component: () => import('@/views/uikit/Input.vue')
                // },
                // {
                //     path: '/uikit/floatlabel',
                //     name: 'floatlabel',
                //     component: () => import('@/views/uikit/FloatLabel.vue')
                // },
                // {
                //     path: '/uikit/invalidstate',
                //     name: 'invalidstate',
                //     component: () => import('@/views/uikit/InvalidState.vue')
                // },
                // {
                //     path: '/uikit/button',
                //     name: 'button',
                //     component: () => import('@/views/uikit/Button.vue')
                // },
                // {
                //     path: '/uikit/table',
                //     name: 'table',
                //     component: () => import('@/views/uikit/Table.vue')
                // },
                // {
                //     path: '/uikit/list',
                //     name: 'list',
                //     component: () => import('@/views/uikit/List.vue')
                // },
                // {
                //     path: '/uikit/tree',
                //     name: 'tree',
                //     component: () => import('@/views/uikit/Tree.vue')
                // },
                // {
                //     path: '/uikit/panel',
                //     name: 'panel',
                //     component: () => import('@/views/uikit/Panels.vue')
                // },

                // {
                //     path: '/uikit/overlay',
                //     name: 'overlay',
                //     component: () => import('@/views/uikit/Overlay.vue')
                // },
                // {
                //     path: '/uikit/media',
                //     name: 'media',
                //     component: () => import('@/views/uikit/Media.vue')
                // },
                // {
                //     path: '/uikit/menu',
                //     component: () => import('@/views/uikit/Menu.vue'),
                //     children: [
                //         {
                //             path: '/uikit/menu',
                //             component: () => import('@/views/uikit/menu/PersonalDemo.vue')
                //         },
                //         {
                //             path: '/uikit/menu/seat',
                //             component: () => import('@/views/uikit/menu/SeatDemo.vue')
                //         },
                //         {
                //             path: '/uikit/menu/payment',
                //             component: () => import('@/views/uikit/menu/PaymentDemo.vue')
                //         },
                //         {
                //             path: '/uikit/menu/confirmation',
                //             component: () => import('@/views/uikit/menu/ConfirmationDemo.vue')
                //         }
                //     ]
                // },
                // {
                //     path: '/uikit/message',
                //     name: 'message',
                //     component: () => import('@/views/uikit/Messages.vue')
                // },
                // {
                //     path: '/uikit/file',
                //     name: 'file',
                //     component: () => import('@/views/uikit/File.vue')
                // },
                // {
                //     path: '/uikit/charts',
                //     name: 'charts',
                //     component: () => import('@/views/uikit/Chart.vue')
                // },
                // {
                //     path: '/uikit/misc',
                //     name: 'misc',
                //     component: () => import('@/views/uikit/Misc.vue')
                // },
                // {
                //     path: '/blocks',
                //     name: 'blocks',
                //     component: () => import('@/views/utilities/Blocks.vue')
                // },
                // {
                //     path: '/utilities/icons',
                //     name: 'icons',
                //     component: () => import('@/views/utilities/Icons.vue')
                // },
                // {
                //     path: '/pages/timeline',
                //     name: 'timeline',
                //     component: () => import('@/views/pages/Timeline.vue')
                // },
                // {
                //     path: '/pages/empty',
                //     name: 'empty',
                //     component: () => import('@/views/pages/Empty.vue')
                // },
                // {
                //     path: '/pages/crud',
                //     name: 'crud',
                //     component: () => import('@/views/pages/Crud.vue')
                // },
                // {
                //     path: '/documentation',
                //     name: 'documentation',
                //     component: () => import('@/views/utilities/Documentation.vue')
                // },
                {
                    path: '/create-institution',
                    name: 'institution',
                    component: () => import('@/views/pages/institution-management/CreateInstitution.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/create-institution/:id',
                    name: 'EditInstitution',
                    component: () => import('@/views/pages/institution-management/CreateInstitution.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/view-institutions',
                    name: 'ViewInstitutions',
                    component: () => import('@/views/pages/institution-management/ViewInstitutions.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/create-user',
                    name: 'CreateUser',
                    component: () => import('@/views/pages/user-management/CreateUser.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },

                },
                {
                    path: '/create-user/:id',
                    name: 'EditUser',
                    component: () => import('@/views/pages/user-management/CreateUser.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },

                },
                {
                    path: '/view-users',
                    name: 'ViewUsers',
                    component: () => import('@/views/pages/user-management/ViewUsers.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/create-role',
                    name: 'CreateRole',
                    component: () => import('@/views/pages/user-management/CreateRole.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/view-roles',
                    name: 'ViewRoles',
                    component: () => import('@/views/pages/user-management/ViewRoles.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/create-member',
                    name: 'CreateMember',
                    component: () => import('@/views/pages/member-management/CreateMember.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/view-members',
                    name: 'ViewMember',
                    component: () => import('@/views/pages/member-management/ViewMembers.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/role-permission/:id',
                    name: 'RolePermission',
                    component: () => import('@/views/pages/user-management/AssignRolePermissions.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path:'/upload-members',
                    name: 'BulkUploadMembers',
                    component: ()=>import('@/views/pages/member-management/BulkUploadMembers.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },

                },
                {
                    path:'/view-uploaded-members',
                    name: 'ViewBulkUploadedMembers',
                    component: ()=>import('@/views/pages/member-management/ViewBulkUploadedMembers.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },

                },
                {
                    path:'/approve-uploaded-members/:id',
                    name: 'ApproveBulkUploadedMembers',
                    component: ()=>import('@/views/pages/member-management/ApproveBulkMember.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },

                {
                    path: '/collect-money',
                    name: 'CollectMoney',
                    component: ()=> import('@/views/pages/collection-management/CollectMoney.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/view-collected-money',
                    name: 'ViewMoneyCollected',
                    component: ()=> import('@/views/pages/collection-management/ViewCollections.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/approve-collections',
                    name: 'ApproveCollections',
                    component: ()=> import('@/views/pages/collection-management/ApproveCollections.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/approved-collections',
                    name: 'ApprovedCollections',
                    component: ()=> import('@/views/pages/collection-management/ViewApprovedCollections.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/view-commissions',
                    name: 'Commissions',
                    component: ()=> import('@/views/pages/comission/Comissions.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },

                },
                {
                    path: '/create-product',
                    name: 'CreateProduct',
                    component: ()=>import('@/views/pages/stock-management/CreateProduct.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/create-product/:id',
                    name: 'EditProduct',
                    component: ()=>import('@/views/pages/stock-management/CreateProduct.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/view-product',
                    name: 'ViewProduct',
                    component: ()=>import('@/views/pages/stock-management/ViewProducts.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/point-of-sale',
                    name: 'PointOfSale',
                    component: ()=>import('@/views/pages/stock-management/PointOfSale.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/view-sales',
                    name: 'ViewOrders',
                    component: ()=>import('@/views/pages/stock-management/ViewOrders.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/view-product-details/:id',
                    name: 'ViewProductDetails',
                    component: ()=>import('@/views/pages/stock-management/ProductDetails.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/chart-of-accounts',
                    name: 'ChartOfAccounts',
                    component: ()=>import('@/views/pages/chart-of-accounts/ChartOfAccounts.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/create-ledger',
                    name: 'CreateLedger',
                    component: ()=>import('@/views/pages/chart-of-accounts/CreateLedger.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/gl-accounts',
                    name: 'GlAccounts',
                    component: ()=>import('@/views/pages/chart-of-accounts/GlAccounts.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/debit-credit-gl',
                    name: 'GlInjection',
                    component: ()=>import('@/views/pages/chart-of-accounts/GlInjection.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/gl-statement/:acctNo',
                    name: 'GlStatement',
                    component: ()=>import('@/views/pages/chart-of-accounts/GlHistory.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/gl-overview',
                    name: 'GlOverView',
                    component: ()=>import('@/views/pages/chart-of-accounts/glOverView.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/gl-balance-sheet',
                    name: 'GlBalanceSheet',
                    component: ()=>import('@/views/pages/chart-of-accounts/BalanceSheet.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/cntrl-params',
                    name: 'CntrlParams',
                    component: ()=>import('@/views/pages/chart-of-accounts/CntrParams.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/income-statement',
                    name: 'IncomeStatement',
                    component: ()=>import('@/views/pages/chart-of-accounts/IncomeStatement.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/products-report',
                    name: 'ProductsReport',
                    component: ()=>import('@/views/pages/reports/ProductsReport.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/sales-report',
                    name: 'SalesReport',
                    component: ()=>import('@/views/pages/reports/SalesReport.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/product-inventory-report/:productId',
                    name: 'ProductsInventoryReport',
                    component: ()=>import('@/views/pages/reports/ProductInventoryReport.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/products-sales-report/:productId',
                    name: 'ProductsSalesReport',
                    component: ()=>import('@/views/pages/reports/ProductSalesReport.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/logout',
                    name: 'LogOut',
                    component: ()=>import('@/views/pages/LogOut.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/upload-products',
                    name: 'UploadProducts',
                    component: ()=>import('@/views/pages/stock-management/BulkUploadProducts.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/view-uploaded-products',
                    name: 'uploadedProducts',
                    component: () => import('@/views/pages/stock-management/ViewUploadedProducts.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                },
                {
                    path: '/approve-uploaded-products/:id',
                    name: 'ApproveUploadedProducts',
                    component: () => import('@/views/pages/stock-management/ApproveBulkProducts.vue'),
                    meta: {
                        requiresAuth: true, // This route requires authentication
                    },
                }




            ]
        },
        {
            path: '/',
            name: 'landing',
            component: () => import('@/views/pages/Landing.vue')
        },
        {
            path: '/pages/notfound',
            name: 'notfound',
            component: () => import('@/views/pages/NotFound.vue')
        },

        {
            path: '/auth/login',
            name: 'login',
            component: () => import('@/views/pages/auth/Login.vue')
        },
        {
            path: '/auth/access',
            name: 'accessDenied',
            component: () => import('@/views/pages/auth/Access.vue')
        },
        {
            path: '/auth/error',
            name: 'error',
            component: () => import('@/views/pages/auth/Error.vue')
        },

    ]
});


router.beforeEach((to, from, next) => {
    // Check if the route requires authentication
    if (to.meta.requiresAuth) {
        // If the user is authenticated, allow navigation
        if (isAuthenticated.value) {
            next();
        } else {
            // If not authenticated, redirect to the login page
            next('/auth/login');
        }
    } else {
        // If the route does not require authentication, allow navigation
        next();
    }
});


export default router;
