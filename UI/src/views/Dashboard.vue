<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import ProductService from '@/service/ProductService';
import { useLayout } from '@/layout/composables/layout';
import CommonService from '@/service/CommonService';


const commonService = new CommonService();
const { isDarkTheme } = useLayout();
const stats=ref(null);
const monthLabel = ref(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jly', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']);
const dashBoardData = ref({});

const products = ref(null);
let lineData = reactive({
    // labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
    // datasets: [
    //     {
    //         label: 'First Dataset',
    //         data: [65, 59, 80, 81, 56, 55, 40],
    //         fill: false,
    //         backgroundColor: '#2f4860',
    //         borderColor: '#2f4860',
    //         tension: 0.4
    //     },
    //     {
    //         label: 'Second Dataset',
    //         data: [28, 48, 40, 19, 86, 27, 90],
    //         fill: false,
    //         backgroundColor: '#00bb7e',
    //         borderColor: '#00bb7e',
    //         tension: 0.4
    //     }
    // ]
});
let inventoryData = reactive({})
const items = ref([
    { label: 'Add New', icon: 'pi pi-fw pi-plus' },
    { label: 'Remove', icon: 'pi pi-fw pi-minus' }
]);
const lineOptions = ref(null);
const productService = new ProductService();

const getDataStats=()=>{
    commonService.genericRequest('get-dashboard-stats', 'get', true, {}).then((response) => {
        if (response.status) {
            stats.value = response.data.count[0];
            organizeGraphicalData(response.data);
            organizeGraphicalData2(response.data)
            dashBoardData.value =response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const organizeGraphicalData=(data)=>{
    lineData=reactive({
        labels:commonService.getMonthsStartingFromCurrent(),
        datasets:[
            {
                label: "No. of Members ",
                data: commonService.organizeGraphData(data.membersGraph, "count"),
                fill: false,
                backgroundColor: 'rgba(47, 72, 96, 1)',
                borderColor: '#2f4860',
                tension: 0.4
            },
            {
                label: 'No. of Collections',
                data: commonService.organizeGraphData(data.collectionGraph, "count"),
                fill: false,
                backgroundColor: 'rgba(0, 187, 126, 1)',
                borderColor: '#00bb7e',
                tension: 0.4
            }

        ]
    })
}


const organizeGraphicalData2=(data)=>{
    inventoryData=reactive({
        labels:commonService.getMonthsStartingFromCurrent(),
        datasets:[
            {
                label: "No. of Products ",
                data: commonService.organizeGraphData(data.productsGraph, "count"),
                fill: false,
                backgroundColor: 'rgba(47, 72, 96, 1)',
                borderColor: '#2f4860',
                tension: 0.4
            },
            {
                label: 'No. of Sales',
                data: commonService.organizeGraphData(data.salesGraph, "count"),
                fill: false,
                backgroundColor: 'rgba(0, 187, 126, 1)',
                borderColor: '#00bb7e',
                tension: 0.4
            }

        ]
    })
}


onMounted(() => {
    productService.getProductsSmall().then((data) => (products.value = data));
    getDataStats();
});


const formatCurrency = (value) => {
    return value.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
};
const applyLightTheme = () => {
    lineOptions.value = {
        plugins: {
            legend: {
                labels: {
                    color: '#495057'
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: '#495057'
                },
                grid: {
                    color: '#ebedef'
                }
            },
            y: {
                ticks: {
                    color: '#495057'
                },
                grid: {
                    color: '#ebedef'
                }
            }
        }
    };
};

const applyDarkTheme = () => {
    lineOptions.value = {
        plugins: {
            legend: {
                labels: {
                    color: '#ebedef'
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: '#ebedef'
                },
                grid: {
                    color: 'rgba(160, 167, 181, .3)'
                }
            },
            y: {
                ticks: {
                    color: '#ebedef'
                },
                grid: {
                    color: 'rgba(160, 167, 181, .3)'
                }
            }
        }
    };
};

watch(
    isDarkTheme,
    (val) => {
        if (val) {
            applyDarkTheme();
        } else {
            applyLightTheme();
        }
    },
    { immediate: true }
);
</script>

<template>
    <div class="grid">
        <div class="col-12 lg:col-6 xl:col-3" v-if="commonService.checkPermissions('CanViewInstitution')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Institutions</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.total_institutions)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-blue-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-building text-blue-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">24 new </span> -->
                <!-- <span class="text-500">since last visit</span> -->
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3" v-if="commonService.checkPermissions('CanViewInstitution')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Branches</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.total_branches)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-orange-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-share-alt text-orange-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">%52+ </span> -->
                <!-- <span class="text-500">since last week</span> -->
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3" v-if="commonService.checkPermissions('ViewUsers')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Users</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.total_users)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-cyan-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-user text-cyan-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">520 </span> -->
                <!-- <span class="text-500">newly registered</span> -->
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewMember')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Members</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.total_customers)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-users text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Products</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.products)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewSales')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Sales</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.sales)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-credit-card text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewSales')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Products Sold</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.product_sold)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-money-bill text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewSales')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Total Sales Value</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.total_sales_value)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-money-bill text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>


        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewSales')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Today's Sales Value</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.today_sales_value)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-money-bill text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>


        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Total Stock Value</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.total_stock_value)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Today's Stock Value</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.today_stock_value)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Today's Markup Value</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.today_mark_up)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Today's Markup Percentage</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.today_mark_up_percentage)}} %</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Total Expenses</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.total_expenses)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Today Expenses</span>
                        <div class="text-900 font-medium text-xl">{{commonService.commaSeparator(stats?.today_expenses)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>


    </div>
    <div class="grid">

        <div class="col-12 xl:col-6" v-if="commonService.checkPermissions('ViewMember')">
            <div class="card">
                <h5>Member and collection growth </h5>
                <Chart type="bar" :data="lineData" :options="lineOptions" />
            </div>
        </div>
        <div class="col-12 xl:col-6" v-if="commonService.checkPermissions('ViewCollection')">
            <div class="card">
                <h5>Member and collection growth </h5>
                <Chart type="line" :data="lineData" :options="lineOptions" />
            </div>
        </div>

        <div class="col-12 xl:col-6" v-if="commonService.checkPermissions('ViewSales')">
            <div class="card">
                <h5>Products and Sales growth </h5>
                <Chart type="bar" :data="inventoryData" :options="lineOptions" />
            </div>
        </div>
        <div class="col-12 xl:col-6" v-if="commonService.checkPermissions('ViewSales')">
            <div class="card">
                <h5>Products and Sales growth </h5>
                <Chart type="line" :data="inventoryData" :options="lineOptions" />
            </div>
        </div>

        <!-- {"id":21,"role_id":2,"permission_id":1,"name":"CanCreateInstitution","description":"Create Institution","is_admin":true,"status":"Active"} -->
        <!-- <div class="col-12 xl:col-4">
            <div class="card">
                <h5>Member and collection growth </h5>
                <Chart type="pie" :data="lineData" :options="lineOptions" />
            </div>
        </div> -->
        <!-- <div class="col-12 xl:col-4">
            <div class="card">
                <h5>Member and collection growth </h5>
                <Chart type="radar" :data="lineData" :options="lineOptions" />
            </div>
        </div> -->
        <!-- <div class="col-12 xl:col-4">
            <div class="card">
                <h5>Member and collection growth </h5>
                <Chart type="doughnut" :data="lineData" :options="lineOptions" />
            </div>
        </div> -->
        <!-- <div class="col-12 xl:col-4">
            <div class="card">
                <h5>Member and collection growth </h5>
                <Chart type="polarArea" :data="lineData" :options="lineOptions" />
            </div>
        </div> -->
    </div>
    <div class="grid">
        <div class="col-12 xl:col-6">
            <div class="card">
                <h5>Best Selling Products</h5>
                <DataTable :value="dashBoardData?.bestSellingProduct" :rows="5" :paginator="true" responsiveLayout="scroll">
                    <Column field="name" header="Name" :sortable="true" style="width: 35%"></Column>
                    <Column field="quantity" header="Quantity" :sortable="true" style="width: 35%"></Column>
                    <Column field="price" header="Price" :sortable="true" style="width: 35%">
                        <template #body="slotProps">
                            {{ formatCurrency(slotProps.data.selling_price) }}
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <div class="col-12 xl:col-6">
            <div class="card">
                <div class="flex justify-content-between align-items-center mb-5">
                    <h5>Best Selling Products</h5>
                    <!-- <div>
                        <Button icon="pi pi-ellipsis-v" class="p-button-text p-button-plain p-button-rounded" @click="$refs.menu2.toggle($event)"></Button>
                        <Menu ref="menu2" :popup="true" :model="items"></Menu>
                    </div> -->
                </div>
                <ul class="list-none p-0 m-0" v-for="(product, index) in dashBoardData?.bestSellingProduct" :key="index">
                    <li class="flex flex-column md:flex-row md:align-items-center md:justify-content-between mb-4">
                        <div>
                            <span class="text-900 font-medium mr-2 mb-1 md:mb-0">{{ product.name }}</span>
                            <div class="mt-1 text-600">Clothing</div>
                        </div>
                        <div class="mt-2 md:mt-0 flex align-items-center">
                            <div class="surface-300 border-round overflow-hidden w-10rem lg:w-6rem" style="height: 8px">
                                <div class="bg-orange-500 h-full" :style="'width: '+product.quantity+'%'"></div>
                            </div>
                            <span class="text-orange-500 ml-3 font-medium">%{{ product.quantity }}</span>
                        </div>
                    </li>
                    <!-- <li class="flex flex-column md:flex-row md:align-items-center md:justify-content-between mb-4">
                        <div>
                            <span class="text-900 font-medium mr-2 mb-1 md:mb-0">Portal Sticker</span>
                            <div class="mt-1 text-600">Accessories</div>
                        </div>
                        <div class="mt-2 md:mt-0 ml-0 md:ml-8 flex align-items-center">
                            <div class="surface-300 border-round overflow-hidden w-10rem lg:w-6rem" style="height: 8px">
                                <div class="bg-cyan-500 h-full" style="width: 16%"></div>
                            </div>
                            <span class="text-cyan-500 ml-3 font-medium">%16</span>
                        </div>
                    </li> -->
                    <!-- <li class="flex flex-column md:flex-row md:align-items-center md:justify-content-between mb-4">
                        <div>
                            <span class="text-900 font-medium mr-2 mb-1 md:mb-0">Supernova Sticker</span>
                            <div class="mt-1 text-600">Accessories</div>
                        </div>
                        <div class="mt-2 md:mt-0 ml-0 md:ml-8 flex align-items-center">
                            <div class="surface-300 border-round overflow-hidden w-10rem lg:w-6rem" style="height: 8px">
                                <div class="bg-pink-500 h-full" style="width: 67%"></div>
                            </div>
                            <span class="text-pink-500 ml-3 font-medium">%67</span>
                        </div>
                    </li> -->
                    <!-- <li class="flex flex-column md:flex-row md:align-items-center md:justify-content-between mb-4">
                        <div>
                            <span class="text-900 font-medium mr-2 mb-1 md:mb-0">Wonders Notebook</span>
                            <div class="mt-1 text-600">Office</div>
                        </div>
                        <div class="mt-2 md:mt-0 ml-0 md:ml-8 flex align-items-center">
                            <div class="surface-300 border-round overflow-hidden w-10rem lg:w-6rem" style="height: 8px">
                                <div class="bg-green-500 h-full" style="width: 35%"></div>
                            </div>
                            <span class="text-green-500 ml-3 font-medium">%35</span>
                        </div>
                    </li> -->
                    <!-- <li class="flex flex-column md:flex-row md:align-items-center md:justify-content-between mb-4">
                        <div>
                            <span class="text-900 font-medium mr-2 mb-1 md:mb-0">Mat Black Case</span>
                            <div class="mt-1 text-600">Accessories</div>
                        </div>
                        <div class="mt-2 md:mt-0 ml-0 md:ml-8 flex align-items-center">
                            <div class="surface-300 border-round overflow-hidden w-10rem lg:w-6rem" style="height: 8px">
                                <div class="bg-purple-500 h-full" style="width: 75%"></div>
                            </div>
                            <span class="text-purple-500 ml-3 font-medium">%75</span>
                        </div>
                    </li> -->
                    <!-- <li class="flex flex-column md:flex-row md:align-items-center md:justify-content-between mb-4">
                        <div>
                            <span class="text-900 font-medium mr-2 mb-1 md:mb-0">Robots T-Shirt</span>
                            <div class="mt-1 text-600">Clothing</div>
                        </div>
                        <div class="mt-2 md:mt-0 ml-0 md:ml-8 flex align-items-center">
                            <div class="surface-300 border-round overflow-hidden w-10rem lg:w-6rem" style="height: 8px">
                                <div class="bg-teal-500 h-full" style="width: 40%"></div>
                            </div>
                            <span class="text-teal-500 ml-3 font-medium">%40</span>
                        </div>
                    </li> -->
                </ul>
            </div>
        </div>

    </div>
</template>
