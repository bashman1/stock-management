<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();
const formData = ref({});
const userData=ref(null);
const formError = ref({});
const durations = ref([]);
const citiesData = ref(null);
const notificationTypes = ref([]);
const users = ref([]);
const members = ref([]);
const customerData = ref([]);
const suppliersData = ref([]);
const manufacturersData = ref([]);
const userTypes = ref([
    { label: "Users", value: "USERS" },
    { label: "Members", value: "MEMBERS" },
    { label: "Customers", value: "CUSTOMERS" },
    { label: "Suppliers", value: "SUPPLIERS" },
    { label: "Manufacturers", value: "MANUFACTURERS" },
    { label: "Others", value: "OTHERS" },
]);

const onSubmit = () => {
    let postData = formData.value;
    postData.notification_type_id = postData?.notificationType?.id
    postData.category = postData?.userType?.value
    postData.user_id = postData?.user?.id
    postData.member_id = postData?.member?.id
    postData.customer_id = postData?.customer?.id
    postData.supplier_id = postData?.supplier?.id
    postData.manufacturer_id = postData?.manufacturer?.id
    postData.daily = postData?.daily?postData?.daily[0]:false,
    postData.weekly = postData?.weekly?postData?.weekly[0]:false,
    postData.monthly = postData?.monthly?postData?.monthly[0]:false,
    postData.quarterly = postData?.quarterly?postData?.quarterly[0]:false,
    postData.yearly = postData?.yearly?postData?.yearly[0]:false,

    commonService.genericRequest('create-notification-config', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            // commonService.redirect(router, "/view-projects");
            formData.value = {};
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onInputBlur=(value, key)=>{
    formError.value[key] = commonService.validateFormField(value);
}

const getDurations = () => {
    commonService.genericRequest("get-durations", 'get', false, {}).then((response) => {
        if (response.status) {
            durations.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

// const getCities = () => {
//     commonService.genericRequest('get-city-county-id/' + 1, 'get', true, {}).then((response) => {
//         if (response.status) {
//             citiesData.value = response.data
//         } else {

//         }
//     })
// }


const getNotificationTypes=()=>{
    const postData = {

    };
    commonService.genericRequest('get-notification-types', 'post', true, postData).then((response) => {
        if (response.status) {
            notificationTypes.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getAllUsers = () => {
    commonService.genericRequest('get-users', 'get', true, {}).then((response) => {
        if (response.status) {
            users.value = response.data.map(user => {
                return { ...user, name: user.first_name + ' ' + user.last_name+ ' ' + user.other_name }
            });
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getAllMembers = () => {
    commonService.genericRequest('get-members', 'get', true, {}).then((response) => {
        if (response.status) {
            members.value = response.data.map(rate => { return { ...rate, name: rate.first_name + ' ' + rate.last_name + ' ' + rate.other_name } });
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getCustomers = () => {
    commonService.genericRequest('get-customers', 'post', true, {}).then((response) => {
        if (response.status) {
            customerData.value = response.data
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

const getManufacturers=()=>{
    commonService.genericRequest('get-manufacturers', 'get', true, {}).then((response) => {
        if (response.status) {
            manufacturersData.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const onReceiverChange =(event)=>{
    const type = event.value;
    if (type.value == 'USERS'){
        getAllUsers();
    } else if (type.value == 'MEMBERS'){
        getAllMembers();
    } else if (type.value == 'CUSTOMERS'){
        getCustomers();
    } else if (type.value == 'SUPPLIERS'){
        getSuppliers();
    } else if (type.value == 'MANUFACTURERS'){
        getManufacturers();
    } else {
        // users.value = [];
    }
}

const onUserSectionChange =(e, t)=>{
    if(t== 'USERS' || t == 'MEMBERS'){
        formData.value.name = e.value?.first_name+' '+e.value?.last_name+' '+(e.value?.other_name ? e.value?.other_name:'')
        formData.value.email = e.value?.email;
        formData.value.phone = e.value?.phone_number
    }else if(t == 'CUSTOMERS' || t == 'SUPPLIERS' || t == 'MANUFACTURERS'){
        formData.value.name = e.value?.name;
        formData.value.email = e.value?.email;
        formData.value.phone = e.value?.phone_number
    }else{}
}



onMounted(() => {
    userData.value=commonService.getStorage().userData;
    getNotificationTypes();
    // getAllUsers();

    // getDurations();
    // getCities();
});


</script>
<template>
    <div>
        <div class="card">
            <h5>Notification Config</h5>
            <div class="grid p-fluid mt-3">
                <Toast />
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="notificationType" :options="notificationTypes" v-model="formData.notificationType" filter optionLabel="name"></Dropdown>
                        <label for="notificationType">Notification Type</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <Dropdown id="receivers" :options="userTypes" @change="onReceiverChange" v-model="formData.userType" filter optionLabel="label"></Dropdown>
                        <label for="receivers">Receiver</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4" v-if="formData?.userType?.value == 'USERS'">
                    <span class="p-float-label">
                        <Dropdown id="receivers" :options="users" @change="onUserSectionChange($event, 'USERS')" v-model="formData.user" filter optionLabel="name"></Dropdown>
                        <label for="receivers">Users</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4" v-if="formData?.userType?.value == 'MEMBERS'">
                    <span class="p-float-label">
                        <Dropdown id="receivers" :options="members" @change="onUserSectionChange($event, 'MEMBERS')" v-model="formData.member" filter optionLabel="name"></Dropdown>
                        <label for="receivers">Member</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4" v-if="formData?.userType?.value == 'CUSTOMERS'">
                    <span class="p-float-label">
                        <Dropdown id="receivers" :options="customerData" @change="onUserSectionChange($event, 'CUSTOMERS')" v-model="formData.customer" filter optionLabel="name"></Dropdown>
                        <label for="receivers">Customer</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4" v-if="formData?.userType?.value == 'SUPPLIERS'">
                    <span class="p-float-label">
                        <Dropdown id="receivers" :options="suppliersData" @change="onUserSectionChange($event, 'SUPPLIERS')" v-model="formData.supplier" filter optionLabel="name"></Dropdown>
                        <label for="receivers">Supplier</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4" v-if="formData?.userType?.value == 'MANUFACTURERS'">
                    <span class="p-float-label">
                        <Dropdown id="receivers" :options="manufacturersData" @change="onUserSectionChange($event, 'MANUFACTURERS')" v-model="formData.manufacturer" filter optionLabel="name"></Dropdown>
                        <label for="receivers">Manufacturer</label>
                    </span>
                </div>
                <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="name" v-model="formData.name" @blur="onInputBlur(formData.name, 'name')" :class="{ 'p-invalid': formError?.name }" /> <!-- class="p-invalid"-->
                        <label for="name">Name</label>
                    </span>
                </div>
                 <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="email" v-model="formData.email" @blur="onInputBlur(formData.email, 'email')" :class="{ 'p-invalid': formError?.email }" /> <!-- class="p-invalid"-->
                        <label for="email">Email</label>
                    </span>
                </div>
                 <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="phone" v-model="formData.phone" @blur="onInputBlur(formData.phone, 'phone')" :class="{ 'p-invalid': formError?.phone }" /> <!-- class="p-invalid"-->
                        <label for="phone">Phone Number</label>
                    </span>
                </div>
                 <div class="col-12 md:col-4">
                        <div class="field-checkbox mb-0">
                            <Checkbox id="checkOption1" name="daily" :value="true" v-model="formData.daily" />
                            <label class="text-gray-900" for="checkOption1">Daily</label>
                        </div>
                </div>
                <div class="col-12 md:col-4">
                        <div class="field-checkbox mb-0">
                            <Checkbox id="checkOption1" name="weekly" :value="true" v-model="formData.weekly" />
                            <label class="text-gray-900" for="checkOption1">Weekly</label>
                        </div>
                </div>
                <div class="col-12 md:col-4">
                        <div class="field-checkbox mb-0">
                            <Checkbox id="checkOption1" name="monthly" :value="true" v-model="formData.monthly" />
                            <label class="text-gray-900" for="checkOption1">Monthly</label>
                        </div>
                </div>
                <div class="col-12 md:col-4">
                        <div class="field-checkbox mb-0">
                            <Checkbox id="checkOption1" name="quarterly" :value="true" v-model="formData.quarterly" />
                            <label class="text-gray-900" for="checkOption1">Quarterly</label>
                        </div>
                </div>
                <div class="col-12 md:col-4">
                        <div class="field-checkbox mb-0">
                            <Checkbox id="checkOption1" name="yearly" :value="true" v-model="formData.yearly" />
                            <label class="text-gray-900" for="checkOption1">Yearly</label>
                        </div>
                </div>
                <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea inputId="instDescription" rows="3" cols="30" v-model="formData.description"></Textarea>
                        <label for="instDescription">More Info</label>
                    </span>
                </div>
                <div class="field col-12 md:col-8"></div>
                <div class="field col-12 md:col-4">
                    <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                </div>

            </div>
        </div>
    </div>
</template>
