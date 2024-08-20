<script setup>
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';
// import { ProductService } from '@/service/ProductService';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();

const productList = ref(null);
const selectedProduct = ref([{ id:null, price: null, quantity: null, discount: null, name: '', tax_config:null }]);
const amountTOPay = ref(0);
const discount = ref(0);
const balance = ref(0);
const filters = ref({});
const institutionDetails = ref({});
const extraTax= ref(0);

const totalInclusiveTax= ref(0);
const totalExclusiveTax= ref(0);
const showCreateCustomerModal= ref(false);
const customerFormError=ref({});
const customerName=ref(null);
const customerEmail=ref(null);
const customerAddress=ref(null);
const customerContact=ref(null);
const customerDesc=ref(null);
const customerData=ref([]);


const VAT = ref([
    {id:1, name:"18%", amount:0, total:0},
    {id:2, name:"Exempted", amount:0, total:0},
])

// **************************************************************************
const getProducts = () => {
    commonService.genericRequest('get-products', 'get', true, {}).then((response) => {
        if (response.status) {
            productList.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const OnSelectItem = (data) => {

    console.log(JSON.stringify(data));

    let obj = { id: data.id, price: data.selling_price, quantity: 1, discount: 0, name: data.name, tax_config:data.tax_config };
    const foundItem = selectedProduct.value.find(item => item.id === obj.id);
    if (foundItem) {
        foundItem.quantity += obj.quantity;
    } else {
        // selectedProduct.value.push(obj);

        let index = selectedProduct.value.length - 1;

// Insert the object at the calculated index
        selectedProduct.value.splice(index, 0, obj);

        // selectedProduct.value= selectedProduct.value.reverse();
    }
    if(institutionDetails?.value?.is_tax_enabled){
        computeVAT()
    }

}

const OnSelectRemoveItem = (data) => {
    const index = selectedProduct.value.findIndex(item => item.id === data.id);
    if (index !== -1) {
        selectedProduct.value.splice(index, 1);
    } else {
    }
}

const updateQty = (event, data) => {
    selectedProduct.value = selectedProduct.value.map(product => {
        if (product.id === data.id) {
            return { ...product, quantity: Number(event.target.value) };
        }
        return product;
    });
}

const updatePrice = (event, data)=>{
    selectedProduct.value = selectedProduct.value.map(product => {
        if (product.id === data.id) {
            return { ...product, price: Number(event.target.value) };
        }
        return product;
    });
}

const increaseReduce = (data, action) => {
    selectedProduct.value = selectedProduct.value.map(product => {
        if (product.id === data.id) {
            if (action == 'Add') {
                return { ...product, quantity: Number(product.quantity + 1) };
            } else {
                if (product.quantity > 0) {
                    return { ...product, quantity: Number(product.quantity - 1) };
                } else {
                    //    return OnSelectRemoveItem(data);
                }
            }
        }
        return product;
    });
    computeVAT();
}

const totalPrice = (array) => {
    let totalPrice = 0
    if (array.length > 0) {
        array.forEach(product => {
            totalPrice += product.quantity * product.price;
        });
    }
    return totalPrice;
}

const onSubmitOrder = () => {

    selectedProduct.value = selectedProduct?.value.filter(obj => obj.id != null);

    let postData = {
        total: totalPrice(selectedProduct?.value) - Number(discount?.value) + Number(extraTax.value),
        discount: Number(discount?.value),
        amountPaid: Number(amountTOPay?.value),
        itemCount: selectedProduct?.value.length,
        tranDate: saleDate.value,
        items: selectedProduct?.value,
        status: "Active",
        vat:  Number(extraTax.value),
        vatInc:  totalInclusiveTax.value,
        vatEx: totalExclusiveTax.value,
        payment_method:payBy?.value.value,
        address:billingAddress.value,
        customer_id:customer?.value?.id
    }

    commonService.genericRequest('create-order', 'post', true, postData).then((response) => {
        if (response.status) {
            // productList.value = response.data;
            commonService.showSuccess(toast, response.message);
            commonService.redirect(router, "/view-sales");
        } else {
            commonService.showError(toast, response.message);
        }
    })

}


const getInstitutionDetails=()=>{
    commonService.genericRequest('get-institution-details', 'get', true, {}).then((response) => {
        if (response.status) {
            institutionDetails.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS }
    };
};

const computeVAT = () => {
    let taxExTotal = 0;
    let taxInTotal = 0;
    let exemptedTotal = 0;

    selectedProduct.value.forEach(element => {
        const totalPrice = element.quantity * element.price;
        switch (element.tax_config) {
            case 'TAX_EXCLUSIVE':
                taxExTotal += totalPrice;
                break;
            case 'TAX_INCLUSIVE':
                taxInTotal += totalPrice;
                break;
            case 'TAX_EXEMPTED':
                exemptedTotal += totalPrice;
                break;
        }
    });

    const totalEx = 0.18 * taxExTotal;
    const totalIn = 0.18 * taxInTotal;

    totalExclusiveTax.value = totalEx;
    totalInclusiveTax.value = totalIn;

    extraTax.value = totalEx;

    VAT.value = VAT.value.map(item => {
        if (item.id === 1) {
            return {
                ...item,
                total: totalEx + totalIn,
                amount: taxExTotal + (taxInTotal - totalIn)
            };
        } else if (item.id === 2) {
            return {
                ...item,
                total: 0,
                amount: exemptedTotal
            };
        }
        return item;
    });
}

const toggleCustomerModal=(action)=>{
    showCreateCustomerModal.value=action;
}

const onCustomerInputBlur=(value, key)=>{
    customerFormError.value[key]= commonService.validateFormField(value);
}

const onSubmitCustomer = () => {
    customerFormError.value.customerName = commonService.validateFormField(customerName.value);

    let invalid = commonService.validateRequiredFields(customerFormError.value);

    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData ={
        name: customerName.value,
        // country_id: supplierCountry.value.id,
        // website:supplierWeb.value,
        address: customerAddress.value,
        email:customerEmail.value,
        phone_number:customerContact.value,
        description: customerDesc.value,
        status: "Active",
    }

    commonService.genericRequest('create-customer', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getCustomers();
            toggleCustomerModal(false);
            customerName.value=null;
            // supplierCountry.value=null;
            // supplierWeb.value=null;
            customerAddress.value=null;
            customerEmail.value=null;
            customerContact.value=null;
            customerDesc.value=null;

        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const getCustomers=()=>{
    commonService.genericRequest('get-customers', 'post', true, {}).then((response) => {
        if (response.status) {
            customerData.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onCustomerChange =(event)=>{
    if (event.value.address){
        billingAddress.value=event.value.address
    }
}


/**
 *
 The start testing code for editable table
 */
// const productService = new ProductService();
const products = ref([]);

    const payBy = ref({id:1, name: "Cash", value:"CASH"})
    const billingAddress =ref(null);
    const customer =ref(null);
    const saleDate =ref(new Date());
    const newTotal = ref(0);
    const paidAmount = ref(0);


const paymentOptions = ref([
    {id:1, name: "Cash", value:"CASH"},
    {id:2, name: "Bank", value:"BANK"},
    {id:3, name: "Credit", value:"CREDIT"},
])

const initializeProducts=()=>{
    return [
        {id: null, name:'', description: '', quantity: null, rate: null, amount: null, uuid: commonService.generateUUID()},
        {id: null, name:'', description: '', quantity: null, rate: null, amount: null, uuid: commonService.generateUUID()},
        {id: null, name:'', description: '', quantity: null, rate: null, amount: null, uuid: commonService.generateUUID()},
        {id: null, name:'', description: '', quantity: null, rate: null, amount: null, uuid: commonService.generateUUID()},
        {id: null, name:'', description: '', quantity: null, rate: null, amount: null, uuid: commonService.generateUUID()},
    ];
}



const columns = ref([
    { field: 'name', header: 'Product/Services' },
    { field: 'description', header: 'Description' },
    { field: 'quantity', header: 'Quantity' },
    { field: 'rate', header: 'Rate' },
    { field: 'amount', header: 'Amount' },
    { field: 'tax', header: 'Tax' },
]);


const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
}

const isPositiveInteger = (val) => {
    let str = String(val);

    str = str.trim();

    if (!str) {
        return false;
    }

    str = str.replace(/^0+/, '') || '0';
    var n = Math.floor(Number(str));

    return n !== Infinity && String(n) === str && n >= 0;
};


const onCellEditComplete = (event) => {
    let { data, newValue, field } = event;
    // const rowIndex = event.rowIndex;
    // alert(rowIndex)
    // console.log(event)
    // alert(JSON.stringify(event))

    computeNewSum()

    switch (field) {
        case 'quantity':
            if (isPositiveInteger(newValue)){
                data[field] = newValue;
                data.amount = data[field] * data.rate;
            }else{
                event.preventDefault();
                // alert(newValue)
                data[field] = 1;
                data.amount = data[field] * data.rate;
            }
            break
        case 'amount':
        case 'rate':
            if (isPositiveInteger(newValue)) data[field] = newValue;
            else event.preventDefault();
            break;
        case 'name':
            data[field] = newValue
        default:
            if ( newValue && typeof newValue === 'string' && newValue.trim().length > 0) data[field] = newValue;
            else event.preventDefault();
            break;
    }
};


const onDropdownChange=(uuid, name)=>{
let index = products.value.findIndex(element => element.uuid === uuid);
let value = products.value[index];
console.log('---------------------------------------------------------')
console.log(name)
    value.name = name.name;
    value.id = name.id;
    value.description = name.description;
    value.quantity = 1;
    value.rate = name.selling_price;
    value.amount = value.quantity * value.rate;
products.value[index] = value;
}


const getIndex=(uuid)=>{
    return products.value.findIndex(element => element.uuid === uuid);
}

const onCellEditInit=(event)=>{
    /** add a new raw **/
if((event.index+ 1)==products.value.length){
 addRow()
}
}


const addRow=()=>{
    products.value.push({id: null, name:'', description: '', quantity: null, rate: null, amount: null, uuid: commonService.generateUUID()})
}

const clearColumns=()=>{
    selectedProduct.value = [{ id:null, price: null, quantity: null, discount: null, name: '', tax_config:null }];
// products.value = initializeProducts();
}

const addColumns=()=>{
products.value = products.value.concat(initializeProducts());
}


const computeNewSum=()=>{
    let total=0;
    products.value.forEach(element => {
        total = total + (element.quantity * element.rate);
    });
   newTotal.value =total;
}


    const onSubmitNewOrder=()=>{
        console.log('****************************************************************')

        let sortedProducts = products.value.find(obj => obj.id != null);
        console.log(JSON.stringify(sortedProducts));
    }

/**
 *
 The end testing code for editable table
 */


onBeforeMount(() => {
    initFilters();
});

onMounted(() => {
    getProducts();
    getInstitutionDetails();
    getCustomers()
    products.value = initializeProducts();
});


</script>
<template>
    <div class="grid p-fluid">


                    <div class="field col-12 md:col-3">
<!--                        <div class="p-inputgroup">-->
                            <span class="p-float-label">
                                <Dropdown id="paymentMethod" :options="paymentOptions" filter v-model="payBy" optionLabel="name">
                                </Dropdown>
                                <label for="paymentMethod">Payment methods</label>
                            </span>
<!--                            <Button @click="toggleGaugeModal(true)" icon="pi pi-plus" />-->
<!--                        </div>-->
                    </div>

                    <div class="field col-12 md:col-3">
                        <div class="p-inputgroup">
                            <span class="p-float-label">
                                <Dropdown id="customer" @change="onCustomerChange" :options="customerData" filter v-model="customer" optionLabel="name">
                                </Dropdown>
                                <label for="Customer">Select customer</label>
                            </span>
                            <Button @click="toggleCustomerModal(true)" icon="pi pi-plus" />
                        </div>
                    </div>

                    <div class="field col-12 md:col-3">
                        <span class="p-float-label">
                            <InputText type="text" id="productId" v-model="billingAddress" /> <!-- class="p-invalid"-->
                            <label for="productId">Billing address</label>
                        </span>
                    </div>

                                      <div class="field col-12 md:col-3">
                        <span class="p-float-label">
                            <Calendar id="date" v-model="saleDate"></Calendar>
                            <label for="date">Sales Date</label>
                        </span>
                    </div>


    <!-- start testing editable table -->
                <div class="col-12 md:col-3"></div>
                <div class="col-12 md:col-3"></div>
                <div class="col-12 md:col-3"></div>
                <div class="col-12 md:col-3"> <p class="font-bold text-right text-6xl green-color">Total: {{  totalPrice(selectedProduct) ? commonService.commaSeparator(totalPrice(selectedProduct) - Number(discount) + Number(extraTax)) : 0 }}</p></div>

                <div class="col-12 md:col-12">
                    <div class="card">


                        <!-- <div class="field col-12 md:col-12"> -->
                        <!-- <p>Items available in the stock.</p> -->
                        <DataTable :value="selectedProduct" :size="'small'" stripedRows :rows="20" dataKey="id" :rowHover="true"
                            filterDisplay="menu" responsiveLayout="scroll">
                            <Column field="name" header="Name" style="max-width: 10rem;">
                                <template #body="{ data }" >
                        <Dropdown id="subCategory" change="" :options="productList" filter v-if="!data.id"
                         optionLabel="name" @change="OnSelectItem($event.value)">
                                </Dropdown>
                                    {{ data.name }}
                                </template>
                            </Column>

                            <Column field="quantity" header="Qty" style="min-width: 10rem; ">
                                <template #body="{ data }">

                                    <i class="pi pi-minus" @click="increaseReduce(data, 'Subtract')" v-if="data.id"
                                    style="font-size: 1rem; margin-left:3px; cursor:pointer"></i>
                                    <!-- <Button icon="pi pi-minus" class="p-button-rounded p-button-text mr-2 mb-2" /> -->
                                    <InputText type="text" @change="updateQty($event, data)" :value="data.quantity" v-if="data.id"
                                        style="width: 100px; padding:5px; margin-left: 1px; margin-right:1px; text-align:center"
                                        placeholder="Qty"></InputText>
                                    <!-- {{ data.quantity }} -->
                                    <i class="pi pi-plus" @click="increaseReduce(data, 'Add')" v-if="data.id"
                                    style="font-size: 1rem; margin-right:3px; cursor:pointer"></i>

                                    <!-- <Button icon="pi pi-plus" class="p-button-rounded p-button-text mr-2 mb-2" /> -->
                                </template>
                            </Column>
                            <Column field="price" header="Price" style="max-width: 10rem;">
                                <template #body="{ data }">
                                      <InputText type="text" @change="updatePrice($event, data)" :value="data.price" v-if="data.id"
                                        style="width: 80%; padding:5px; margin-left: 1px; margin-right:1px; text-align:center"
                                        placeholder="Qty"></InputText>
                                    <!-- {{ commonService.commaSeparator(data.price) }} -->
                                </template>
                            </Column>
                            <Column field="subtotal" header="Sub Total" style="max-width: 10rem;">
                                <template #body="{ data }">
                                    {{ data.id?commonService.commaSeparator(data.quantity * data.price):'' }}
                                </template>
                            </Column>
                            <Column headerStyle="max-width:10rem;">
                                <template #body="{ data }">
                                    <Button icon="pi pi-trash" class="p-button-rounded p-button-danger mr-2" v-if="data.id"
                                        @click="OnSelectRemoveItem(data)" />
                                </template>
                            </Column>
                        </DataTable>
                    <!-- </div> -->


        <!-- <DataTable :value="products" stripedRows showGridlines editMode="cell" @cell-edit-complete="onCellEditComplete" @cell-edit-init="onCellEditInit"
            :pt="{
                table: { style: 'min-width: 30rem' },
                column: {
                    bodycell: ({ state }) => ({
                        class: [{ 'pt-0 pb-0': state['d_editing'] }]
                    })
                }
            }"
        >
            <Column v-for="(col, colIndex) of columns" :key="col.field" :field="col.field" :header="col.header" style="width: 15%">
                <template #body="{ data, field }">
                    {{ field === 'price' ? formatCurrency(data[field]) : data[field] }}
                </template>
                <template #editor="{ data, field }">
                    <template v-if="field == 'name'">
                        <Dropdown id="subCategory" change="" :options="productList" filter
                        v-model="data[field]" optionLabel="name" @change="onDropdownChange(data.uuid, data.name)">
                                </Dropdown>
                    </template>
                    <template v-else-if="field === 'description'">
                        <InputText v-model="data[field]" fluid />
                    </template>
                    <template v-else>
                        <InputNumber v-model="data[field]" fluid />
                    </template>
                </template>
            </Column>
        </DataTable> -->
    </div>
                </div>
                 <div class="col-12 md:col-1">
                    <Button label="Clear" @click="clearColumns" icon="pi pi-minus" iconPos="left" class=" p-button-danger mr-2 mb-2" />
                </div>
                <div class="col-12 md:col-1">

                </div>

                <div class="col-12 md:col-4"></div>
                <div class="col-12 md:col-3"></div>
                <div class="col-12 md:col-3"> <p class="font-bold text-right text-xl green-color">Total: {{ totalPrice(selectedProduct) ? commonService.commaSeparator(totalPrice(selectedProduct) - Number(discount) + Number(extraTax)) : 0 }}</p></div>

                <div class="col-12 md:col-3"></div>
                <div class="col-12 md:col-3">
                        <span class="p-float-label">
                            <InputText type="text" id="discount" v-model="amountTOPay" /> <!-- class="p-invalid"-->
                            <label for="productId">Amount</label>
                        </span>
                </div>
                <div class="col-12 md:col-3">
                <p class="font-bold text-left text-xl green-color" v-if="amountTOPay && amountTOPay > 0">Change: {{ commonService.commaSeparator(Number(amountTOPay) + Number(discount) - totalPrice(selectedProduct)) }}</p>
                </div>
                <div class="col-12 md:col-3">
                 <Button @click="onSubmitOrder" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                    <!-- <Button @click="onSubmitNewOrder" label="SUBMIT" class="p-button-outlined mr-2 mb-2" /> -->
                </div>

                <!-- end testing editable table -->


        <!-- <div class="col-12 md:col-7">
            <div class="card">
                <h5>Point Of Sale</h5><br>
                <div class="grid">
                    <div class="col-12 md:col-12">
                        <p>Items available in the stock.</p>
                        <DataTable :value="productList" :paginator="true"  size="small" class="p-datatable-gridlines" :rows="50"
                            dataKey="id" :filters="filters" :rowHover="true" filterDisplay="menu"
                            responsiveLayout="scroll">
                            <template #header>
                                <div
                                    class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">

                                    <span class="block mt-2 md:mt-0 p-input-icon-left">
                                        <i class="pi pi-search" />
                                        <InputText v-model="filters['global'].value" placeholder="Search..." />
                                    </span>
                                </div>
                            </template>
                            <Column field="name" header="Product" style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.name }}
                                </template>
                            </Column>

                            <Column field="name" header="Category" style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.category_name }}
                                </template>
                            </Column>

                            <Column field="name" header="Price" style="max-width: 10rem">
                                <template #body="{ data }">
                                    {{ commonService.commaSeparator(data.selling_price) }}
                                </template>
                            </Column>
                            <Column field="name" header="Quantity" style="max-width: 15rem">
                                <template #body="{ data }">
                                    {{ data.quantity + ' ' + data.unit }}
                                </template>
                            </Column>
                            <Column header="Manufacturer" style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.manufacturer }}
                                </template>
                            </Column>

                            <Column header="Date" style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.created_at }}
                                </template>
                            </Column>
                            <Column headerStyle="max-width:10rem;">
                                <template #body="{ data }">
                                    <Button icon="pi pi-check" class="p-button-success mr-2"  v-tooltip="'Select product'"
                                        @click="OnSelectItem(data)" />
                                </template>
                            </Column>
                        </DataTable>

                    </div>
                </div>
            </div>
        </div> -->


        <!-- <div class="col-12 md:col-5">
            <div class="card">
                <h5>Selected Items</h5><br>
                <div class="grid">
                    <div class="field col-12 md:col-12">
                        <DataTable :value="selectedProduct" size="small" :rows="20" dataKey="id" :rowHover="true"
                            filterDisplay="menu" responsiveLayout="scroll">
                            <Column field="name" header="Name" style="max-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.name }}
                                </template>
                            </Column>
                            <Column field="price" header="Price" style="max-width: 10rem">
                                <template #body="{ data }">
                                    {{ commonService.commaSeparator(data.price) }}
                                </template>
                            </Column>
                            <Column field="quantity" header="Qty" style="min-width: 10rem">
                                <template #body="{ data }">

                                    <i class="pi pi-minus" @click="increaseReduce(data, 'Subtract')"
                                    style="font-size: 1rem; margin-left:3px; cursor:pointer"></i>
                                    <InputText type="text" @change="updateQty($event, data)" :value="data.quantity"
                                        style="width: 60px; padding:5px; margin-left: 1px; margin-right:1px; text-align:center"
                                        placeholder="Qty"></InputText>
                                    <i class="pi pi-plus" @click="increaseReduce(data, 'Add')"
                                    style="font-size: 1rem; margin-right:3px; cursor:pointer"></i>
                                </template>
                            </Column>
                            <Column field="subtotal" header="Sub Total" style="max-width: 10rem">
                                <template #body="{ data }">
                                    {{ commonService.commaSeparator(data.quantity * data.price) }}
                                </template>
                            </Column>
                            <Column headerStyle="max-width:10rem;">
                                <template #body="{ data }">
                                    <Button icon="pi pi-trash" class="p-button-rounded p-button-danger mr-2"
                                        @click="OnSelectRemoveItem(data)" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>

            <div class="card" v-if="institutionDetails?.is_tax_enabled && selectedProduct.length>0">
                <h5>V.A.T 18%</h5><br>
                <div class="grid">
                    <div class="field col-12 md:col-12">
                        <DataTable :value="VAT" size="small" :rows="20" dataKey="id" :rowHover="true"
                        filterDisplay="menu" responsiveLayout="scroll">
                        <Column field="name" header="18%" style="max-width: 10rem">
                            <template #body="{ data }">
                                {{ data.name }}
                            </template>
                        </Column>
                        <Column field="price" header="Amount" style="max-width: 10rem">
                            <template #body="{ data }">
                                {{ commonService.commaSeparator(data.amount) }}
                            </template>
                        </Column>
                        <Column field="subtotal" header="V.A.T" style="max-width: 10rem">
                            <template #body="{ data }">
                                {{ commonService.commaSeparator(data.total) }}
                            </template>
                        </Column>
                    </DataTable>
                    </div>
                </div>
            </div>


            <div class="card">
                <h5>Total </h5><br>
                <div class="grid">
                    <div class="field col-12 md:col-4">
                        <span class="p-float-label green-color">
                            Amount To Pay
                        </span>
                    </div>
                    <div class="field col-12 md:col-8">
                        <span class="p-float-label green-color">
                            {{ totalPrice(selectedProduct) ? commonService.commaSeparator(totalPrice(selectedProduct) -
                            Number(discount) + Number(extraTax)) : 0 }}
                        </span>
                    </div>
                    <div class="field col-12 md:col-4">
                        <span class="p-float-label green-color">
                            Discount
                        </span>
                    </div>
                    <div class="field col-12 md:col-8">
                        <span class="p-float-label">
                            <InputText type="text" id="discount" v-model="discount" />
                            <label for="discount">Discount Amount</label>
                        </span>
                    </div>
                    <div class="field col-12 md:col-4">
                        <span class="p-float-label green-color">
                            Amount Paid
                        </span>
                    </div>
                    <div class="field col-12 md:col-8">
                        <span class="p-float-label">
                            <InputText type="text" id="discount" v-model="amountTOPay" />
                            <label for="discount">Amount</label>
                        </span>
                    </div>
                    <div class="field col-12 md:col-4">
                        <span class="p-float-label green-color">
                            Change
                        </span>
                    </div>
                    <div class="field col-12 md:col-8">
                        <span class="p-float-label green-color" v-if="amountTOPay && amountTOPay > 0">
                            {{
                            commonService.commaSeparator(Number(amountTOPay) + Number(discount) - totalPrice(selectedProduct))
                            }}
                        </span>
                        <span class="p-float-label" v-if="!amountTOPay || amountTOPay==0">0</span>
                    </div>

                    <div class="field col-12 md:col-8"></div>
                    <div class="field col-12 md:col-4">
                        <Button @click="onSubmitOrder" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                    </div>
                </div>
            </div>
        </div> -->


        <!-- Start of Modals-->
        <Dialog header="Create new customer" v-model:visible="showCreateCustomerModal" :style="{ width: '75vw' }"
                :modal="true" class="p-fluid">
            <p style="padding-top: 20px;">
                <div class="grid p-fluid">
                    <div class="field col-12 md:col-6">
                    <span class="p-float-label">
                        <InputText type="text" id="customerName" @blur="onCustomerInputBlur(customerName, 'customerName')" v-model="customerName"  :class="{ 'p-invalid': customerFormError?.customerName }"/> <!-- class="p-invalid"-->
                        <label for="supplierName">Name</label>
                    </span>
                    </div>
                    <div class="field col-12 md:col-6">
                    <span class="p-float-label">
                        <InputText type="text" id="supplierEmail" v-model="customerEmail" /> <!-- class="p-invalid"-->
                        <label for="supplierEmail">Email</label>
                    </span>
                    </div>
                    <div class="field col-12 md:col-6">
                    <span class="p-float-label">
                        <InputText type="text" id="supplierAddress" v-model="customerAddress" /> <!-- class="p-invalid"-->
                        <label for="supplierAddress">Address</label>
                    </span>
                    </div>

                    <div class="field col-12 md:col-6">
                    <span class="p-float-label">
                        <InputText type="text" id="supplierContact" v-model="customerContact" /> <!-- class="p-invalid"-->
                        <label for="supplierContact">Phone Number</label>
                    </span>
                    </div>
                    <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="supplierDesc" rows="7" v-model="customerDesc"></Textarea>
                        <label for="supplierDesc">More Info</label>
                    </span>
                    </div>
                </div>
            </p>
            <template #footer>
                <Button label="Cancel" @click="toggleCustomerModal(false)" icon="pi pi-times"
                        class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="onSubmitCustomer" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>
        <!-- /End of the modals -->

    </div>
</template>
