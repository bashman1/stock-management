<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();


const productList = ref(null);
const selectedProduct = ref([]);

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
    let obj ={id: data.id, price:data.selling_price, quantity:1, discount:0, name:data.name };
    const foundItem = selectedProduct.value.find(item => item.id === obj.id);
    if (foundItem) {
        foundItem.quantity += obj.quantity;
    } else {
        selectedProduct.value.push(obj);
    }
}

const OnSelectRemoveItem=(data)=>{
    const index = selectedProduct.value.findIndex(item => item.id === data.id);
    alert(index)
    if (index !== -1) {
        selectedProduct.value.splice(index, 1);
        // console.log(`Object with id ${id} removed from the array.`);
    } else {
        // console.log(`Object with id ${id} not found in the array.`);
    }
}

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
                        <p>Use this page to start from scratch and place your custom content.</p>
                        <DataTable :value="productList" :paginator="true" class="p-datatable-gridlines" :rows="50"
                            dataKey="id" :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
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
                            <Column field="name" header="Selling Price" style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ data.selling_price }}
                                </template>
                            </Column>
                            <Column field="name" header="Quantity" style="min-width: 10rem">
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
                            <Column headerStyle="min-width:10rem;">
                                <template #body="{ data }">
                                    <Button icon="pi pi-file-edit" class="p-button-rounded p-button-success mr-2"
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
                        <DataTable :value="selectedProduct" :paginator="true" size="small" :rows="20"
                        dataKey="id" :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
                        <Column field="name" header="Name" style="min-width: 10rem">
                            <template #body="{ data }">
                                {{ data.name }}
                            </template>
                        </Column>
                        <Column field="price" header="Price" style="min-width: 10rem">
                            <template #body="{ data }">
                                {{ data.price }}
                            </template>
                        </Column>
                        <Column field="quantity" header="Qty" style="min-width: 10rem">
                            <template #body="{ data }">
                                <Button icon="pi pi-check" class="p-button-rounded p-button-text mr-2 mb-2" />
                                {{ data.quantity }}
                                <Button icon="pi pi-check" class="p-button-rounded p-button-text mr-2 mb-2" />
                            </template>
                        </Column>
                        <Column field="subtotal" header="Sub Total" style="min-width: 10rem">
                            <template #body="{ data }">
                                {{ data.quantity * data.price }}
                            </template>
                        </Column>
                        <Column headerStyle="min-width:10rem;">
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
                <h5>Total </h5><br>
                <div class="grid">
                    <!-- <div class="field col-12 md:col-6">
                        <span class="p-float-label">
                            <InputText type="text" @blur="onInputBlur(sellingPrice, 'sellingPrice')" id="sellingPrice" v-model="sellingPrice" :class="{ 'p-invalid': formError?.sellingPrice }"/>
                            <label for="sellingPrice">Selling Price </label>
                        </span>
                    </div> -->
                    <!-- <div class="field col-12 md:col-6">
                        <span class="p-float-label">
                            <InputText type="text" id="discount" v-model="discount" />
                            <label for="discount">Discount Rate</label>
                        </span>
                    </div> -->

                    <div class="field col-12 md:col-8"></div>
                    <div class="field col-12 md:col-4">
                        <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                    </div>
                </div>
            </div>
        </div>


        <!-- Start of Modals-->

        <!-- /End of the modals -->

    </div>
</template>
