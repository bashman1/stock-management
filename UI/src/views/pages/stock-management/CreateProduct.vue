<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();

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

const citiesData = ref(null);
const userData = ref(null);
const institutionsData = ref(null);
const institution = ref(null);
const showCategoryModal = ref(false);
const showSubCategoryModal = ref(false);
const showManufacturerModal = ref(false);
const showSupplierModal = ref(false);
const showMeasurementUnitModal = ref(false);
const formError = ref({});



const genderOptions = ref([
    { label: 'Male', value: 'Male' },
    { label: 'Female', value: 'Female' },
]);

// const onSubmit = () => {
//     let postData = {
//         first_name: fName.value,
//         last_name: lName.value,
//         other_name: oName.value,
//         email: email.value,
//         phone_number: phoneNumber.value,
//         date_of_birth: dob.value,
//         gender: gender.value.value,
//         address: address.value,
//         city_id: city.value.id,
//         street: street.value,
//         p_o_box: pOBox.value,
//         description: description.value,
//         status: 'Active'
//     }
//     commonService.genericRequest('create-member', 'post', true, postData).then((response) => {
//         if (response.status) {
//             commonService.showSuccess(toast, response.message);
//             commonService.redirect(router, "/view-members");
//         } else {
//             commonService.showError(toast, response.message);
//         }
//     })
// }

const getCities = () => {
    commonService.genericRequest('get-city-county-id/' + 1, 'get', true, {}).then((response) => {
        if (response.status) {
            citiesData.value = response.data
        } else {

        }
    })
}


const getInstitution = () => {
    if (userData.value?.user_type == 'Admin') {
        commonService.genericRequest('get-institutions', 'get', true, {}).then((response) => {
            if (response.status) {
                institutionsData.value = response.data
            } else {

            }
        })
    }
}


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


const onSubmit= async()=>{
    formError.value.name=commonService.validateFormField(name.value);
    formError.value.category=commonService.validateFormField(category.value);
    formError.value.quantity=commonService.validateFormField(quantity.value);
    formError.value.measurementUnit=commonService.validateFormField(measurementUnit.value);
    formError.value.sellingPrice=commonService.validateFormField(sellingPrice.value);

    let invalid = await commonService.validateRequiredFields(formError);

    if(invalid){
        return
    }

    alert("Passed")
    submitNow();

}

const submitNow=()=>{

}


onMounted(() => {
    userData.value = commonService.getStorage().userData;
    getCities();
    getInstitution();
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
                                <Dropdown id="Category" @blur="onInputBlur(category, 'category')" :options="genderOptions" v-model="category"  :class="{ 'p-invalid': formError?.category }" optionLabel="label">
                                </Dropdown>
                                <label for="Category">Category</label>
                            </span>
                            <Button @click="openCategoryModal" icon="pi pi-plus" />
                        </div>
                    </div>
                    <div class="field col-12 md:col-12">
                        <div class="p-inputgroup">
                            <span class="p-float-label">
                                <Dropdown id="subCategory" :options="genderOptions" v-model="subCategory" optionLabel="label">
                                </Dropdown>
                                <label for="subCategory">Sub Category</label>
                            </span>
                            <Button @click="openSubCategoryModal" icon="pi pi-plus" />
                        </div>
                    </div>
                    <div class="field col-12 md:col-12">
                        <div class="p-inputgroup">
                            <span class="p-float-label">
                                <Dropdown id="manufacturer"  :options="genderOptions" v-model="manufacturer" optionLabel="label">
                                </Dropdown>
                                <label for="manufacturer">Manufacturer</label>
                            </span>
                            <Button @click="toggleManufacturerModal(true)" icon="pi pi-plus" />
                        </div>
                    </div>

                    <div class="field col-12 md:col-12">
                        <div class="p-inputgroup">
                            <span class="p-float-label">
                                <Dropdown id="supplier"  :options="genderOptions" v-model="supplier" optionLabel="label">
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
                            <Textarea inputId="description" rows="7" v-model="description"></Textarea>
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
                                <Dropdown id="measurementUnit" :options="genderOptions" @blur="onInputBlur(measurementUnit, 'measurementUnit')" v-model="measurementUnit" :class="{ 'p-invalid': formError?.measurementUnit }" optionLabel="label">
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
                            <InputText type="text" id="purchasePrice" v-model="purchasePrice" />
                            <label for="purchasePrice">Purchase Price </label>
                        </span>
                    </div>

                    <div class="field col-12 md:col-6">
                        <span class="p-float-label">
                            <Calendar id="date" v-model="date"></Calendar>
                            <label for="date">Date</label>
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
                            <label for="sellingPrice">Selling Price </label>
                        </span>
                    </div>
                    <div class="field col-12 md:col-6">
                        <span class="p-float-label">
                            <InputText type="text" id="discount" v-model="discount" />
                            <label for="discount">Discount Rate</label>
                        </span>
                    </div>

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
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="textarea" rows="7" v-model="address"></Textarea>
                        <label for="textarea">Description</label>
                    </span>
                </div>
            </div>
            </p>
            <template #footer>
                <Button label="Cancel" @click="closeCategoryModal" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>

        <Dialog header="Create Category" v-model:visible="showSubCategoryModal" :style="{ width: '30vw' }" :modal="true"
            class="p-fluid">
            <p style="padding-top: 20px;">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-12">
                    <div class="p-inputgroup">
                        <span class="p-float-label">
                            <Dropdown id="gender" :options="genderOptions" v-model="gender" optionLabel="label"></Dropdown>
                            <label for="gender">Category</label>
                        </span>
                        <Button @click="openCategoryModal" icon="pi pi-plus" />
                    </div>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="textarea" rows="7" v-model="address"></Textarea>
                        <label for="textarea">Description</label>
                    </span>
                </div>
            </div>
            </p>
            <template #footer>
                <Button label="Cancel" @click="closeSubCategoryModal" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>

        <Dialog header="Create Manufacturer" v-model:visible="showManufacturerModal" :style="{ width: '75vw' }"
            :modal="true" class="p-fluid">
            <p style="padding-top: 20px;">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="gender" :options="genderOptions" v-model="gender" optionLabel="label"></Dropdown>
                        <label for="gender">Country</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Website</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Address</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Email</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Phone Number</label>
                    </span>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="textarea" rows="7" v-model="address"></Textarea>
                        <label for="textarea">Description</label>
                    </span>
                </div>
            </div>
            </p>
            <template #footer>
                <Button label="Cancel" @click="toggleManufacturerModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>


        <Dialog header="Create Supplier" v-model:visible="showSupplierModal" :style="{ width: '75vw' }" :modal="true"
            class="p-fluid">
            <p style="padding-top: 20px;">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="gender" :options="genderOptions" v-model="gender" optionLabel="label"></Dropdown>
                        <label for="gender">Country</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Website</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Address</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Email</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Phone Number</label>
                    </span>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="textarea" rows="7" v-model="address"></Textarea>
                        <label for="textarea">Description</label>
                    </span>
                </div>
            </div>
            </p>
            <template #footer>
                <Button label="Cancel" @click="toggleSupplierModal(false)" icon="pi pi-times"
                    class="p-button-outlined p-button-danger mr-2 mb-2" />
                <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </template>
        </Dialog>

        <Dialog header="Create Measurement Unit" v-model:visible="showMeasurementUnitModal" :style="{ width: '30vw' }"
            :modal="true" class="p-fluid">
            <p style="padding-top: 20px;">
            <div class="grid p-fluid">
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <InputText type="text" id="firstName" v-model="name" /> <!-- class="p-invalid"-->
                        <label for="firstName">Name</label>
                    </span>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="textarea" rows="7" v-model="address"></Textarea>
                        <label for="textarea">Description</label>
                    </span>
                </div>
        </div>
        </p>
        <template #footer>
            <Button label="Cancel" @click="toggleMeasurementUnitModal(false)" icon="pi pi-times"
                class="p-button-outlined p-button-danger mr-2 mb-2" />
            <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
        </template>
    </Dialog>

    <!-- /End of the modals -->

</div></template>
