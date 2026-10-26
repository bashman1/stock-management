<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

 const router = useRouter();
const incomeStatementData = ref(null);
const fromDate = ref(null);
const toDate = ref(null);

const getCntrlLedger=(postData)=>{
    commonService.genericRequest('get-income-statement', 'post', true, postData).then((response)=>{
        if(response.status){
            incomeStatementData.value = response.data
        }else{}
    })
}

const generateIncomeStatement=()=>{
    let postData = {
        fromDate: commonService.toApiDate(fromDate.value),
        toDate: commonService.toApiDate(toDate.value),
    }
    getCntrlLedger(postData);
}

const money = (value) => commonService.formatMoney(value);

const checkDateFormat=(event)=>{
    console.log(event)
}

 onMounted(() => {

    let currentDate = new Date();
    let oneMonthAgo = new Date(currentDate);
    oneMonthAgo.setMonth(currentDate.getMonth() - 1);

    fromDate.value=oneMonthAgo;
    toDate.value=currentDate;

    generateIncomeStatement();
});


</script>
<template>
    <div>
        <div class="card">
            <h4>Income Statement</h4>
            <!-- <div class="grid p-fluid mt-3"> -->
                <Toast />

                <Toolbar class="mb-4">
                    <template v-slot:start>
                        <div class="grid p-fluid mt-3">
                            <div class="field col-12 md:col-4">
                                <span class="p-float-label">
                                    <Calendar inputId="calendar" v-model="fromDate"></Calendar>
                                    <label for="calendar">From Date</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-4">
                                <span class="p-float-label">
                                    <Calendar @blur="checkDateFormat($event)" inputId="calendar" v-model="toDate"></Calendar>
                                    <label for="calendar">To Date</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-4">
                                <div>
                                    <Button label="Search" class="p-button-success mr-2" @click="generateIncomeStatement" />
                                </div>
                            </div>
                        </div>



                    </template>
                    <template v-slot:end>
                        <div class="my-2">
                            <Button label="CSV" icon="pi pi-file-excel" class="p-button-success mr-2" @click="openNew" />
                            <Button label="PDF" icon="pi pi-file-pdf" class="p-button-danger" @click="confirmDeleteSelected" />
                        </div>
                    </template>


                </Toolbar>


                <table class="income-statement">
                    <tr>
                        <th class="bold"> <h5 class="statement-header bold text-900">Particulars</h5></th>
                        <td class="bold text-900">Amount</td>
                        <td class="bold text-900">Amount</td>
                    </tr>
                    <tr>
                        <th class="text-900">Sales</th>
                        <td class="text-900"></td>
                        <td class="text-900">{{ money(incomeStatementData?.totalSales) }}</td>
                    </tr>
                    <tr>
                        <th class="text-900">Less: returns inwards</th>
                        <td class="text-900"></td>
                        <td class="text-900">{{ money(incomeStatementData?.totalReturnIn) }}</td>
                    </tr>
                    <tr>
                        <th class="text-900">Net sales</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ money(incomeStatementData?.netSales) }}</td>
                    </tr>
                    <tr>
                        <th> <h5 class="statement-header bold text-900">Cost Of Sales</h5></th>
                        <td class="text-900"></td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Opening stock</th>
                        <td class="text-900">{{ money(incomeStatementData?.openingStock) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Add: purchases</th>
                        <td class="text-900">{{ money(incomeStatementData?.totalPurchases) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Stock adjustments and returns</th>
                        <td class="text-900">{{ money(incomeStatementData?.stockAdjustments) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Goods available for sale</th>
                        <td class="bold text-900">{{ money(incomeStatementData?.goodAvailableForSale) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Less: closing stock</th>
                        <td class="text-900">{{ money(incomeStatementData?.closingStock) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Cost of sales</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ money(incomeStatementData?.costOfSales) }}</td>
                    </tr>
                    <tr>
                        <th class="text-900">Gross Profit</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ money(incomeStatementData?.grossProfit) }}</td>
                    </tr>
                    <tr>
                        <th> <h5 class="statement-header bold text-900">Other Incomes</h5></th>
                        <td class="text-900"></td>
                        <td class="text-900"></td>
                    </tr>
                    <tr v-for="(inc, i) in incomeStatementData?.otherIncomes" :key="'inc' + i">
                        <th class="text-900" style="font-weight: normal">{{ inc.description }}</th>
                        <td class="text-900">{{ money(inc.amount) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr v-if="!incomeStatementData?.otherIncomes?.length">
                        <th class="text-900" style="font-weight: normal">None</th>
                        <td class="text-900">0</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Total income</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ money(incomeStatementData?.totalIncome) }}</td>
                    </tr>
                    <tr>
                        <th> <h5 class="statement-header bold text-900">Operating Expenses</h5></th>
                        <td class="text-900"></td>
                        <td class="text-900"></td>
                    </tr>
                    <tr v-for="(exp, i) in incomeStatementData?.operatingExpenses" :key="'exp' + i">
                        <th class="text-900" style="font-weight: normal">{{ exp.description }}</th>
                        <td class="text-900">{{ money(exp.amount) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Total operating expenses</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ money(incomeStatementData?.totalOperatingExpenses) }}</td>
                    </tr>
                    <tr>
                        <th class="text-900">Profit before interest and tax (PBIT)</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ money(incomeStatementData?.profitBeforeInterestAndTax) }}</td>
                    </tr>
                    <tr>
                        <th class="text-900">Less: finance costs</th>
                        <td class="text-900"></td>
                        <td class="text-900">{{ money(incomeStatementData?.totalFinanceCosts) }}</td>
                    </tr>
                    <tr>
                        <th class="text-900">Profit before tax (PBT)</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ money(incomeStatementData?.netProfit) }}</td>
                    </tr>
                </table>
        </div>
    </div>
</template>
