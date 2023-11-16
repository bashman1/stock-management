<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

const router = useRouter();

const transactions = ref([]);
const receipt = ref(null);
const display = ref(false);

const getOfficerTransactions = () => {
    commonService.genericRequest('get-officer-collection', 'get', true, {}).then((response) => {
        if (response.status) {
            transactions.value = response.data
        } else { }
    })
}

const showModal = (data) => {
    commonService.genericRequest('get-transaction-receipt/' + data.tran_id, 'get', true, {}).then((response) => {
        if (response.status) {
            receipt.value = response.data
        } else { }
    })
    display.value = commonService.toggleModal(true);
}

const close = () => {
    display.value = commonService.toggleModal(false);
    printContent();
};

const printContent = () => {
    const printWindow = window.open('', '', 'width=600,height=600');
    printWindow.document.open();
    printWindow.document.write(`
        <html>
        <head>
          <title>Print</title>
        </head>
        <style>
        .container {
    border: 1px solid #000;
    padding: 20px;
    max-width: 600px;
    margin: 0 auto;
    font-family: "Courier New", monospace;
}
h1 {
    text-align: center;
}
.section {
    margin-bottom: 10px;
}
.section-title {
    font-weight: bold;
}
.item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
}
.item-description {
    flex: 2;
}
.item-quantity {
    flex: 1;
    text-align: right;
}
.item-price {
    flex: 1;
    text-align: right;
}
.total {
    text-align: right;
    font-weight: bold;
}
.thank-you {
    text-align: center;
}
.receipt-number {
    position: absolute;
    color: red;
    right: 40px;
}
@media print {
    body {
        font-size: 12pt; /* Adjust font size for readability */
    }
    .container {
        border: 1px solid #000;
        padding: 20px;
        max-width: 100%; /* Adjust to fit the printer width */
        margin: 0;
        font-family: "Courier New", monospace;
    }
    /* Add other print-specific styles as needed */
}
        </style>
        <body>
          <div class="print-content">
            <div class="container">
                <div class="receipt-number">` + commonService.removeLetters(receipt.value?.tran_id) + `</div>
                <h1>` + receipt.value?.institution_name + `</h1>

                <div class="item">
                    <div class="item-description">Member Name</div>
                    <div class="item-price">`+ receipt.value?.member_name + `</div>
                </div>

                <div class="item">
                    <div class="item-description">Member Number</div>
                    <div class="item-price">`+ receipt.value?.member_number + `</div>
                </div>

                <div class="item">
                    <div class="item-description">Amount</div>
                    <div class="item-price">`+ commonService.commaSeparator(receipt.value?.amount) + `</div>
                </div>

                <div class="item">
                    <div class="item-description">Amount in words</div>
                    <div class="item-price">` +commonService.NumInWords(receipt.value?.amount) +`</div>
                </div>

                <div class="item">
                    <div class="item-description">Transaction ID</div>
                    <div class="item-price">` + receipt.value?.tran_id + `</div>
                </div>


                <div class="item">
                    <div class="item-description">Deposited By</div>
                    <div class="item-price">`+ receipt.value?.user_name +`</div>
                </div>

                <div class="item">
                    <div class="item-description">Date</div>
                    <div class="item-price">`+ receipt.value?.created_at + `</div>
                </div>

                <p class="thank-you">Thank you for saving with`+ receipt.value?.institution_name + `</p>
                <p class="thank-you">Have a great day!</p>
            </div>
          </div>
        </body>
        </html>
      `);
    printWindow.document.close();
    printWindow.print();
    printWindow.close();
}

onMounted(() => {
    getOfficerTransactions();
});

</script>
<template>
    <div className="card">
        <h5>View Officers Collections</h5>

        <Dialog header="Receipt" v-model:visible="display" :breakpoints="{ '960px': '75vw' }" :style="{ width: '30vw' }"
            :modal="true">
            <p class="line-height-3 m-0">
            <div class="container">
                <div class="receipt-number">{{ commonService.removeLetters(receipt?.tran_id) }}</div>
                <h1>{{ receipt?.institution_name }}</h1>

                <div class="item">
                    <div class="item-description">Member Name</div>
                    <div class="item-price">{{ receipt?.member_name }}</div>
                </div>

                <div class="item">
                    <div class="item-description">Member Number</div>
                    <div class="item-price">{{ receipt?.member_number }}</div>
                </div>

                <div class="item">
                    <div class="item-description">Amount</div>
                    <div class="item-price">{{ commonService.commaSeparator(receipt?.amount) }}</div>
                </div>

                <div class="item">
                    <div class="item-description">Amount in words</div>
                    <div class="item-price">{{ commonService.NumInWords(receipt?.amount) }}</div>
                </div>

                <div class="item">
                    <div class="item-description">Transaction ID</div>
                    <div class="item-price">{{ receipt?.tran_id }}</div>
                </div>

                <div class="item">
                    <div class="item-description">Deposited By</div>
                    <div class="item-price">{{ receipt?.user_name }}</div>
                </div>


                <div class="item">
                    <div class="item-description">Date</div>
                    <div class="item-price">{{ receipt?.created_at }}</div>
                </div>




                <p class="thank-you">Thank you for saving with {{ receipt?.institution_name }}</p>
                <p class="thank-you">Have a great day!</p>
            </div>
            </p>
            <template #footer>
                <Button label="print" @click="close" icon="pi pi-print" class="p-button-outlined" />
            </template>
        </Dialog>
        <div class="grid p-fluid mt-3">
            <div class="field col-12 md:col-12">
                <Toolbar class="mb-4">
                    <template v-slot:start>
                        <div class="my-2">
                            Amount Collected: {{commonService.commaSeparator(commonService.sumOfAmount(transactions))}}
                        </div>
                    </template>

                    <template v-slot:end>
                        <!--                        <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" chooseLabel="Import" class="mr-2 inline-block" />-->
                        <!--                        <Button label="Export" icon="pi pi-upload" class="p-button-help" @click="exportCSV($event)" />-->
                    </template>
                </Toolbar>
                <DataTable :value="transactions" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                    :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
                    <Column field="name" header="Name" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.member_name }}
                        </template>
                    </Column>
                    <Column header="Member No." style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.member_number }}
                        </template>
                    </Column>
                    <Column header="Amount" style="min-width: 12rem">
                        <template #body="{ data }">
                            {{ commonService.commaSeparator(data.amount) }}
                        </template>
                    </Column>
                    <Column header="Status" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.status }}
                        </template>
                    </Column>
                    <Column header="Date" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.tran_date }}
                        </template>
                    </Column>
                    <Column header="Officer" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.officer_name }}
                        </template>
                    </Column>
                    <Column header="Transaction ID" style="min-width: 10rem">
                        <template #body="{ data }">
                            {{ data.tran_id }}
                        </template>
                    </Column>
                    <Column header="Receipt" headerStyle="min-width:5rem;">
                        <template #body="{ data }">
                            <Button icon="pi pi-print" class="p-button-rounded p-button-success mr-2"
                                @click="showModal(data)" />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </div>
</template>
<style >
.container {
    border: 1px solid #000;
    padding: 20px;
    max-width: 600px;
    margin: 0 auto;
    font-family: "Courier New", monospace;
}

h1 {
    text-align: center;
}

.section {
    margin-bottom: 10px;
}

.section-title {
    font-weight: bold;
}

.item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
}

.item-description {
    flex: 2;
}

.item-quantity {
    flex: 1;
    text-align: right;
}

.item-price {
    flex: 1;
    text-align: right;
}

.total {
    text-align: right;
    font-weight: bold;
}

.thank-you {
    text-align: center;
}

.receipt-number {
    position: absolute;
    color: red;
    right: 40px;
}</style>
