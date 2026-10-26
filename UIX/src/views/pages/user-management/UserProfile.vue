<script setup>
import { ref, computed, onMounted } from 'vue';
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

// --- presentation helpers ---
const val = (x) => (x !== null && x !== undefined && String(x).trim() !== '') ? x : '—';
const fmtDate = (d) => {
    if (!d) return '—';
    const dt = new Date(d);
    return isNaN(dt.getTime()) ? d : dt.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};
const initials = computed(() => {
    const f = userDetails.value?.first_name?.[0] ?? '';
    const l = userDetails.value?.last_name?.[0] ?? '';
    return (f + l).toUpperCase() || 'U';
});
const fullName = computed(() => {
    const u = userDetails.value;
    if (!u) return '';
    return [u.first_name, u.last_name, u.other_name].filter(Boolean).join(' ');
});
const statusSeverity = (s) => {
    const v = (s || '').toLowerCase();
    if (v === 'active') return 'success';
    if (v === 'archived' || v === 'deleted') return 'danger';
    if (v === 'inactive' || v === 'suspended') return 'warning';
    return 'info';
};

const canResetPassword = computed(() =>
    !!oldPassword.value && !!newPassword.value && !!confirmPassword.value && matchingPassword.value
);

const personalFields = computed(() => [
    { icon: 'pi-user', label: 'Gender', value: val(userDetails.value?.gender) },
    { icon: 'pi-calendar', label: 'Date of Birth', value: fmtDate(userDetails.value?.date_of_birth) },
    { icon: 'pi-info-circle', label: 'Description', value: val(userDetails.value?.description) },
]);
const contactFields = computed(() => [
    { icon: 'pi-envelope', label: 'Email', value: val(userDetails.value?.email) },
    { icon: 'pi-phone', label: 'Phone', value: val(userDetails.value?.phone_number) },
    { icon: 'pi-map-marker', label: 'Address', value: val(userDetails.value?.address) },
    { icon: 'pi-directions', label: 'Street', value: val(userDetails.value?.street) },
    { icon: 'pi-map', label: 'City', value: val(userDetails.value?.city) },
    { icon: 'pi-inbox', label: 'P.O. Box', value: val(userDetails.value?.p_o_box) },
]);
const orgFields = computed(() => [
    { icon: 'pi-id-card', label: 'User Type', value: val(userDetails.value?.user_type) },
    { icon: 'pi-tag', label: 'User Category', value: val(userDetails.value?.user_category) },
    { icon: 'pi-building', label: 'Institution', value: val(userDetails.value?.institution) },
    { icon: 'pi-sitemap', label: 'Branch', value: val(userDetails.value?.branch) },
    { icon: 'pi-shield', label: 'Role', value: val(userDetails.value?.role) },
    { icon: 'pi-user-edit', label: 'Created By', value: val(userDetails.value?.created_by) },
]);

const getUserDetail = () => {
    let postData = { userId: route.params.id }
    commonService.genericRequest('get-user-details', 'post', true, postData).then((response) => {
        if (response.status) {
            userDetails.value = response.data[0];
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const getAssignedUserBranches = () => {
    let postData = { status: "Active", user_id: route.params.id }
    commonService.genericRequest('get-assigned-user-branches', 'post', true, postData).then((response) => {
        if (response.status) {
            assignedBranches.value = response?.data
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const switchBranch = () => {
    let previousData = commonService.getStorage();
    if (!selectedBrach.value) {
        commonService.showError(toast, "Please Select a branch");
        return
    }
    let postData = { userId: route.params.id, branchId: selectedBrach.value.branch_id }
    commonService.genericRequest('switch-branches', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            if (previousData?.userData?.id == response?.data?.id) {
                const storedData = { token: previousData?.token, userData: response.data };
                commonService.setStorage(storedData);
                router.go(0);
            }
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

const onInputBlur = (value, key) => {
    formError.value[key] = commonService.validateFormField(value);
}

const checkMatchingPassword = () => {
    matchingPassword.value = newPassword.value === confirmPassword.value;
}

const onPasswordReset = () => {
    formError.value.oldPassword = commonService.validateFormField(oldPassword.value);
    formError.value.newPassword = commonService.validateFormField(newPassword.value);
    formError.value.confirmPassword = commonService.validateFormField(confirmPassword.value);

    let invalid = commonService.validateRequiredFields(formError.value);
    if (invalid || newPassword.value != confirmPassword.value) {
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData = {
        userId: route.params.id,
        oldPassword: oldPassword.value,
        newPassword: newPassword.value,
        confirmPassword: confirmPassword.value,
    }

    commonService.genericRequest('reset-password', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            oldPassword.value = null; newPassword.value = null; confirmPassword.value = null;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

onMounted(() => {
    getUserDetail();
    getAssignedUserBranches();
});
</script>

<template>
    <div class="grid">
        <Toast />

        <!-- Profile header -->
        <div class="col-12">
            <div class="card profile-header">
                <template v-if="userDetails">
                    <div class="flex flex-column md:flex-row align-items-center gap-4">
                        <Avatar v-if="userDetails.image" :image="userDetails.image" shape="circle" size="xlarge"
                            class="profile-avatar" />
                        <Avatar v-else :label="initials" shape="circle" size="xlarge" class="profile-avatar" />

                        <div class="flex-1 text-center md:text-left">
                            <div class="flex align-items-center justify-content-center md:justify-content-start gap-2 flex-wrap">
                                <h4 class="m-0">{{ fullName }}</h4>
                                <Tag v-if="userDetails.role" :value="userDetails.role" severity="info" />
                                <Tag v-if="userDetails.status" :value="userDetails.status" :severity="statusSeverity(userDetails.status)" />
                            </div>
                            <div class="text-600 mt-1">
                                <i class="pi pi-building mr-1" />{{ val(userDetails.institution) }}
                                <span class="mx-2">·</span>
                                <i class="pi pi-sitemap mr-1" />{{ val(userDetails.branch) }}
                            </div>
                            <div class="flex align-items-center justify-content-center md:justify-content-start gap-2 mt-3 flex-wrap">
                                <Chip v-if="userDetails.email" :label="userDetails.email" icon="pi pi-envelope" />
                                <Chip v-if="userDetails.phone_number" :label="userDetails.phone_number" icon="pi pi-phone" />
                            </div>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="flex align-items-center gap-4">
                        <Skeleton shape="circle" size="5rem" />
                        <div class="flex-1">
                            <Skeleton width="14rem" height="1.6rem" class="mb-2" />
                            <Skeleton width="20rem" height="1rem" class="mb-2" />
                            <Skeleton width="24rem" height="1.2rem" />
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Details -->
        <div class="col-12 lg:col-8">
            <div class="card">
                <h5 class="mb-3">Profile Details</h5>

                <template v-if="userDetails">
                    <div class="detail-section">
                        <span class="section-title text-700"><i class="pi pi-user mr-2" />Personal</span>
                        <div class="grid mt-2">
                            <div v-for="f in personalFields" :key="f.label" class="col-12 md:col-6">
                                <div class="detail-row">
                                    <i :class="'pi ' + f.icon" />
                                    <div>
                                        <div class="detail-label text-600">{{ f.label }}</div>
                                        <div class="detail-value text-900">{{ f.value }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Divider />

                    <div class="detail-section">
                        <span class="section-title text-700"><i class="pi pi-map-marker mr-2" />Contact</span>
                        <div class="grid mt-2">
                            <div v-for="f in contactFields" :key="f.label" class="col-12 md:col-6">
                                <div class="detail-row">
                                    <i :class="'pi ' + f.icon" />
                                    <div>
                                        <div class="detail-label text-600">{{ f.label }}</div>
                                        <div class="detail-value text-900">{{ f.value }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Divider />

                    <div class="detail-section">
                        <span class="section-title text-700"><i class="pi pi-briefcase mr-2" />Organization</span>
                        <div class="grid mt-2">
                            <div v-for="f in orgFields" :key="f.label" class="col-12 md:col-6">
                                <div class="detail-row">
                                    <i :class="'pi ' + f.icon" />
                                    <div>
                                        <div class="detail-label text-600">{{ f.label }}</div>
                                        <div class="detail-value text-900">{{ f.value }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <Skeleton v-for="n in 6" :key="n" width="100%" height="2.2rem" class="mb-3" />
                </template>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-12 lg:col-4">
            <div class="card p-fluid">
                <h5><i class="pi pi-lock mr-2" />Reset Password</h5>
                <div class="grid p-fluid mt-2">
                    <div class="field col-12">
                        <span class="p-float-label">
                            <Password id="oldPassword" v-model="oldPassword" @blur="onInputBlur(oldPassword, 'oldPassword')"
                                :feedback="false" :class="{ 'p-invalid': formError?.oldPassword }" :toggleMask="true" />
                            <label for="oldPassword">Old Password</label>
                        </span>
                    </div>
                    <div class="field col-12">
                        <span class="p-float-label">
                            <Password id="newPassword" @keyup="checkMatchingPassword" v-model="newPassword"
                                @blur="onInputBlur(newPassword, 'newPassword')"
                                :class="{ 'p-invalid': formError?.newPassword || !matchingPassword }" :toggleMask="true" />
                            <label for="newPassword">New Password</label>
                        </span>
                    </div>
                    <div class="field col-12">
                        <span class="p-float-label">
                            <Password id="confirmPassword" :feedback="false" @keyup="checkMatchingPassword" v-model="confirmPassword"
                                @blur="onInputBlur(confirmPassword, 'confirmPassword')"
                                :class="{ 'p-invalid': formError?.confirmPassword || !matchingPassword }" :toggleMask="true" />
                            <label for="confirmPassword">Confirm Password</label>
                        </span>
                        <small v-if="!matchingPassword" class="text-red-500"><i class="pi pi-times-circle mr-1" />Passwords do not match</small>
                    </div>
                    <div class="col-12">
                        <Button @click="onPasswordReset" label="Update Password" icon="pi pi-check"
                            class="p-button-outlined" :disabled="!canResetPassword" />
                    </div>
                </div>
            </div>

            <div class="card p-fluid">
                <h5><i class="pi pi-sync mr-2" />Switch Branch</h5>
                <div class="grid p-fluid mt-2">
                    <div class="field col-12">
                        <span class="p-float-label">
                            <Dropdown id="branchSelect" :options="assignedBranches" v-model="selectedBrach" optionLabel="name" />
                            <label for="branchSelect">Select Branch</label>
                        </span>
                    </div>
                    <div class="col-12">
                        <Button @click="switchBranch" label="Switch" icon="pi pi-arrow-right-arrow-left" class="p-button-outlined" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.profile-header {
    background: linear-gradient(135deg, var(--primary-50, #eef2ff) 0%, var(--surface-card) 60%);
}
.profile-avatar {
    width: 5rem;
    height: 5rem;
    font-size: 1.75rem;
    background: var(--primary-color);
    color: var(--primary-color-text);
}
.section-title {
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-size: 0.85rem;
}
.detail-row {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.4rem 0;
}
.detail-row > i {
    color: var(--primary-color);
    margin-top: 0.2rem;
}
.detail-label {
    font-size: 0.8rem;
}
/* colour comes from the PrimeFlex text-900 utility so it stays theme-correct */
.detail-value {
    font-weight: 600;
    word-break: break-word;
}
</style>
