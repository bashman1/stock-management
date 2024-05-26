<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();
const route = useRoute()

const name = ref(null);
const category=ref(null)
const subCategory  = ref(null);
const manufacturer  = ref(null);
const supplier  = ref(null);
const productId = ref(null);
const description = ref(null);

const quantity = ref(null);
const minStock = ref(null);
const maxStock = ref(null);
const measurementUnit = ref(null);

const purchasePrice = ref(null);
const date = ref(null);
const sellingPrice = ref(null);
const discount = ref(null);

const catName = ref(null);
const catDesc = ref(null);

const subCatName = ref(null);
const subCatCat = ref(null);
const subCatDesc = ref(null);
const manufacturerName= ref(null)
const manufacturerCountry=ref(null);
const manufacturerWeb=ref(null);
const manufactureAddress=ref(null);
const manufacturerEmail=ref(null);
const manufacturerContact=ref(null);
const manufactureDesc=ref(null);

const supplierName=ref(null);
const supplierCountry=ref(null);
const supplierWeb=ref(null);
const supplierAddress=ref(null);
const supplierEmail=ref(null);
const supplierContact=ref(null);
const supplierDesc=ref(null);

const unitName= ref(null);
const unitDesc= ref(null);

const showCategoryModal = ref(false);
const showSubCategoryModal = ref(false);
const showManufacturerModal = ref(false);
const showSupplierModal = ref(false);
const showMeasurementUnitModal = ref(false);
const showProductTypeModal = ref(false);
const showProductGaugeModal = ref(false);
const formError = ref({});

const catFormError=ref({});
const subCatFormError=ref({});
const manufactureFormError=ref({});
const supplierFormError=ref({});
const unitFormError=ref({});
const typeFormError=ref({});
const gaugeFormError=ref({});

const productCategoryData = ref(null);
const productSubCategoryData= ref(null);

const countriesData = ref(null);
const manufacturersData=ref(null);
const suppliersData=ref(null);
const measurementUnitsData=ref(null);
const manufacturedDate=ref(null);
const expiryDate=ref(null);

const prodType=ref(null);
const prodGauge=ref(null);

const typeName=ref(null);
const typeDesc=ref(null);
const gaugeName=ref(null);
const gaugeDesc=ref(null);

const productTypeList = ref(null);
const productGaugeList = ref(null);
const productDetails= ref(null);

const isEdit=ref(false);
const editId=ref(null);

const institutionDetails =ref({});
const taxableConfigs=(null);

const taxConfig=ref({id: 1, name:'Tax Exclusive', value: 'TAX_EXCLUSIVE'});


const taxOptions= ref([
   {id: 1, name:'VAT Exclusive', value: 'TAX_EXCLUSIVE'},
   {id: 2, name:'VAT Inclusive', value: 'TAX_INCLUSIVE'},
//    {id: 3, name:'VAT Exempted', value: 'TAX_EXEMPTED'},
])

const taxExempted=ref([
    {id: 1, name:'VAT Exempted', value: 'TAX_EXEMPTED'},
    {id: 2, name:'Standard Rated Supplies', value: 'TAX_NOT_EXEMPTED'},
    {id: 3, name:'Zero Sated Supplies', value: 'TAX_ZERO_RATED'},
])


// ********************************************************
const openCategoryModal = () => {
    showCategoryModal.value = true;
}

const closeCategoryModal = () => {
    showCategoryModal.value = false;
}

const openSubCategoryModal = () => {
    showSubCategoryModal.value = true;
}

const closeSubCategoryModal = () => {
    showSubCategoryModal.value = false;
}

const toggleManufacturerModal = (action) => {
    showManufacturerModal.value = action;
}

const toggleSupplierModal = (action) => {
    showSupplierModal.value = action;
}

const toggleMeasurementUnitModal = (action) => {
    showMeasurementUnitModal.value = action;
}

const onInputBlur=(value, key)=>{
    formError.value[key] = commonService.validateFormField(value);
}

const onCatInputBlur=(value, key)=>{
    catFormError.value[key]= commonService.validateFormField(value);
}

const onSubCatInputBlur=(value, key)=>{
    subCatFormError.value[key]= commonService.validateFormField(value);
}

const onManufacturerInputBlur=(value, key)=>{
    manufactureFormError.value[key]= commonService.validateFormField(value);
}

const onSupplierInputBlur=(value, key)=>{
    supplierFormError.value[key]= commonService.validateFormField(value);
}

const onUnitInputBlur=(value, key)=>{
    unitFormError.value[key]= commonService.validateFormField(value);
}

const toggleTypeModal=(action)=>{
    showProductTypeModal.value=action;
}

const toggleGaugeModal=(action)=>{
    showProductGaugeModal.value=action;
}

const onTypeInputBlur=(value, key)=>{
    typeFormError.value[key]= commonService.validateFormField(value)
}

const onGaugeInputBlur=(value, key)=>{
    gaugeFormError.value[key]= commonService.validateFormField(value);
}


const onSubmit= ()=>{
    formError.value.name=commonService.validateFormField(name.value);
    formError.value.category=commonService.validateFormField(category.value);
    formError.value.quantity=commonService.validateFormField(quantity.value);
    formError.value.measurementUnit=commonService.validateFormField(measurementUnit.value);
    formError.value.sellingPrice=commonService.validateFormField(sellingPrice.value);
    formError.value.prodType=commonService.validateFormField(prodType.value);

    formError.value.taxConfig=commonService.validateFormField(taxConfig.value);



    let invalid = commonService.validateRequiredFields(formError.value);
    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData={
        id:editId.value,
        name: name?.value,
        category_id: category?.value?.id,
        sub_category_id: subCategory?.value?.id,
        manufacturer_id:manufacturer?.value?.id,
        supplier_id:supplier?.value?.id,
        product_no: productId?.value,
        description: description?.value,
        quantity: quantity?.value,
        min_quantity:minStock?.value,
        max_quantity: maxStock?.value,
        measurement_unit_id: measurementUnit?.value?.id,
        purchase_price: purchasePrice?.value,
        date:date?.value,
        selling_price: sellingPrice?.value,
        discount: discount?.value,
        manufactured_date:manufacturedDate?.value,
        expiry_date:expiryDate?.value,
        tax_config: taxConfig?.value?.value,
        type_id: prodType?.value?.id,
        gauge_id: prodGauge?.value?.id,
    }

    commonService.genericRequest('create-product', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            name.value=null;
            category.value=null;
            subCategory.value=null;
            manufacturer.value=null;
            supplier.value=null;
            productId.value=null;
            description.value=null;
            quantity.value=null;
            minStock.value=null;
            maxStock.value=null;
            measurementUnit.value=null;
            purchasePrice.value=null;
            date.value=null;
            sellingPrice.value=null;
            discount.value=null;
            manufacturedDate.value=null;
            expiryDate.value=null;
            prodType.value=null;
            prodGauge.value=null;
            tax_config.value = {id: 1, name:'Tax Exclusive', value: 'TAX_EXCLUSIVE'}
        } else {
            commonService.showError(toast, response.message);
        }
    })


}


const onSubmitCat=()=>{
    catFormError.value.catName = commonService.validateFormField(catName.value);
    let invalid = commonService.validateRequiredFields(catFormError.value);
    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData={
        name: catName.value,
        description: catDesc.value,
        status: "Active",
    }
    commonService.genericRequest('create-product-category', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getProductCategories();
            closeCategoryModal();
            catName.value=null;
            catDesc.value=null;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getProductCategories=()=>{
    commonService.genericRequest('get-product-categories', 'get', true, {}).then((response) => {
        if (response.status) {
            productCategoryData.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onSubmitSubCat=()=>{
    subCatFormError.value.subCatName = commonService.validateFormField(subCatName.value);
    subCatFormError.value.subCatCat = commonService.validateFormField(subCatCat.value);

    let invalid = commonService.validateRequiredFields(subCatFormError.value);

    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }
    let postData ={
        category_id: subCatCat.value.id,
        name: subCatName.value,
        status:"Active",
        description:subCatDesc.value
    }
    commonService.genericRequest('create-product-sub-category', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getProductSubCategories();
            closeSubCategoryModal();
            subCatCat.value=null;
            subCatName.value=null;
            subCatDesc.value=null;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const getProductSubCategories=()=>{
    commonService.genericRequest('get-product-sub-categories', 'get', true, {}).then((response) => {
        if (response.status) {
            productSubCategoryData.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onSubmitManufacturer=()=>{
    manufactureFormError.value.manufacturerName = commonService.validateFormField(manufacturerName.value);
    let invalid = commonService.validateRequiredFields(manufactureFormError.value);

    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData = {
        name: manufacturerName.value,
        country_id: manufacturerCountry.value.id,
        website: manufacturerWeb.value,
        address: manufactureAddress.value,
        email: manufacturerEmail.value,
        phone_number: manufacturerContact.value,
        description:manufactureDesc.value,
        status:"Active",
    }

    commonService.genericRequest('create-manufacturer', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getManufacturers();
            toggleManufacturerModal(false);
            manufacturerName.value=null;
            manufacturerCountry.value=null;
            manufacturerWeb.value=null;
            manufactureAddress.value=null;
            manufacturerEmail.value=null;
            manufacturerContact.value=null;
            manufactureDesc.value=null;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getManufacturers=()=>{
    commonService.genericRequest('get-manufacturers', 'get', true, {}).then((response) => {
        if (response.status) {
            manufacturersData.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onSubmitSupplier=()=>{
    supplierFormError.value.supplierName = commonService.validateFormField(supplierName.value);

    let invalid = commonService.validateRequiredFields(supplierFormError.value);

    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData ={
        name: supplierName.value,
        country_id: supplierCountry.value.id,
        website:supplierWeb.value,
        address: supplierAddress.value,
        email:supplierEmail.value,
        phone_number:supplierContact.value,
        description: supplierDesc.value,
        status: "Active",
    }

    commonService.genericRequest('create-supplier', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getSuppliers();
            toggleSupplierModal(false);
            supplierName.value=null;
            supplierCountry.value=null;
            supplierWeb.value=null;
            supplierAddress.value=null;
            supplierEmail.value=null;
            supplierContact.value=null;
            supplierDesc.value=null;

        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getSuppliers=()=>{
    commonService.genericRequest('get-suppliers', 'get', true, {}).then((response) => {
        if (response.status) {
            suppliersData.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
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


const onSubmitType=()=>{
    typeFormError.value.typeName = commonService.validateFormField(typeName.value);
    let invalid = commonService.validateRequiredFields(typeFormError.value);
    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData = {
        name: typeName.value,
        description: typeDesc.value,
        status: "Active"
    }

    commonService.genericRequest('create-product-type', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getProductTypes();
            toggleTypeModal(false);
            typeName.value=null;
            typeDesc.value=null;
        } else {
            commonService.showError(toast, response.message);
        }
    })

}

const getProductTypes=()=>{
    commonService.genericRequest('get-product-types', 'get', true, {}).then((response) => {
        if (response.status) {
            productTypeList.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onSubmitGauge=()=>{
    gaugeFormError.value.gaugeName = commonService.validateFormField(gaugeName.value);
    let invalid = commonService.validateRequiredFields(gaugeFormError.value);
    if(invalid){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData = {
        name: gaugeName.value,
        description: gaugeDesc.value,
        status: "Active"
    }

    commonService.genericRequest('create-product-gauge', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            getProductGauge();
            toggleGaugeModal(false);
            gaugeName.value=null;
            gaugeDesc.value=null;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getProductGauge=()=>{
    commonService.genericRequest('get-product-gauge', 'get', true, {}).then((response) => {
        if (response.status) {
            productGaugeList.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const getCountries=()=>{
    commonService.genericRequest('get-countries', 'get', true, {}).then((response) => {
        if (response.status) {
            countriesData.value = response.data
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

const getProductDetails=(prodId)=>{
    let postData={
        id:prodId,
    }
    commonService.genericRequest('get-product-details', 'post', true, postData).then((response) => {
        if (response.status) {
            productDetails.value = response.data[0];
            name.value= productDetails.value.name;
            category.value=productCategoryData.value.find(cat=>cat.id ==productDetails.value.category_id)
            manufacturer.value=manufacturersData.value.find(cat=>cat.id ==productDetails.value.manufacturer_id)
            supplier.value=suppliersData.value.find(cat=>cat.id ==productDetails.value.supplier_id)
            productId.value =productDetails.value.product_no;
            description.value =productDetails.value.description;
            quantity.value= productDetails.value.quantity
            minStock.value= productDetails.value.min_quantity
            maxStock.value= productDetails.value.max_quantity
            measurementUnit.value = measurementUnitsData.value.find(cat=>cat.id ==productDetails.value.measurement_unit_id)
            purchasePrice.value =productDetails.value.purchase_price
            date.value =productDetails.value.stock_date
            sellingPrice.value =productDetails.value.selling_price
            prodType.value =productTypeList.value.find(cat=>cat.id ==productDetails.value.type_id)
            prodGauge.value =productGaugeList.value.find(cat=>cat.id ==productDetails.value.gauge_id)
            taxConfig.value = taxOptions.value.find(tax=>tax.value ==productDetails.value.tax_config)


        } else {
            commonService.showError(toast, response.message);
        }
    })
}

onMounted(() => {
    getProductCategories();
    getProductSubCategories();
    getCountries();
    getManufacturers()
    getSuppliers();
    getMeasurementUnit();
    getProductTypes();
    getProductGauge();
    getInstitutionDetails();
    if(route.params.id != null){
        editId.value=Number(route.params.id);
        isEdit.value = true;
        getProductDetails(route.params.id );
    }
});


</script>
<template>
    <div class="grid p-fluid">
        <div class="col-12 md:col-6">
            <div class="card">
                <h5>Basic Info</h5><br>
                <div class="grid">
                    <div class="field col-12 md:col-12">
                        <span class="p-float-label">
                            <InputText type="text" @blur="onInputBlur(name, 'name')" id="name" v-model.trim="name"  :class="{ 'p-invalid': formError?.name }" /> <!-- class="p-invalid"-->
                            <label for="name">Name</label>
                        </span>
                    </div>

                    <div class="field col-12 md:col-12">
                        <div class="p-inputgroup">
                            <span class="p-float-label">
                                <Dropdown id="type" @blur="onInputBlur(prodType, 'prodType')" filter :options="productTypeList" v-model="prodType"  :class="{ 'p-invalid': formError?.prodType }" optionLabel="name">
                                </Dropdown>
                                <label for="type">Type</label>
                            </span>
                            <Button @click="toggleTypeModal(true)" icon="pi pi-plus" />
                        </div>
                    </div>

                    <!-- <div class="field col-12 md:col-12">
                        <div class="p-inputgroup">
                            <span class="p-float-label">
                                <Dropdown id="Category" @blur="onInputBlur(category, 'category')" filter :options="productCategoryData" v-model="category"  :class="{ 'p-invalid': formError?.category }" optionLabel="name">
                                </Dropdown>
                                <label for="Category">Category</label>
                            </span>
                            <Button @click="openCategoryModal" icon="pi pi-plus" />
                        </div>
                    </div> -->

                    <div class="field col-12 md:col-12">
                        <div class="p-inputgroup">
                            <span class="p-float-label">
                                <Dropdown id="Category" @blur="onInputBlur(category, 'category')" filter :options="productCategoryData" v-model="category"  :class="{ 'p-invalid': formError?.category }" optionLabel="name">
                                </Dropdown>
                                <label for="Category">Category</label>
                            </span>
                            <Button @click="openCategoryModal" icon="pi pi-plus" />
                        </div>
                    </div>

                    <div class="field col-12 md:col-12">
                        <div class="p-inputgroup">
                            <span class="p-float-label">
                                <Dropdown id="subCategory" :options="productSubCategoryData" filter v-model="subCategory" optionLabel="name">
                                </Dropdown>
                                <label for="subCategory">Sub Category</label>
                            </span>
                            <Button @click="openSubCategoryModal" icon="pi pi-plus" />
                        </div>
                    </div>

                    <div class="field col-12 md:col-12">
                        <div class="p-inputgroup">
                            <span class="p-float-label">
                                <Dropdown id="gauge" :options="productGaugeList" filter v-model="prodGauge" optionLabel="name">
                                </Dropdown>
                                <label for="gauge">Gauge</label>
                            </span>
                            <Button @click="toggleGaugeModal(true)" icon="pi pi-plus" />
                        </div>
                    </div>

                    <div class="field col-12 md:col-12">
                        <div class="p-inputgroup">
                            <span class="p-float-label">
                                <Dropdown id="manufacturer" :options="manufacturersData" filter v-model="manufacturer" optionLabel="name">
                                </Dropdown>
                                <label for="manufacturer">Manufacturer</label>
                            </span>
                            <Button @click="toggleManufacturerModal(true)" icon="pi pi-plus" />
                        </div>
                    </div>

                    <div class="field col-12 md:col-12">
                        <div class="p-inputgroup">
                            <span class="p-float-label">
                                <Dropdown id="supplier"  :options="suppliersData" filter v-model="supplier" optionLabel="name">
                                </Dropdown>
                                <label for="supplier">Supplier</label>
                            </span>
                            <Button @click="toggleSupplierModal(true)" icon="pi pi-plus" />
                        </div>
                    </div>

                    <div class="field col-12 md:col-12">
                        <span class="p-float-label">
                            <InputText type="text" id="productId" v-model="productId" /> <!-- class="p-invalid"-->
                            <label for="productId">Product ID</label>
                        </span>
                    </div>

                    <div class="field col-12 md:col-12">
                        <span class="p-float-label">
                            <Textarea inputId="description" rows="11" v-model="description"></Textarea>
                            <label for="description">Description</label>
                        </span>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12 md:col-6">
            <div class="card">
                <h5>Stock Info</h5><br>
                <div class="grid">
                    <div class="field col-12 md:col-6">
                        <span class="p-float-label">
                            <InputText type="text" id="quantity" @blur="onInputBlur(quantity, 'quantity')" v-model.trim="quantity" :class="{ 'p-invalid': formError?.quantity }" :disabled="isEdit"/>
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
                </div>
            </div>

            <div class="card">
                <h5>Purchase Info</h5><br>
                <div class="grid">

                    <div class="field col-12 md:col-6">
                        <span class="p-float-label">
                            <InputText type="text" id="purchasePrice" v-model="purchasePrice" :disabled="isEdit" />
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

                    <div class="field col-12 md:col-6" v-if="institutionDetails?.is_tax_enabled">
                        <span class="p-float-label">
                            <Dropdown id="taxableConfig" :options="taxExempted" v-model="taxableConfigs" @blur="onInputBlur(taxableConfigs, 'taxableConfigs')" :class="{ 'p-invalid': formError?.taxableConfigs }" optionLabel="name"></Dropdown>
                            <label for="taxableConfig">VAT Config</label>
                        </span>
                    </div>


                </div>
            </div>

            <div class="card">
                <h5>Selling Price</h5><br>
                <div class="grid">
                    <div class="field col-12 md:col-6">
                        <span class="p-float-label">
                            <InputText type="text" @blur="onInputBlur(sellingPrice, 'sellingPrice')" id="sellingPrice" v-model="sellingPrice" :class="{ 'p-invalid': formError?.sellingPrice }"/>
                            <label for="sellingPrice">Unit Selling Price </label>
                        </span>
                    </div>
                    <div class="field col-12 md:col-6" v-if="institutionDetails?.is_tax_enabled">
                        <span class="p-float-label">
                            <Dropdown id="instCity" :options="taxOptions" v-model="taxConfig" @blur="onInputBlur(taxConfig, 'taxConfig')" :class="{ 'p-invalid': formError?.taxConfig }" optionLabel="name"></Dropdown>
                            <label for="instCity">Tax Config</label>
                        </span>
                    </div>
                    <!-- <div class="field col-12 md:col-6">
                        <span class="p-float-label">
                            <InputText type="text" id="discount" :value="sellingPrice*quantity" disabled/>
                            <label for="discount">Expected Total</label>
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
        <Dialog header="Create Category" v-model:visible="showCategoryModal" :style="{ width: '30vw' }" :modal="true"
            class="p-fluid">
            <p style="padding-top: 20px;">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <InputText type="text" id="catName" @blur="onCatInputBlur(catName, 'catName')" v-model="catName" :class="{ 'p-invalid': catFormError?.catName }"/> <!-- class="p-invalid"-->
                        <label for="catName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="catDesc" rows="7" v-model="catDesc"></Textarea>
                        <label for="catDesc">Description</label>
                    </span>
                </div>
            </div>
            </p>
            <template #footer>
                <Button label="Cancel" @click="closeCategoryModal" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="onSubmitCat" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>

        <Dialog header="Create Sub Category" v-model:visible="showSubCategoryModal" :style="{ width: '30vw' }" :modal="true"
            class="p-fluid">
            <p style="padding-top: 20px;">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <InputText type="text" id="subCatName" @blur="onSubCatInputBlur(subCatName, 'subCatName')" v-model="subCatName" :class="{ 'p-invalid': subCatFormError?.subCatName }" /> <!-- class="p-invalid"-->
                        <label for="subCatName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-12">
                    <div class="p-inputgroup">
                        <span class="p-float-label">
                            <Dropdown id="subCatCat" :options="productCategoryData" @blur="onSubCatInputBlur(subCatCat, 'subCatCat')" v-model="subCatCat" optionLabel="name" :class="{ 'p-invalid': subCatFormError?.subCatCat }"></Dropdown>
                            <label for="subCatCat">Category</label>
                        </span>
                        <Button @click="openCategoryModal" icon="pi pi-plus" />
                    </div>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="subCatDesc" rows="7" v-model="subCatDesc"></Textarea>
                        <label for="subCatDesc">Description</label>
                    </span>
                </div>
            </div>
            </p>
            <template #footer>
                <Button label="Cancel" @click="closeSubCategoryModal" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="onSubmitSubCat" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>

        <Dialog header="Create Manufacturer" v-model:visible="showManufacturerModal" :style="{ width: '75vw' }"
            :modal="true" class="p-fluid">
            <p style="padding-top: 20px;">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="manufacturerName" @blur="onManufacturerInputBlur(manufacturerName, 'manufacturerName')" v-model="manufacturerName" :class="{ 'p-invalid': manufactureFormError?.manufacturerName }" /> <!-- class="p-invalid"-->
                        <label for="manufacturerName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="manufacturerCountry" :options="countriesData" filter  v-model="manufacturerCountry" optionLabel="name"></Dropdown>
                        <label for="manufacturerCountry">Country</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="manufacturerWeb" v-model="manufacturerWeb" /> <!-- class="p-invalid"-->
                        <label for="manufacturerWeb">Website</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="manufactureAddress" v-model="manufactureAddress" /> <!-- class="p-invalid"-->
                        <label for="manufactureAddress">Address</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="manufacturerEmail" v-model="manufacturerEmail" /> <!-- class="p-invalid"-->
                        <label for="manufacturerEmail">Email</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="manufacturerContact" v-model="manufacturerContact" /> <!-- class="p-invalid"-->
                        <label for="manufacturerContact">Phone Number</label>
                    </span>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="manufactureDesc" rows="7" v-model="manufactureDesc"></Textarea>
                        <label for="manufactureDesc">Description</label>
                    </span>
                </div>
            </div>
            </p>
            <template #footer>
                <Button label="Cancel" @click="toggleManufacturerModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="onSubmitManufacturer" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>


        <Dialog header="Create Supplier" v-model:visible="showSupplierModal" :style="{ width: '75vw' }" :modal="true"
            class="p-fluid">
            <p style="padding-top: 20px;">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="supplierName" @blur="onSupplierInputBlur(supplierName, 'supplierName')" v-model="supplierName"  :class="{ 'p-invalid': supplierFormError?.supplierName }"/> <!-- class="p-invalid"-->
                        <label for="supplierName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="supplierCountry" :options="countriesData" filter v-model="supplierCountry" optionLabel="name"></Dropdown>
                        <label for="supplierCountry">Country</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="supplierWeb" v-model="supplierWeb" /> <!-- class="p-invalid"-->
                        <label for="supplierWeb">Website</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="supplierAddress" v-model="supplierAddress" /> <!-- class="p-invalid"-->
                        <label for="supplierAddress">Address</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="supplierEmail" v-model="supplierEmail" /> <!-- class="p-invalid"-->
                        <label for="supplierEmail">Email</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="supplierContact" v-model="supplierContact" /> <!-- class="p-invalid"-->
                        <label for="supplierContact">Phone Number</label>
                    </span>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="supplierDesc" rows="7" v-model="supplierDesc"></Textarea>
                        <label for="supplierDesc">Description</label>
                    </span>
                </div>
            </div>
            </p>
            <template #footer>
                <Button label="Cancel" @click="toggleSupplierModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="onSubmitSupplier" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
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


        <Dialog header="Create Product Type" v-model:visible="showProductTypeModal" :style="{ width: '30vw' }"
        :modal="true" class="p-fluid">
            <p style="padding-top: 20px;">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <InputText type="text" id="unitName" @blur="onTypeInputBlur(typeName, 'typeName')" v-model="typeName" :class="{ 'p-invalid': typeFormError?.typeName }" /> <!-- class="p-invalid"-->
                        <label for="unitName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="unitDesc" rows="7" v-model="typeDesc"></Textarea>
                        <label for="unitDesc">Description</label>
                    </span>
                </div>
        </div>
        </p>
        <template #footer>
            <Button label="Cancel" @click="toggleTypeModal(false)" icon="pi pi-times"
                class="p-button-outlined p-button-danger mr-2 mb-2" />
            <Button @click="onSubmitType" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
        </template>
    </Dialog>

    <Dialog header="Create Product Gauge" v-model:visible="showProductGaugeModal" :style="{ width: '30vw' }"
    :modal="true" class="p-fluid">
        <p style="padding-top: 20px;">
        <div class="grid p-fluid">
            <div class="field col-12 md:col-12">
                <span class="p-float-label">
                    <InputText type="text" id="unitName" @blur="onGaugeInputBlur(gaugeName, 'gaugeName')" v-model="gaugeName" :class="{ 'p-invalid': gaugeFormError?.gaugeName }" /> <!-- class="p-invalid"-->
                    <label for="unitName">Name</label>
                </span>
            </div>
            <div class="field col-12 md:col-12">
                <span class="p-float-label">
                    <Textarea inputId="unitDesc" rows="7" v-model="gaugeDesc"></Textarea>
                    <label for="unitDesc">Description</label>
                </span>
            </div>
    </div>
    </p>
    <template #footer>
        <Button label="Cancel" @click="toggleGaugeModal(false)" icon="pi pi-times"
            class="p-button-outlined p-button-danger mr-2 mb-2" />
        <Button @click="onSubmitGauge" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
    </template>
</Dialog>
    <!-- /End of the modals -->

</div></template>
