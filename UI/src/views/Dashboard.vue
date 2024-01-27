<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import ProductService from '@/service/ProductService';
import { useLayout } from '@/layout/composables/layout';
import CommonService from '@/service/CommonService';


const commonService = new CommonService();
const { isDarkTheme } = useLayout();
const stats=ref(null);
const monthLabel = ref(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jly', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']);

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
        <div class="col-12 lg:col-6 xl:col-3">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Institutions</span>
                        <div class="text-900 font-medium text-xl">{{stats?.total_institutions}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-blue-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-building text-blue-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">24 new </span> -->
                <!-- <span class="text-500">since last visit</span> -->
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Branches</span>
                        <div class="text-900 font-medium text-xl">{{stats?.total_branches}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-orange-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-share-alt text-orange-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">%52+ </span> -->
                <!-- <span class="text-500">since last week</span> -->
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Users</span>
                        <div class="text-900 font-medium text-xl">{{stats?.total_users}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-cyan-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-user text-cyan-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">520 </span> -->
                <!-- <span class="text-500">newly registered</span> -->
            </div>
        </div>
        <div class="col-12 lg:col-6 xl:col-3">
            <div class="card mb-0">
                <div class="flex justify-content-between mb-3">
                    <div>
                        <span class="block text-500 font-medium mb-3">Members</span>
                        <div class="text-900 font-medium text-xl">{{stats?.total_customers}}</div>
                    </div>
                    <div class="flex align-items-center justify-content-center bg-purple-100 border-round" style="width: 2.5rem; height: 2.5rem">
                        <i class="pi pi-users text-purple-500 text-xl"></i>
                    </div>
                </div>
                <!-- <span class="text-green-500 font-medium">85 </span> -->
                <!-- <span class="text-500">responded</span> -->
            </div>
        </div>

        <div class="col-12 xl:col-6">
            <div class="card">
                <h5>Member and collection growth </h5>
                <Chart type="bar" :data="lineData" :options="lineOptions" />
            </div>
        </div>
        <div class="col-12 xl:col-6">
            <div class="card">
                <h5>Member and collection growth </h5>
                <Chart type="line" :data="lineData" :options="lineOptions" />
            </div>
        </div>
        <!-- <div class="col-12 xl:col-4">
            <div class="card">
                <h5>Member and collection growth </h5>
                <Chart type="pie" :data="lineData" :options="lineOptions" />
            </div>
        </div> -->
        <div class="col-12 xl:col-4">
            <div class="card">
                <h5>Member and collection growth </h5>
                <Chart type="radar" :data="lineData" :options="lineOptions" />
            </div>
        </div>
        <!-- <div class="col-12 xl:col-4">
            <div class="card">
                <h5>Member and collection growth </h5>
                <Chart type="doughnut" :data="lineData" :options="lineOptions" />
            </div>
        </div> -->
        <div class="col-12 xl:col-4">
            <div class="card">
                <h5>Member and collection growth </h5>
                <Chart type="polarArea" :data="lineData" :options="lineOptions" />
            </div>
        </div>
    </div>
</template>
