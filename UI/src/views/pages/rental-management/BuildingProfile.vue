<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';
const route = useRoute()

const toast = useToast();

const commonService = new CommonService();
const router = useRouter();
const userDetails = ref(null);
const assignedBranches = ref(null);
const selectedBrach = ref(null);
const oldPassword = ref(null);
const newPassword = ref(null);
const confirmPassword = ref(null);
const matchingPassword = ref(true);
const formError = ref({});

const members = ref(null);
const buildingDetails = ref(null);
const buildingUnits = ref(null);

const getBuildingUnits=()=>{
    let postData={
        page: 1,
        search: null,
        rowPerPage: 1000,
        startDate: null,
        endDate: null,
        building_id: Number(route.params.id)
    }

    commonService.genericRequest('get-building-units', 'post', true, postData).then((response) => {
        if (response.status) {
            buildingUnits.value = response.data;
        } else {
                commonService.showError(toast, response.message);
        }
    })
}


const getBuildingDetails = () => {
        let postData = {
        page: 1,
        search: null,
        rowPerPage: 10,
        startDate: null,
        endDate: null,
        id: Number(route.params.id)
    }
    commonService.genericRequest('get-buildings', 'post', true, postData).then((response) => {
        if (response.status) {
            buildingDetails.value = response?.data?.data[0];
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


onMounted(() => {
    getBuildingUnits();
    getBuildingDetails();
});


</script>

<template>
    <div class="grid">
        <div class="col-12 md:col-3">
            <div class="card p-fluid">
                <h5 class="text-500">Estate Details</h5>

                <div class="field">
                    <label for="name1" class="text-900 font-bold">Name: <span class="font-normal"> {{buildingDetails?.name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">City: <span class="font-normal"> {{buildingDetails?.city_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Address: <span class="font-normal"> {{buildingDetails?.address}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Street: <span class="font-normal"> {{buildingDetails?.street}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">P.O Box: <span class="font-normal"> {{buildingDetails?.p_o_box}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Status: <span class="font-normal"> {{buildingDetails?.status}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Institution: <span class="font-normal"> {{buildingDetails?.institution_name	}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Branch: <span class="font-normal"> {{buildingDetails?.branch_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Description: <span class="font-normal"> {{buildingDetails?.description}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Created By: <span class="font-normal"> {{buildingDetails?.user_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900 font-bold">Created On: <span class="font-normal"> {{buildingDetails?.created_at}}</span></label>
                </div>
            </div>
        </div>

        <div class="col-12 md:col-9">
            <div class="card p-fluid">
                <h5 class="text-500">Units</h5>
                <div class="grid p-fluid mt-3" >
                    <div class="field col-12 md:col-2 " v-for="(unit, index) of buildingUnits?.data" >
                        <i v-if="unit.status == 'Occupied'" class="pi pi-check-square"  style="color: green; font-size: 3rem"></i>
                        <i v-if="unit.status == 'Active'" class="pi pi-stop"  style="color: red; font-size: 3rem"></i>
                        <p style="color: #000">{{unit.name+' '+unit.unit_no}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
