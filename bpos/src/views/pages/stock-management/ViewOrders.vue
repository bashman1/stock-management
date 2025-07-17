<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const memberBatch = ref(null);
const showProductDetailsModal = ref(false);
const orderItems = ref(null);
const commonService = new CommonService();
const orderDetails = ref({});
const router = useRouter();
const route = useRoute();
const displayConfirmation = ref(false);
const deleteOrderId = ref(null);
const showInvoiceModal = ref(false);
const userData = ref({});


const getSales = () => {
    commonService.genericRequest('get-orders', 'get', true, {}).then((response) => {
        if (response.status) {
            memberBatch.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}
const viewDetails = (data) => {
    // toggleModal(true);
    orderDetails.value = data;
    getOrderDetails(data?.id);

}

const getOrderDetails = (orderId) => {
    commonService.genericRequest('get-orders-details/' + orderId, 'get', true, {}).then((response) => {
        if (response.status) {
            orderItems.value = response.data;
            toggleInvoiceModel(true);
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const toggleModal = (action) => {
    showProductDetailsModal.value = action;
}

const deleteOrder = ()=>{
    // let postData = {
    //     id: data.id
    // }
    commonService.genericRequest('reverse-sale', 'post', true, deleteOrderId.value).then((response) => {
        closeConfirmation();
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getSales();
            // orderItems.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const printReceipt=()=>{
    commonService.genericRequest('print-receipt', 'post', true, {}).then((response) => {
        if (response.status) {
            orderItems.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const openConfirmation = (data) => {
    displayConfirmation.value = true;
    let postData = {
        id: data.id
    }

    deleteOrderId.value= postData;
};

const closeConfirmation = () => {
    displayConfirmation.value = true;
};

const toggleInvoiceModel = (action) => {
    showInvoiceModal.value = action
}

const printInvoice = () => {
  const printWindow = window.open('', '', 'width=600,height=600');
  printWindow.document.open();

  let productRows = '';
  orderItems.value.forEach(prod => {
    productRows += `
      <tr>
        <td style='border: 1px solid #ddd; padding: 8px; color: #666;'>${prod.qty}</td>
        <td style='border: 1px solid #ddd; padding: 8px; color: #666;'>${prod.name}</td>
        <td style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>${commonService.commaSeparator(Number(prod.price))}</td>
        <td style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>${commonService.commaSeparator(Number(prod.price) * Number(prod.qty))}</td>
      </tr>`;
  });

  printWindow.document.write(`<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Receipt</title>
  <style>
  @media print {
    body {
      background: #fff !important;
      color: #000;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
      font-family: Arial, sans-serif;
    }

    th, td {
      color: #000 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    * {
      box-shadow: none !important;
    }
  }
</style>
</head>
<body style="background-color: #fff;">
  <div style='max-width: 750px; font-family: monospace; margin: 20px auto; padding: 30px; border: 1px solid #ddd; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); background: #fff'>
    <header style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;'>
      <h1 style='color: #2B5F2C; margin: 0;'>Receipt</h1>
    </header>

    <section>
      <div style='display: flex; justify-content: space-between;'>
        <div>
          <p style="color: #666;"><strong>Company Name:</strong> ${userData.value?.institution_name}</p>
          <p style="color: #666;"><strong>P.O.Box:</strong> ${userData.value?.p_o_box +' '+userData.value?.address }</p>
        </div>
        <div style='text-align: right;'>
          <p style="color: #666;"><strong>Receipt #:</strong> ${commonService.generateRandomFiveId()}</p>
          <p style="color: #666;"><strong>Date:</strong> ${commonService.formatDate(commonService.getCurrentTimestamp())}</p>

        </div>
      </div>
    </section>



    <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
      <thead>
        <tr>
          <th style='border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #002060; color: #fff;'>QTY</th>
          <th style='border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #002060; color: #fff;'>DESCRIPTION</th>
          <th style='border: 1px solid #ddd; padding: 8px; text-align: right; background-color: #002060; color: #fff;'>UNIT PRICE</th>
          <th style='border: 1px solid #ddd; padding: 8px; text-align: right; background-color: #002060; color: #fff;'>AMOUNT</th>
        </tr>
      </thead>
      <tbody>
        ${productRows}
      </tbody>
      <tfoot>
        <tr>
          <td colspan='3' style='border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold; color: #666;'>Total</td>
          <td style='border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold; color: #666;'>${orderItems.value[0].total? commonService.commaSeparator( Number(orderItems.value[0].total) ) : 0}</td>
        </tr>
        <tr>
          <td colspan='3' style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>Amount Paid</td>
          <td style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>${commonService.commaSeparator( Number(orderItems.value[0].amount_paid))}</td>
        </tr>
        <tr>
          <td colspan='3' style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>Change</td>
          <td style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>${commonService.commaSeparator( Number(orderItems.value[0].amount_paid) - Number(orderItems.value[0].total))}</td>
        </tr>

      </tfoot>
    </table>

    <div style='margin-top: 20px; border: 1px solid #ddd; padding: 10px; background: #f2f2f2;'>
      <p style="color: #666;"><strong>Other Comments:</strong></p>
      <p style="color: #666;">1. Acknowledged with thanks.</p>

    </div>

    <div style='text-align: center; margin-top: 30px;'>
      <p style='font-size: 0.9em; color: #666;'>Thank you for your business!</p>
    </div>
  </div>
</body>
</html>`);
//   printWindow.document.close();
//   printWindow.print();
//   printWindow.close();
printWindow.document.close();
printWindow.onload = function () {
  printWindow.focus();
  printWindow.print();
  printWindow.close();
};
};

/**
 *
 The end testing code for editable table
 */

const close = () => {
    toggleInvoiceModel(false);
    printInvoice();
};

onMounted(() => {
    userData.value=commonService.getStorage().userData;
    getSales();
});

</script>
<template>
    <div className="card">
        <h5>View Sales</h5>
        <!--        <p>Use this page to start from scratch and place your custom content.</p>-->
        <DataTable :value="memberBatch" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
            :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
            <Column field="Ref No." header="Ref No." style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.ref_no }}
                </template>
            </Column>
            <Column field="Receipt No." header="Receipt No." style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.receipt_no }}
                </template>
            </Column>
            <Column field="name" header="Item Count" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.item_count }}
                </template>
            </Column>
            <!-- <Column field="name" header="Cost" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.total) }}
                </template>
            </Column> -->
            <Column field="name" header="Amount Paid" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.amount_paid) }}
                </template>
            </Column>
            <Column field="name" header="Cost" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.total) }}
                </template>
            </Column>
            <Column field="name" header="Change" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.amount_paid-data.total) }}
                </template>
            </Column>
            <Column header="Discount" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.discount) }}
                </template>
            </Column>
            <Column header="Status" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.payment_status }}
                </template>
            </Column>
            <Column header="Date" style="max-width: 10rem">
                <template #body="{ data }">
                    {{ data.created_at }}
                </template>
            </Column>
            <Column headerStyle="min-width:10rem;">
                <template #body="{ data }">
                    <Button icon="pi pi-eye" class="p-button-primary mr-2" @click="viewDetails(data)" v-tooltip="'View sell details'"/>
                                       <Button icon="pi pi-trash" class="p-button-danger mt-2"
                                               @click="openConfirmation(data)" v-tooltip="'Reverse sale'" />
                </template>
            </Column>
        </DataTable>


        <div class="card p-fluid">
            <!-- <h5>Confirmation</h5> -->
            <!-- <Button label="Delete" icon="pi pi-trash" class="p-button-danger" style="width: auto" @click="openConfirmation" /> -->
            <Dialog header="Confirmation" v-model:visible="displayConfirmation" :style="{ width: '350px' }" :modal="true">
                <div class="flex align-items-center justify-content-center">
                    <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                    <span>Are you sure you want to proceed?</span>
                </div>
                <template #footer>
                    <Button label="No" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                    <Button label="Yes" icon="pi pi-check" @click="deleteOrder" class="p-button-text" autofocus />
                </template>
            </Dialog>
        </div>



        <Dialog header="Sales Details" v-model:visible="showProductDetailsModal" :style="{ width: '33vw' }" :modal="true"
            class="p-fluid">
            <!-- <p style="padding: 20px;"> -->
            <div class="grid p-fluid">
                <div class="field col-12 md:col-12">
                    <h1>{{orderDetails.institution_name}}</h1>
                </div>
                <div class="field col-6 md:col-6">
                    <span class="p-float-label">
                        <!-- <InputText type="text" id="unitName" @blur="onTypeInputBlur(typeName, 'typeName')" v-model="typeName" :class="{ 'p-invalid': typeFormError?.typeName }" /> class="p-invalid" -->
                        <label for="unitName">Ref No.: {{ orderDetails.ref_no }} </label>
                    </span>
                </div>
                <div class="field col-6 md:col-6">
                    <span class="p-float-label">
                        <!-- <InputText type="text" id="unitName" @blur="onTypeInputBlur(typeName, 'typeName')" v-model="typeName" :class="{ 'p-invalid': typeFormError?.typeName }" /> class="p-invalid" -->
                        <label for="unitName">Receipt No.: {{ orderDetails.receipt_no }}</label>
                    </span>
                </div>
                <div class="field col-6 md:col-6">
                    <span class="p-float-label">
                        <!-- <InputText type="text" id="unitName" @blur="onTypeInputBlur(typeName, 'typeName')" v-model="typeName" :class="{ 'p-invalid': typeFormError?.typeName }" /> class="p-invalid" -->
                        <label for="unitName">Item Count: {{ orderDetails.item_count }}</label>
                    </span>
                </div>
                <div class="field col-6 md:col-6">
                    <span class="p-float-label">
                        <!-- <InputText type="text" id="unitName" @blur="onTypeInputBlur(typeName, 'typeName')" v-model="typeName" :class="{ 'p-invalid': typeFormError?.typeName }" /> class="p-invalid" -->
                        <label for="unitName">Total: {{ commonService.commaSeparator(orderDetails.total) }}</label>
                    </span>
                </div>
                <hr>
            </div>



            <div class="grid p-fluid">
                <DataTable :value="orderItems" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
                :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
                <Column field="Ref No." header="Item" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.name }}
                    </template>
                </Column>
                <Column field="Receipt No." header="Price" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.price) }}
                    </template>
                </Column>
                <Column field="name" header="Qty" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.qty }}
                    </template>
                </Column>
                <Column field="name" header="Sub Total" style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ commonService.commaSeparator(data.price * data.qty) }}
                    </template>
                </Column>
            </DataTable>
            </div>
            <!-- </p> -->
            <template #footer>
                <Button label="Cancel" @click="toggleTypeModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="printReceipt" icon="pi pi-print" label="Print" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>


        <!-- A4 Receipt generation -->
        <Dialog header="Generate Invoice" v-model:visible="showInvoiceModal" :style="{ width: '75vw' }" :modal="true"
            class="p-fluid">

            <!-- template -->

            <!DOCTYPE html>
            <html lang='en'>

            <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Invoice</title>
            </head>

            <body style="background-color: #fff;">
            <div style='max-width: 750px; font-family: monospace; margin: 20px auto; padding: 30px; border: 1px solid #ddd; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); background: #fff'>
                <header style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;'>
                <h1 style='color: #2B5F2C; margin: 0;'>Receipt</h1>
                <!-- <div style='width: 80px; height: 80px; background: #ccc; border-radius: 50%; text-align: center; line-height: 80px; font-size: 20px; font-weight: bold;'>LOGO</div> -->
                </header>

                <section>
                <div style='display: flex; justify-content: space-between;'>
                    <div>
                    <p style="color: #666;"><strong>Company Name:</strong> {{ userData?.institution_name }}</p>
                    <p style="color: #666;"><strong>P.O.Box:</strong> {{ userData?.p_o_box +' '+userData?.address }}</p>
                    </div>
                    <div style='text-align: right;'>
                    <p style="color: #666;"><strong>Receipt #:</strong> {{ commonService.generateRandomFiveId() }}</p>
                    <p style="color: #666;"><strong>Date:</strong> {{ commonService.formatDate(commonService.getCurrentTimestamp()) }}</p>
                    <!-- <p style="color: #666;"><strong>Due Date:</strong> 26/02/2019</p> -->
                    </div>
                </div>
                </section>

                <!-- <section style='margin-top: 20px;'>
                <div style='display: flex; justify-content: space-between;'>
                    <div>
                    <div style='background: #2B5F2C; padding: 5px 10px; font-weight: bold;'>BILL TO</div>
                    <p style="color: #666;">{{ customer?.name }}</p>
                    <p style="color: #666;">{{ billingAddress }}</p>
                    </div>
                    <div>
                    <div style='background: #2B5F2C; padding: 5px 10px; font-weight: bold;'>SHIP TO</div>
                    <p style="color: #666;">{{ customer?.name }}</p>
                    <p style="color: #666;">{{ billingAddress }}</p>
                    </div>
                </div>
                </section> -->

                <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                <thead>
                    <tr>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #2B5F2C; color: #fff;'>QTY</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #2B5F2C; color: #fff;'>DESCRIPTION</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: right; background-color: #2B5F2C; color: #fff;'>UNIT PRICE</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: right; background-color: #2B5F2C; color: #fff;'>AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for='(prod, index) of orderItems' :key='prod.id'>
                    <td style='border: 1px solid #ddd; padding: 8px; color: #666;'>{{ prod.qty	 }}</td>
                    <td style='border: 1px solid #ddd; padding: 8px; color: #666;'>{{ prod.name }}</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>{{ commonService.commaSeparator(Number(prod.price)) }}</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>{{ commonService.commaSeparator(Number(prod.price) * Number(prod.qty)	) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                    <td colspan='3' style='border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold; color: #666;'>Total</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold; color: #666;'>{{ orderItems[0].total	 ? commonService.commaSeparator( Number(orderItems[0].total) ) : 0 }}</td>
                    </tr>
                    <tr>
                        <td colspan='3' style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>Amount Paid</td>
                        <td style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>{{ commonService.commaSeparator( Number(orderItems[0].amount_paid	) ) }}</td>
                    </tr>
                    <tr>
                    <td colspan='3' style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>Change</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: right; color: #666;'>{{ commonService.commaSeparator( Number(orderItems[0].amount_paid	) - Number(orderItems[0].total)) }}</td>
                    </tr>

                </tfoot>
                </table>

                <div style='margin-top: 20px; border: 1px solid #ddd; padding: 10px; background: #f2f2f2;'>
                <p style="color: #666;"><strong>Other Comments:</strong></p>
                <p style="color: #666;">1. Acknowledged with thanks.</p>
                <!-- <p style="color: #666;">2. Please include the invoice number on your check.</p> -->
                </div>

                <div style='text-align: center; margin-top: 30px;'>
                <p style='font-size: 0.9em; color: #666;'>Thank you for your business!</p>
                <!-- <p style='font-size: 0.9em; color: #666;'>Payment is due within 15 days.</p> -->
                <!-- <p style='font-size: 0.9em; color: #666;'>Please make checks payable to: {{ commonService.getStorage()?.institution_name }}.</p> -->
                </div>
            </div>
            </body>

            </html>

            <!-- /template -->
            <template #footer>
                <Button label="print" @click="close" icon="pi pi-print" class="p-button-outlined" />
            </template>
        </Dialog>

    </div>
</template>
