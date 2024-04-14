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
    getCntrlLedger(postData);
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
                                    <Calendar inputId="calendar" v-model="toDate"></Calendar>
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
                        <th> <h5 class="statement-header">Particulars</h5></th>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Sales</th>
                        <td></td>
                        <td>{{ incomeStatementData?.totalSales==0?incomeStatementData?.totalSales:commonService.commaSeparator(incomeStatementData?.totalSales) }}</td>
                    </tr>
                    <tr>
                        <th>Return in Wards</th>
                        <td></td>
                        <td>{{ incomeStatementData?.totalReturnIn==0?incomeStatementData?.totalReturnIn:commonService.commaSeparator(incomeStatementData?.totalReturnIn) }}</td>
                    </tr>
                    <tr>
                        <th>Net sales</th>
                        <td></td>
                        <td class="bold">{{ incomeStatementData?.netSales==0?incomeStatementData?.netSales:commonService.commaSeparator(incomeStatementData?.netSales) }}</td>
                    </tr>
                    <tr>
                        <th> <h5 class="statement-header">Cost Of Sales</h5></th>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>opening stock</th>
                        <td>{{ incomeStatementData?.openingStock==0?incomeStatementData?.openingStock:commonService.commaSeparator(incomeStatementData?.openingStock) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Purchases</th>
                        <td>{{ incomeStatementData?.totalPurchases==0?incomeStatementData?.totalPurchases:commonService.commaSeparator(incomeStatementData?.totalPurchases) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Return in wards/ purchase return</th>
                        <td>{{ incomeStatementData?.totalReturnIn==0?incomeStatementData?.totalReturnIn:commonService.commaSeparator(incomeStatementData?.totalReturnIn) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Passage/ carriage</th>
                        <td>{{ incomeStatementData?.totalPassage==0?incomeStatementData?.totalPassage:commonService.commaSeparator(incomeStatementData?.totalPassage) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Goods available for sale</th>
                        <td class="bold">{{ incomeStatementData?.goodAvailableForSale==0?incomeStatementData?.goodAvailableForSale:commonService.commaSeparator(incomeStatementData?.goodAvailableForSale) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Closing stock</th>
                        <td>{{ incomeStatementData?.closingStock==0?incomeStatementData?.closingStock:commonService.commaSeparator(incomeStatementData?.closingStock) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Cost of sales</th>
                        <td></td>
                        <td class="bold">{{ incomeStatementData?.costOfSales==0?incomeStatementData?.costOfSales:commonService.commaSeparator(incomeStatementData?.costOfSales) }}</td>
                    </tr>
                    <tr>
                        <th>Gross Profit</th>
                        <td></td>
                        <td class="bold">{{ incomeStatementData?.grossProfit==0?incomeStatementData?.grossProfit:commonService.commaSeparator(incomeStatementData?.grossProfit) }}</td>
                    </tr>
                    <tr>
                        <th> <h5 class="statement-header">Other Incomes</h5></th>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Discount received</th>
                        <td>3,000</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Profit on deposable of NCA</th>
                        <td>3,000</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Total income</th>
                        <td></td>
                        <td class="bold">{{ incomeStatementData?.totalIncome==0?incomeStatementData?.totalIncome:commonService.commaSeparator(incomeStatementData?.totalIncome) }}</td>
                    </tr>
                    <tr>
                        <th> <h5 class="statement-header">Operating Expenses</h5></th>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Total operating expenses</th>
                        <td></td>
                        <td class="bold">{{ incomeStatementData?.totalOperatingExpenses==0?incomeStatementData?.totalOperatingExpenses:commonService.commaSeparator(incomeStatementData?.totalOperatingExpenses) }}</td>
                    </tr>
                    <tr>
                        <th>Profit before interest and tax(PBIT)</th>
                        <td></td>
                        <td class="bold">{{ incomeStatementData?.totalIncome==0?incomeStatementData?.totalIncome:commonService.commaSeparator(incomeStatementData?.totalIncome) }}</td>
                    </tr>
                    <tr>
                        <th> <h5 class="statement-header">Finance Costs</h5></th>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Interest on external loan</th>

                        <td>{{ incomeStatementData?.totalOperatingExpenses==0?incomeStatementData?.totalOperatingExpenses:commonService.commaSeparator(incomeStatementData?.totalOperatingExpenses) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Interest to URL</th>

                        <td >{{ incomeStatementData?.totalOperatingExpenses==0?incomeStatementData?.totalOperatingExpenses:commonService.commaSeparator(incomeStatementData?.totalOperatingExpenses) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Total finance costs</th>
                        <td></td>
                        <td class="bold">{{ incomeStatementData?.totalOperatingExpenses==0?incomeStatementData?.totalOperatingExpenses:commonService.commaSeparator(incomeStatementData?.totalOperatingExpenses) }}</td>
                    </tr>
                    <tr>
                        <th>Profit before tax (PBT)</th>
                        <td></td>
                        <td class="bold">{{ incomeStatementData?.totalOperatingExpenses==0?incomeStatementData?.totalOperatingExpenses:commonService.commaSeparator(incomeStatementData?.totalOperatingExpenses) }}</td>
                    </tr>
                </table>
        </div>
    </div>
</template>
