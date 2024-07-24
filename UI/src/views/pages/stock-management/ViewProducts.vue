<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const memberBatch = ref(null);
const showRestock = ref(false);
const selectedProduct = ref({});
const commonService = new CommonService();
const unitName= ref(null);
const unitDesc= ref(null);
const unitFormError=ref({});
const measurementUnitsData=ref(null);
const formError = ref({});
const quantity = ref(null);
const minStock = ref(null);
const maxStock = ref(null);
const measurementUnit = ref(null);
const manufacturedDate=ref(null);
const expiryDate=ref(null);

const purchasePrice = ref(null);
const date = ref(null);
const sellingPrice = ref(null);
const discount = ref(null);

const deleteProductsDialog = ref(false);

const router = useRouter();
const route = useRoute()
const showMeasurementUnitModal = ref(false);

const getMemberBatch = () => {
    commonService.genericRequest('get-products', 'get', true, {}).then((response) => {
        if (response.status) {
            memberBatch.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}
const onInputBlur=(value, key)=>{
    formError.value[key] = commonService.validateFormField(value);
}

const onUnitInputBlur=(value, key)=>{
    unitFormError.value[key]= commonService.validateFormField(value);
}

const goToDetails = (event) => {
    router.push("/view-product-details/" + event?.id);
}

const editProduct = (event) => {
    router.push("/create-product/" + event?.id);
}

const restock = (data) => {
    // alert(JSON.stringify(data))
    selectedProduct.value = data;
    showRestock.value = true;
}

const toggleMeasurementUnitModal = (data) => {
    showMeasurementUnitModal.value = data;

}

const toggleConfirmDialog=(data)=>{
    selectedProduct.value = data;
    deleteProductsDialog.value=true;
}

const goToApprove = (event) => {
    router.push("/get-products" + event?.id);
}

const onSubmit= ()=>{
    formError.value.quantity=commonService.validateFormField(quantity.value);
    formError.value.measurementUnit=commonService.validateFormField(measurementUnit.value);
    formError.value.sellingPrice=commonService.validateFormField(sellingPrice.value);
    // formError.value.name=commonService.validateFormField(name.value);
    let invalid = commonService.validateRequiredFields(formError.value);
    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData={
        quantity: quantity?.value,
        min_quantity:minStock?.value,
        max_quantity: maxStock?.value,
        measurement_unit_id: measurementUnit?.value?.id,
        purchase_price: purchasePrice?.value,
        manufactured_date:manufacturedDate?.value,
        expiry_date:expiryDate?.value,
        selling_price: sellingPrice?.value,
        date:date?.value,
        productId: selectedProduct.value.id,
        status: "Active"
    }
    console.log(postData)
    commonService.genericRequest('restock-product', 'post', true, postData).then((response) => {
        if (response.status) {
            quantity.value = null;
            minStock.value = null;
            maxStock.value = null;
            measurementUnit.value = null;
            purchasePrice.value = null;
            manufacturedDate.value = null;
            expiryDate.value = null;
            sellingPrice.value = null;
            date.value = null;
            onCancel();
            getMemberBatch();
            commonService.showSuccess(toast, response.message);
        //    console.log(response.data);
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const onCancel=()=>{
    showRestock.value = false;
}


const onSubmitUnit=()=>{
    unitFormError.value.unitName = commonService.validateFormField(unitName.value);
    let invalid = commonService.validateRequiredFields(unitFormError.value);
    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }
    let postData={
        name: unitName.value,
        description: unitDesc.value,
        status: "Active"
    }
    commonService.genericRequest('create-measurement-unit', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getMeasurementUnit();
            toggleMeasurementUnitModal(false);
            unitName.value=null;
            unitDesc.value=null;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getMeasurementUnit=()=>{
    commonService.genericRequest('get-measurement-unit', 'get', true, {}).then((response) => {
        if (response.status) {
            measurementUnitsData.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const archiveProduct=()=>{
    let postData={
        status:"Archived",
        productId: selectedProduct?.value?.id
    }
    commonService.genericRequest("archive-product", 'post', true, postData).then((response) => {
        if (response.status) {
            getMemberBatch();
            commonService.showSuccess(toast, response.message);
            deleteProductsDialog.value = false
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

onMounted(() => {
    getMemberBatch();
    getMeasurementUnit();
});

</script>
<template>
    <div className="card">
        <h5>View Products</h5>
        <!--        <p>Use this page to start from scratch and place your custom content.</p>-->
        <DataTable :value="memberBatch" :paginator="true" class="p-datatable-gridlines" :rows="10" dataKey="id"
            :rowHover="true" filterDisplay="menu" responsiveLayout="scroll">
            <Column field="name" header="Product" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.name }}
                </template>
            </Column>
            <Column field="name" header="Ref. No" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.ref_no }}
                </template>
            </Column>
            <Column field="name" header="Product ID" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.product_no }}
                </template>
            </Column>
            <Column field="name" header="Category" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.category_name }}
                </template>
            </Column>
            <Column field="name" header="Sub Category" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.sub_category_name }}
                </template>
            </Column>
            <Column field="name" header="Selling Price" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.commaSeparator(data.selling_price) }}
                </template>
            </Column>
            <Column field="name" header="Quantity" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.quantity }}
                </template>
            </Column>
            <Column field="name" header="Unit" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.unit }}
                </template>
            </Column>
            <Column header="Manufacturer" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.manufacturer }}
                </template>
            </Column>
            <Column header="Institution" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.institution_name }}
                </template>
            </Column>
            <Column header="Branch" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.branch_name }}
                </template>
            </Column>
            <Column header="Date" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ data.created_at }}
                </template>
            </Column>
            <Column headerStyle="min-width:17rem;">
                <template #body="{ data }">
                    <Button icon="pi pi-eye" @click="goToDetails(data)" class="p-button-primary mr-2"  v-tooltip="'View product details'" />
                    <Button icon="pi pi-pencil" @click="editProduct(data)" class="p-button-success mr-2"  v-tooltip="'Edit product details'" />
                    <Button icon="pi pi-refresh" @click="restock(data)" class="p-button-warning mr-2"   v-tooltip="'Restock product'"/>
                    <Button icon="pi pi-trash" class="p-button-danger mr-2" @click="toggleConfirmDialog(data)"  v-tooltip="'Archive product'" />
                </template>
            </Column>
        </DataTable>


        <Dialog v-model:visible="showRestock" :style="{ width: '900px' }" :header="'Restock ' + selectedProduct.name"
            :modal="true" class="p-fluid">
            <div class="field">
                <label class="mb-3">Product info</label>
                <div class="formgrid grid">
                    <div class="field-radiobutton col-4">
                        <div class="formgrid grid">
                            <div class="field-radiobutton col-12">
                                <p style="text-align: justify;">Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita voluptas libero beatae, sapiente autem voluptatum atque. Optio ad animi id tempora eius corporis, nam aliquid quos amet cumque molestias ullam.</p>
                            </div>
                            <div class="field-radiobutton col-12">
                                <p style="text-align: justify;">Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita voluptas libero beatae, sapiente autem voluptatum atque. Optio ad animi id tempora eius corporis, nam aliquid quos amet cumque molestias ullam.</p>
                            </div>
                        </div>
                    </div>
                    <div class="field-radiobutton col-8">
                        <div class="formgrid grid">

                            <div class="field col-12 md:col-6">
                                <span class="p-float-label">
                                    <InputText type="text" id="quantity" @blur="onInputBlur(quantity, 'quantity')" v-model.trim="quantity" :class="{ 'p-invalid': formError?.quantity }"/>
                                    <label for="quantity">Quantity</label>
                                </span>
                            </div>

                            <div class="field col-12 md:col-6">
                                <span class="p-float-label">
                                    <InputText type="text" id="minStock" v-model="minStock" />
                                    <label for="minStock">Minimum Stock Level</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-6">
                                <span class="p-float-label">
                                    <InputText type="text" id="maxStock" v-model="maxStock" />
                                    <label for="maxStock">Maximum Stock Level</label>
                                </span>
                            </div>
                            <div class="field col-12 md:col-6">
                                <div class="p-inputgroup">
                                    <span class="p-float-label">
                                        <Dropdown id="measurementUnit" :options="measurementUnitsData" filter @blur="onInputBlur(measurementUnit, 'measurementUnit')" v-model="measurementUnit" :class="{ 'p-invalid': formError?.measurementUnit }" optionLabel="name">
                                        </Dropdown>
                                        <label for="measurementUnit">Measurement Unit</label>
                                    </span>
                                    <Button @click="toggleMeasurementUnitModal(true)" icon="pi pi-plus" />
                                </div>
                            </div>

                            <div class="field col-12 md:col-6">
                                <span class="p-float-label">
                                    <InputText type="text" id="purchasePrice" v-model="purchasePrice"  />
                                    <label for="purchasePrice">Unit Purchase Price </label>
                                </span>
                            </div>


                            <div class="field col-12 md:col-6">
                                <span class="p-float-label">
                                    <Calendar id="date" v-model="date"></Calendar>
                                    <label for="date">Stock Date</label>
                                </span>
                            </div>

                            <div class="field col-12 md:col-6">
                                <span class="p-float-label">
                                    <Calendar id="date" v-model="manufacturedDate"></Calendar>
                                    <label for="date">Manufactured Date</label>
                                </span>
                            </div>

                            <div class="field col-12 md:col-6">
                                <span class="p-float-label">
                                    <Calendar id="date" v-model="expiryDate"></Calendar>
                                    <label for="date">Expiry Date</label>
                                </span>
                            </div>

                            <!-- <div class="field col-12 md:col-6" v-if="institutionDetails?.is_tax_enabled">
                                <span class="p-float-label">
                                    <Dropdown id="taxableConfig" :options="taxExempted" v-model="taxableConfigs" @blur="onInputBlur(taxableConfigs, 'taxableConfigs')" :class="{ 'p-invalid': formError?.taxableConfigs }" optionLabel="name"></Dropdown>
                                    <label for="taxableConfig">VAT Config</label>
                                </span>
                            </div> -->

                            <div class="field col-12 md:col-6">
                                <span class="p-float-label">
                                    <InputText type="text" @blur="onInputBlur(sellingPrice, 'sellingPrice')" id="sellingPrice" v-model="sellingPrice" :class="{ 'p-invalid': formError?.sellingPrice }"/>
                                    <label for="sellingPrice">Unit Selling Price </label>
                                </span>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

            <template #footer>
                <Button label="CANCEL" icon="pi pi-times" class="p-button-outlined p-button-danger mr-2 mb-2" @click="onCancel" />
                <Button label="SUBMIT" icon="pi pi-check" class="p-button-outlined mr-2 mb-2" @click="onSubmit" />
            </template>
        </Dialog>

        <Dialog header="Create Measurement Unit" v-model:visible="showMeasurementUnitModal" :style="{ width: '30vw' }"
        :modal="true" class="p-fluid">
            <p style="padding-top: 20px;">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <InputText type="text" id="unitName" @blur="onUnitInputBlur(unitName, 'unitName')" v-model="unitName" :class="{ 'p-invalid': unitFormError?.unitName }" /> <!-- class="p-invalid"-->
                        <label for="unitName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="unitDesc" rows="7" v-model="unitDesc"></Textarea>
                        <label for="unitDesc">Description</label>
                    </span>
                </div>
        </div>
        </p>
        <template #footer>
            <Button label="Cancel" @click="toggleMeasurementUnitModal(false)" icon="pi pi-times"
                class="p-button-outlined p-button-danger mr-2 mb-2" />
            <Button @click="onSubmitUnit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
        </template>
    </Dialog>


    <Dialog v-model:visible="deleteProductsDialog" :style="{ width: '450px' }" :header="'Confirm'" :modal="true">
        <div class="flex align-items-center justify-content-center">
            <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
            <span>Are you sure you want to archive {{ selectedProduct?.name }}?</span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" class="p-button-outlined p-button-danger mr-2 mb-2" @click="deleteProductsDialog = false" />
            <Button label="Yes" icon="pi pi-check" class="p-button-outlined mr-2 mb-2" @click="archiveProduct" />
        </template>
    </Dialog>

    </div>
</template>
