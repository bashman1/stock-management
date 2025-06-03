<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CommonService from '@/service/CommonService';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const commonService = new CommonService();
const router = useRouter();
const route = useRoute()

const durations = ref([]);
const priceForm = ref([]);
const setPrice = ref({});
const packageForm = ref({});
const configLimit = ref([]);
const limitationList = ref([]);
const selectedConfig = ref([]);
const dummyConfig = ref([  { config: null, value: '' }]);
const licenseBenefits = ref([]);


const addPricing = () => {
    if (setPrice.value.duration && setPrice.value.amount) {
        priceForm.value.push({
            duration: setPrice.value.duration,
            amount: setPrice.value.amount
        });
        setPrice.value = {};
    } else {
        commonService.showError(toast, 'Please fill all fields');
    }
}

const removePricing = (data) => {
    const index = priceForm.value.indexOf(data);
    if (index > -1) {
        priceForm.value.splice(index, 1);
    }
}



// const setSelectedConfig = (selectedValue, index) => {
//     console.log("event", JSON.stringify(selectedValue));

//     const existingIndex = selectedConfig.value.findIndex(item => item.index === index);

//     if (selectedValue) {
//         if (existingIndex !== -1) {
//             // Update existing
//             selectedConfig.value[existingIndex].config = selectedValue;
//         } else {
//             // Push new
//             selectedConfig.value.push({ config: selectedValue, index: index });
//         }
//     } else {
//         // If no value, remove the item at that index if it exists
//         if (existingIndex !== -1) {
//             selectedConfig.value.splice(existingIndex, 1);
//         }
//     }
// };







const updateSelectedConfig = (data, index) => {
    const existingIndex = selectedConfig.value.findIndex(item => item.index === index);

    if (!data || Object.keys(data).length === 0) {
        // Remove item if data is empty/null/undefined
        if (existingIndex !== -1) {
            selectedConfig.value.splice(existingIndex, 1);
        }
        return;
    }

    if (existingIndex !== -1) {
        // Update existing entry
        selectedConfig.value[existingIndex] = {
            ...selectedConfig.value[existingIndex],
            ...data
        };
    } else {
        // Push new entry
        selectedConfig.value.push({
            index,
            ...data
        });
    }

    console.log(JSON.stringify(selectedConfig.value));
};


const handleLimitationChange = (event, index) => {
    updateSelectedConfig({ config: event.value }, index);
}

const setSelectedAmount = (amount, index) => {
    updateSelectedConfig({ amount: Number(amount) }, index);
};



const getDurations = () => {
    commonService.genericRequest("get-durations", 'get', false, {}).then((response) => {
        if (response.status) {
            durations.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getLicensePackageConfig = () => {
    commonService.genericRequest("get-license-package-configs", 'get', true, {}).then((response) => {
        if (response.status) {
            configLimit.value = response.data
            dummyConfig.value = response.data.map(config => ({ config: null, value: '' }));
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getLicensePackageLimitation = () => {
    commonService.genericRequest("get-license-package-limitation", 'get', true, {}).then((response) => {
        if (response.status) {
            limitationList.value = response.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const onSubmit = () => {
    if (packageForm.value.name && packageForm.value.description && priceForm.value.length > 0) {
        const data = {
            name: packageForm.value.name,
            description: packageForm.value.description,
            prices: priceForm.value,
            implementation: packageForm.value.implementation,
            benefits: packageForm.value.benefits,
        };
        commonService.genericRequest("create-license-package", 'post', true, data).then((response) => {
            if (response.status) {
                commonService.showSuccess(toast, response.message);
                packageForm.value = {};
                priceForm.value = [];

                // toast.add({ severity: 'success', summary: 'Success', detail: response.message, life: 3000 });
                // router.push("/view-packages");
            } else {
                commonService.showError(toast, response.message);
            }
        });
    } else {
        commonService.showError(toast, 'Please fill all fields');
    }
}


const getLicenseBenefits = () => {
    commonService.genericRequest("get-license-benefits", 'get', true, {}).then((response) => {
        if (response.status) {
            licenseBenefits.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    });
}

onMounted(() => {
    getDurations();
    getLicensePackageConfig();
    getLicensePackageLimitation();
    getLicenseBenefits();
});

</script>
<template>
    <div class="grid">
        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5>Package Details</h5>
                <div class="field mt-4">
                    <span class="p-float-label">
                        <InputText id="name1" type="text" v-model="packageForm.name" />
                        <label for="name1">Name</label>
                    </span>
                </div>

                <div class="field  mt-4">
                    <span class="p-float-label">
                        <InputNumber id="name1" type="text" v-model="packageForm.implementation" />
                        <label for="name1">Implementation Fee</label>
                    </span>
                </div>

                <div class="field mt-4">
                    <span class="p-float-label">
                        <Textarea id="address" rows="4" v-model="packageForm.description" />
                        <label for="address">Description</label>
                    </span>
                </div>

                <div class="formgrid grid" v-for="(config, index) in configLimit">
                    <div class="field col-12">
                        <h6>{{config.description}}</h6>
                    </div>
                    <div class="field col">
                        <span class="p-float-label">
                            <Dropdown id="state"  :options="limitationList" v-model="dummyConfig[index].config"  optionLabel="name"
                                placeholder="Select duration" @change="handleLimitationChange($event, index)"></Dropdown>
                            <label for="state">{{config.name}}</label>
                        </span>
                    </div>
                    <div class="field col" v-if="selectedConfig[index] && selectedConfig[index]?.config?.name === 'Limited'">
                        <span class="p-float-label">
                            <InputNumber id="name2" type="text"  v-model="dummyConfig[index].value"  @change="setSelectedAmount($event.target.value, index)" />
                            <label for="name2">{{config.description}}</label>
                        </span>
                    </div>
                </div>
            </div>

            <div class="card p-fluid">
                <h5>Package Benefits</h5>
                <div class="formgrid grid">
                    <div class="field col">
                        <span class="p-float-label">
                            <MultiSelect id="state"  :options="licenseBenefits" v-model="packageForm.benefits"  optionLabel="name"
                                placeholder="Select duration" display="chip" ></MultiSelect>
                            <label for="state">Package Benefits</label>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5>Package Prices</h5>
                <div class="field ">
                    <DataTable :value="priceForm" :paginator="false" :scrollable="true">
                        <Column field="duration.name" header="Duration"></Column>
                        <Column field="amount" header="Price">
                            <template #body="{ data }">
                                {{ commonService.commaSeparator(data.amount) }}
                            </template>
                        </Column>
                        <Column>
                            <template #body="{ data }">
                                <Button icon="pi pi-trash" class="p-button-danger mr-2" @click="removePricing(data)"
                                    v-tooltip="'Delete pricing'" />
                            </template>
                        </Column>
                    </DataTable>
                </div>
                <div class="field mt-4">
                    <span class="p-float-label">
                        <Dropdown id="state" v-model="setPrice.duration" :options="durations" optionLabel="name"
                            placeholder="Select duration"></Dropdown>
                        <label for="state">Duration</label>
                    </span>
                </div>
                <div class="field mt-4">
                    <div class="p-inputgroup">
                        <span class="p-float-label">
                            <InputText id="name1" type="text" v-model="setPrice.amount" />
                            <label for="name1">Price</label>
                        </span>
                        <Button @click="addPricing" label="Add" />
                    </div>
                </div>

                <div class="field col-12 md:col-8"></div>
                <div class="field col-12 md:col-4">
                    <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
                </div>
            </div>

        </div>

    </div>
</template>
