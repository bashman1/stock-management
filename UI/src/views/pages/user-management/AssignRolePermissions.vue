<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const roleDetails = ref(null);
const roleId = ref(null);
const commonService = new CommonService();

const router = useRouter();
const route = useRoute()

const getRoleDetails = (id) => {
    commonService.genericRequest('get-role/' + id, 'get', true, {}).then((response) => {
        if (response.status) {
            roleDetails.value = response.data;

        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onChangeSwitch = (event, permission) => {
    let postObject = {
        permissionId: permission.id,
        event: event.target.checked,
        roleId: roleId.value,
    };
    commonService.genericRequest("assign-role-permission", "post", true, postObject).then(
        (response) => {
            if (response.status) {
            }
        }
    );
}

const isActive = (permission) => {
    return commonService.existingRolePermission(roleDetails?.value?.rolePermissions, permission);
}


onMounted(() => {
    roleId.value = route.params.id;
    getRoleDetails(roleId.value);
});

</script>

<template>
    <div className="card">
        <h5>{{ roleDetails?.role?.name }}</h5>

        <div class="grid">
            <div class="col-12 md:col-3" v-for="permission in roleDetails?.permissions" :key="permission.id">
                <div class="field-checkbox mb-0">
                    <label>
                        <input id="my-checkbox" type="checkbox" @change="onChangeSwitch($event, permission)"
                            :checked="isActive(permission)" class="large-checkbox" />
                        {{ permission.name }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>
