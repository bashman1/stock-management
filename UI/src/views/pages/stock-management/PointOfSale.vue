<script setup>
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();


const productList = ref(null);
const selectedProduct = ref([]);
const amountTOPay = ref(0);
const discount = ref(0);
const balance = ref(0);
const filters = ref({});

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
    let obj = { id: data.id, price: data.selling_price, quantity: 1, discount: 0, name: data.name };
    const foundItem = selectedProduct.value.find(item => item.id === obj.id);
    if (foundItem) {
        foundItem.quantity += obj.quantity;
    } else {
        selectedProduct.value.push(obj);
    }
}

const OnSelectRemoveItem = (data) => {
    const index = selectedProduct.value.findIndex(item => item.id === data.id);
    if (index !== -1) {
        selectedProduct.value.splice(index, 1);
        // console.log(`Object with id ${id} removed from the array.`);
    } else {
        // console.log(`Object with id ${id} not found in the array.`);
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
    let postData = {
        total: totalPrice(selectedProduct?.value) - Number(discount?.value),
        discount: Number(discount?.value),
        amountPaid: Number(amountTOPay?.value),
        itemCount: selectedProduct?.value.length,
        tranDate: new Date(),
        items: selectedProduct?.value,
        status: "Active"
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

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS }
    };
};

onBeforeMount(() => {
    initFilters();
});

onMounted(() => {
    getProducts()
});


</script>
<template>
    <div class="grid p-fluid">
        <div class="col-12 md:col-7">
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
                                    <!-- <h5 class="m-0">Manage Products</h5> -->
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
                            <!-- <Column field="name" header="Product ID" style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.product_no }}
                                </template>
                            </Column> -->
                            <Column field="name" header="Category" style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.category_name }}
                                </template>
                            </Column>
                            <!-- <Column field="name" header="Sub Category" style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.sub_category_name }}
                                </template>
                            </Column> -->
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
                            <!-- <Column header="Institution" style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.institution_name }}
                                </template>
                            </Column>
                            <Column header="Branch" style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.branch_name }}
                                </template>
                            </Column> -->
                            <Column header="Date" style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.created_at }}
                                </template>
                            </Column>
                            <Column headerStyle="max-width:10rem;">
                                <template #body="{ data }">
                                    <Button icon="pi pi-check" class="p-button-rounded p-button-success mr-2"
                                        @click="OnSelectItem(data)" />
                                </template>
                            </Column>
                        </DataTable>

                    </div>
                </div>
            </div>
        </div>


        <div class="col-12 md:col-5">
            <div class="card">
                <h5>Selected Items</h5><br>
                <div class="grid">
                    <div class="field col-12 md:col-12">
                        <!-- <p>Items available in the stock.</p> -->
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
                                    <!-- <Button icon="pi pi-minus" class="p-button-rounded p-button-text mr-2 mb-2" /> -->
                                    <InputText type="text" @change="updateQty($event, data)" :value="data.quantity"
                                        style="width: 60px; padding:5px; margin-left: 1px; margin-right:1px; text-align:center"
                                        placeholder="Qty"></InputText>
                                    <!-- {{ data.quantity }} -->
                                    <i class="pi pi-plus" @click="increaseReduce(data, 'Add')"
                                    style="font-size: 1rem; margin-right:3px; cursor:pointer"></i>

                                    <!-- <Button icon="pi pi-plus" class="p-button-rounded p-button-text mr-2 mb-2" /> -->
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

            <div class="card">
                <h5>V.A.T Tax 18%</h5><br>
                <div class="grid">
                    <div class="field col-12 md:col-4">
                        <span class="p-float-label">
                            Product
                        </span>
                    </div>
                    <div class="field col-12 md:col-4">
                        <span class="p-float-label">
                            Total
                        </span>
                    </div>
                    <div class="field col-12 md:col-4">
                        <span class="p-float-label">
                        V.A.T
                        </span>
                    </div>
                    <!-- <div class="field col-12 md:col-8">
                        <span class="p-float-label">
                            {{ totalPrice(selectedProduct) ? commonService.commaSeparator(totalPrice(selectedProduct) -
                            Number(discount)) : 0 }}
                        </span>
                    </div> -->
                    <!-- <div class="field col-12 md:col-4">
                        <span class="p-float-label">
                            Discount
                        </span>
                    </div> -->
                    <!-- <div class="field col-12 md:col-8">
                        <span class="p-float-label">
                            <InputText type="text" id="discount" v-model="discount" />
                            <label for="discount">Discount Amount</label>
                        </span>
                    </div> -->
                    <!-- <div class="field col-12 md:col-4">
                        <span class="p-float-label">
                            Amount Paid
                        </span>
                    </div> -->

                </div>
            </div>


            <div class="card">
                <h5>Total </h5><br>
                <div class="grid">
                    <div class="field col-12 md:col-4">
                        <span class="p-float-label">
                            Amount To Pay
                        </span>
                    </div>
                    <div class="field col-12 md:col-8">
                        <span class="p-float-label">
                            {{ totalPrice(selectedProduct) ? commonService.commaSeparator(totalPrice(selectedProduct) -
                            Number(discount)) : 0 }}
                        </span>
                    </div>
                    <div class="field col-12 md:col-4">
                        <span class="p-float-label">
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
                        <span class="p-float-label">
                            Amount Paid
                        </span>
                    </div>
                    <div class="field col-12 md:col-8">
                        <span class="p-float-label">
                            <InputText type="text" id="discount" v-model="amountTOPay" />
                            <label for="discount">Amount</label>
                            <!-- <small class="p-invalid ">Enter.</small> -->
                        </span>
                    </div>
                    <div class="field col-12 md:col-4">
                        <span class="p-float-label">
                            Change
                        </span>
                    </div>
                    <div class="field col-12 md:col-8">
                        <span class="p-float-label" v-if="amountTOPay && amountTOPay > 0">
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
        </div>


        <!-- Start of Modals-->

        <!-- /End of the modals -->

    </div>
</template>
