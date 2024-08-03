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
                backgroundColor: 'rgba(98, 98, 98, 1)',
                borderColor: '#626262',
                tension: 0.4
            },
            {
                label: 'No. of Collections',
                data: commonService.organizeGraphData(data.collectionGraph, "count"),
                fill: false,
                backgroundColor: 'rgba(127, 198, 34, 1)',
                borderColor: '#7FC622',
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
                backgroundColor: 'rgba(98, 98, 98, 1)',
                borderColor: '#626262',
                tension: 0.4
            },
            {
                label: 'No. of Sales',
                data: commonService.organizeGraphData(data.salesGraph, "count"),
                fill: false,
                backgroundColor: 'rgba(127, 198, 34, 1)',
                borderColor: '#7FC622',
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
                        <span class="block text-500 font-bold text-xl mb-3 ">Institutions</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.total_institutions)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-blue-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-building text-blue-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">24 new </span> -->
                <!-- <span class="text-500">since last visit</span> -->
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3" v-if="commonService.checkPermissions('CanViewInstitution')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Branches</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.total_branches)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-orange-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-share-alt text-orange-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">%52+ </span> -->
                <!-- <span class="text-500">since last week</span> -->
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3" v-if="commonService.checkPermissions('ViewUsers')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Users</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.total_users)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-cyan-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-user text-cyan-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">520 </span> -->
                <!-- <span class="text-500">newly registered</span> -->
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewMember')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Members</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.total_customers)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-users text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Products</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.products)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewSales')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Sales</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.sales)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-credit-card text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewSales')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Products Sold</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.product_sold)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-money-bill text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewSales')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Total Sales Value</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.total_sales_value)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-money-bill text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>


        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewSales')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Today's Sales Value</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.today_sales_value)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-money-bill text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>


        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Total Stock Value</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.total_stock_value)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Today's Purchases</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.today_stock_value)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Today's Markup Value</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.today_mark_up)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Today's Markup Percentage</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.today_mark_up_percentage)}} %</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Total Income</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.total_income)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>


        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Today Income</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.today_income)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>


        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Total Expenses</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.total_expenses)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 lg:col-6 xl:col-3"  v-if="commonService.checkPermissions('ViewProducts')">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-bold text-xl mb-3">Today Expenses</span>
                        <div class="text-900 font-bold text-xl">{{commonService.commaSeparator(stats?.today_expenses)}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-green-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-shopping-bag text-green-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-bold text-xl">85 </span> -->
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
                <DataTable :value="dashBoardData?.bestSellingProduct" stripedRows :rows="10" :paginator="true" responsiveLayout="scroll">
                    <Column field="name" header="Name" :sortable="true" style="width: 35%"></Column>
                    <Column field="quantity" header="Quantity" :sortable="true" style="width: 35%"></Column>
                    <Column field="price" header="Price" :sortable="true" style="width: 35%">
                        <template #body="slotProps">
                            {{ commonService.commaSeparator(slotProps.data.selling_price) }}
                        </template>
                    </Column>
                    <Column field="" header="MarkUp" :sortable="true" style="width: 40%">
                        <template #body="slotProps">
                            {{ commonService.commaSeparator(slotProps.data.quantity * (slotProps.data.selling_price - slotProps.data.purchase_price)) }}
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <div class="col-12 xl:col-6">
            <div class="card">
                <h5>Products About To Expire</h5>
                <DataTable :value="dashBoardData?.productAboutToExpire" stripedRows :rows="10" :paginator="true" responsiveLayout="scroll">
                    <Column field="name" header="Name" :sortable="true" style="width: 35%"></Column>
                    <Column field="quantity" header="Stock" :sortable="true" style="width: 20%"></Column>
<!--                    <Column field="price" header="Price" :sortable="true" style="width: 35%">-->
<!--                        <template #body="slotProps">-->
<!--                            {{ commonService.commaSeparator(slotProps.data.selling_price) }}-->
<!--                        </template>-->
<!--                    </Column>-->
                    <Column field="days_difference" header="Days Remaining" :sortable="true" style="width: 35%"></Column>
<!--                    <Column field="" header="Mark Up" :sortable="true" style="width: 35%">-->
<!--                        <template #body="slotProps">-->
<!--                            {{ commonService.commaSeparator(slotProps.data.quantity * (slotProps.data.selling_price - slotProps.data.purchase_price)) }}-->
<!--                        </template>-->
<!--                    </Column>-->
                    <Column field="expiry_date" header="Expiry" :sortable="true" style="width: 35%"></Column>

                </DataTable>
            </div>
        </div>

    </div>
</template>
