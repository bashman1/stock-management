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
        fromDate: fromDate.value,
        toDate: toDate.value,
    }
    console.log(postData)
    getCntrlLedger(postData);
}

const checkDateFormat=(event)=>{
    console.log(event)
}

 onMounted(() => {

    let currentDate = new Date();
    let oneMonthAgo = new Date(currentDate);
    oneMonthAgo.setMonth(currentDate.getMonth() - 1);

    fromDate.value=oneMonthAgo;
    toDate.value=currentDate;

    let postData = {
        fromDate: fromDate.value,
        toDate: toDate.value,
    }
    getCntrlLedger(postData);
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
                        <td class="text-900">{{ incomeStatementData?.totalSales==0?incomeStatementData?.totalSales:commonService.commaSeparator(incomeStatementData?.totalSales) }}</td>
                    </tr>
                    <tr>
                        <th class="text-900">Return in Wards</th>
                        <td class="text-900"></td>
                        <td class="text-900">{{ incomeStatementData?.totalReturnIn==0?incomeStatementData?.totalReturnIn:commonService.commaSeparator(incomeStatementData?.totalReturnIn) }}</td>
                    </tr>
                    <tr>
                        <th class="text-900">Net sales</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ incomeStatementData?.netSales==0?incomeStatementData?.netSales:commonService.commaSeparator(incomeStatementData?.netSales) }}</td>
                    </tr>
                    <tr>
                        <th> <h5 class="statement-header bold text-900">Cost Of Sales</h5></th>
                        <td class="text-900"></td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">opening stock</th>
                        <td class="text-900">{{ incomeStatementData?.openingStock==0?incomeStatementData?.openingStock:commonService.commaSeparator(incomeStatementData?.openingStock) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Purchases</th>
                        <td class="text-900">{{ incomeStatementData?.totalPurchases==0?incomeStatementData?.totalPurchases:commonService.commaSeparator(incomeStatementData?.totalPurchases) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Return in wards/ purchase return</th>
                        <td class="text-900">{{ incomeStatementData?.totalReturnIn==0?incomeStatementData?.totalReturnIn:commonService.commaSeparator(incomeStatementData?.totalReturnIn) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Passage/ carriage</th>
                        <td class="text-900">{{ incomeStatementData?.totalPassage==0?incomeStatementData?.totalPassage:commonService.commaSeparator(incomeStatementData?.totalPassage) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Goods available for sale</th>
                        <td class="bold text-900">{{ incomeStatementData?.goodAvailableForSale==0?incomeStatementData?.goodAvailableForSale:commonService.commaSeparator(incomeStatementData?.goodAvailableForSale) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Closing stock</th>
                        <td class="text-900">{{ incomeStatementData?.closingStock==0?incomeStatementData?.closingStock:commonService.commaSeparator(incomeStatementData?.closingStock) }}</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Cost of sales</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ incomeStatementData?.costOfSales==0?incomeStatementData?.costOfSales:commonService.commaSeparator(incomeStatementData?.costOfSales) }}</td>
                    </tr>
                    <tr>
                        <th class="text-900">Gross Profit</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ incomeStatementData?.grossProfit==0?incomeStatementData?.grossProfit:commonService.commaSeparator(incomeStatementData?.grossProfit) }}</td>
                    </tr>
                    <tr>
                        <th> <h5 class="statement-header bold text-900">Other Incomes</h5></th>
                        <td class="text-900"></td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Discount received</th>
                        <td class="text-900">0</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Profit on deposable of NCA</th>
                        <td class="text-900">0</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Total income</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ incomeStatementData?.totalIncome==0?incomeStatementData?.totalIncome:commonService.commaSeparator(incomeStatementData?.totalIncome) }}</td>
                    </tr>
                    <tr>
                        <th> <h5 class="statement-header bold text-900">Operating Expenses</h5></th>
                        <td class="text-900"></td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Total operating expenses</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ incomeStatementData?.totalOperatingExpenses==0?incomeStatementData?.totalOperatingExpenses:commonService.commaSeparator(incomeStatementData?.totalOperatingExpenses) }}</td>
                    </tr>
                    <tr>
                        <th class="text-900">Profit before interest and tax(PBIT)</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ incomeStatementData?.profitBeforeInterestAndTax==0?incomeStatementData?.totalIncome:commonService.commaSeparator(incomeStatementData?.profitBeforeInterestAndTax) }}</td>
                    </tr>
                    <tr>
                        <th> <h5 class="statement-header bold text-900">Finance Costs</h5></th>
                        <td class="text-900"></td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Interest on external loan</th>

                        <!-- <td>{{ incomeStatementData?.totalOperatingExpenses==0?incomeStatementData?.totalOperatingExpenses:commonService.commaSeparator(incomeStatementData?.totalOperatingExpenses) }}</td> -->
                        <td class="text-900">0</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Interest to URL</th>

                        <!-- <td >{{ incomeStatementData?.totalOperatingExpenses==0?incomeStatementData?.totalOperatingExpenses:commonService.commaSeparator(incomeStatementData?.totalOperatingExpenses) }}</td> -->
                        <td class="text-900">0</td>
                        <td class="text-900"></td>
                    </tr>
                    <tr>
                        <th class="text-900">Total finance costs</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ incomeStatementData?.totalOperatingExpenses==0?incomeStatementData?.totalOperatingExpenses:commonService.commaSeparator(incomeStatementData?.totalOperatingExpenses) }}</td>
                    </tr>
                    <tr>
                        <th class="text-900">Profit before tax (PBT)</th>
                        <td class="text-900"></td>
                        <td class="bold text-900">{{ incomeStatementData?.totalOperatingExpenses==0?incomeStatementData?.totalOperatingExpenses:commonService.commaSeparator(incomeStatementData?.totalOperatingExpenses) }}</td>
                    </tr>
                </table>
        </div>
    </div>
</template>
