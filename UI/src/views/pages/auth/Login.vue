<script setup>
import { useLayout } from '@/layout/composables/layout';
import { ref, computed } from 'vue';
import AppConfig from '@/layout/AppConfig.vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const message = ref([]);

const commonService = new CommonService();

const { layoutConfig } = useLayout();
const router = useRouter();
const email = ref('');
const password = ref('');
const checked = ref(false);
const showResetModal = ref(false)
const globalUserData=ref({});
const newPassword = ref(null);
const confirmPassword = ref(null);
const matchingPassword = ref(true);
const formError = ref({});

const logoUrl = computed(() => {
    // return `layout/images/${layoutConfig.darkTheme.value ? 'logo-white' : 'logo-dark'}.svg`;
    // return `demo/images/Smart-Collect-logo-black-removebg.png`;
    return `demo/images/SmarCollectlogo-removebg-preview.png`;
});


const toggleResetModal=(action)=>{
    showResetModal.value = action
}

const login=  ()=>{
   let postData = {
        email:email.value,
        password:password.value,
    }
    commonService.genericRequest('user-login', 'post', false, postData).then((response)=>{
        if(response.status){
            const storedData = {
                token: response.data.token.accessToken,
                userData: response.data.user_data,
            };
            globalUserData.value = storedData
            if(globalUserData?.value?.userData?.reset_required){
                toggleResetModal(true);
            }else{
                goToDashboard(storedData);
            }
        }else{
            commonService.showError(toast,response.message);
        }
    })
}


const onInputBlur=(value, key)=>{
    formError.value[key] = commonService.validateFormField(value);
}


const onPasswordReset=async()=>{
    // formError.value.oldPassword=commonService.validateFormField(oldPassword.value);
    formError.value.newPassword=commonService.validateFormField(newPassword.value);
    formError.value.confirmPassword=commonService.validateFormField(confirmPassword.value);

    let invalid = commonService.validateRequiredFields(formError.value);
    if(invalid || newPassword.value != confirmPassword.value){
        commonService.showError(toast, "Please fill in the missing field");
        return
    }

    let postData = {
        userId:globalUserData?.value?.userData?.id,
        oldPassword: password.value,
        newPassword: newPassword.value,
        confirmPassword: confirmPassword.value,
    }
    await commonService.setStorage(globalUserData.value);
    commonService.genericRequest('reset-password', 'post', true, postData).then((response) => {
        if (response.status) {
            commonService.showSuccess(toast, response.message);
            toggleResetModal(false)
            newPassword.value = null;
            confirmPassword.value=null;
        } else {
            commonService.showError(toast, response.message);
        }
    })
    await commonService.removeStorage()
}

const checkMatchingPassword=()=>{
    if(newPassword.value === confirmPassword.value ){
        matchingPassword.value = true;
    }else{
        matchingPassword.value = false;
    }
}

const goToDashboard=async(storedData)=>{
    await commonService.setStorage(storedData);
    await redirectToHome();
}

const redirectToHome = ()=>{
    router.push("/admin");
}

const goToSelfRegistration = ()=>{
    router.push("/auth/register");
}

</script>



<template>
    <div class="surface-ground flex align-items-center justify-content-center min-h-screen min-w-screen overflow-hidden">
        <div class="flex flex-column align-items-center justify-content-center">
<!--            <img :src="logoUrl" alt="Sakai logo" class="mb-5 w-6rem flex-shrink-0" />-->
            <img :src="logoUrl" alt="Sakai logo" class="mb-5 w-30rem flex-shrink-0" />
            <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)">
                <div class="w-full surface-card py-8 px-5 sm:px-8" style="border-radius: 53px">
                    <Toast />
                    <div class="text-center mb-5">
<!--                        <img src="/demo/images/login/avatar.png" alt="Image" height="50" class="mb-3" />-->
                        <div class="text-900 text-3xl font-medium mb-3"> </div>
                        <span class="text-600 font-medium">Sign in to continue</span>
                    </div>

                    <div>
                        <label for="email1" class="block text-900 text-xl font-medium mb-2">Email</label>
                        <InputText id="email1" type="text" placeholder="Email address" class="w-full md:w-30rem mb-5" style="padding: 1rem" v-model="email" />

                        <label for="password1" class="block text-900 font-medium text-xl mb-2">Password</label>
                        <Password id="password1" v-model="password" placeholder="Password" :feedback="false" :toggleMask="true" class="w-full mb-3" inputClass="w-full" inputStyle="padding:1rem"></Password>

                        <div class="flex align-items-center justify-content-between mb-5 gap-5">
                            <div class="flex align-items-center">
                            <a class="font-medium no-underline ml-2 text-left cursor-pointer" style="color: var(--primary-color)" @click="goToSelfRegistration" >Self enroll</a>
                                <!-- <Checkbox v-model="checked" id="rememberme1" binary class="mr-2"></Checkbox> -->
                                <!-- <label for="rememberme1">Remember me</label> -->
                            </div>
                            <!-- <a class="font-medium no-underline ml-2 text-left cursor-pointer" style="color: var(--primary-color)">Forgot password?</a> -->
                            <a class="font-medium no-underline ml-2 text-right cursor-pointer" style="color: var(--primary-color)">Forgot password?</a>
                        </div>
                        <Button @click="login" label="Sign In" class="w-full p-3 text-xl"></Button>
                    </div>
                </div>
            </div>
        </div>

        <Dialog header="Reset Password" v-model:visible="showResetModal" :style="{ width: '40vw' }"
        :modal="true" class="p-fluid">
        <div class="grid p-fluid">
            <!-- <div class="field col-12 md:col-12 mt-10">
                <span class="p-float-label">
                    <InputText type="text" id="subject" v-model="subject"
                         />
                    <label for="subject">Subject</label>
                </span>
            </div> -->
            <!-- <div class="field col-12 md:col-12">

            </div> -->
            <!-- <div class="field col-12 md:col-12">
                <QuillEditor  v-model:content="data" contentType="html" toolbar="full" theme="snow" />
            </div> -->

            <div class="field col-12 md:col-12">
                <span class="p-float-label">
                    <Password type="text" id="password"  @keyup="checkMatchingPassword" v-model="newPassword" @blur="onInputBlur(newPassword, 'newPassword')"
                        :class="{ 'p-invalid': formError?.newPassword || !matchingPassword}"  :toggleMask="true"/>
                    <label for="password">New Password</label>
                </span>
            </div>

            <div  class="field col-12 md:col-12">
                <span class="p-float-label">
                    <Password type="text" id="confirmPassword" @keyup="checkMatchingPassword" v-model="confirmPassword"
                        @blur="onInputBlur(confirmPassword, 'confirmPassword')"
                        :class="{ 'p-invalid': formError?.confirmPassword || !matchingPassword  }"  :toggleMask="true"/>
                    <label for="confirmPassword">Confirm Password</label>
                </span>
            </div>


        </div>

        <template #footer>
            <Button label="Cancel" @click="toggleResetModal(false)" icon="pi pi-times"
                class="p-button-outlined p-button-danger mr-2 mb-2" />
            <Button @click="onPasswordReset" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
        </template>
    </Dialog>

    </div>
    <!-- <AppConfig simple /> -->
</template>

<style scoped>
.pi-eye {
    transform: scale(1.6);
    margin-right: 1rem;
}

.pi-eye-slash {
    transform: scale(1.6);
    margin-right: 1rem;
}
</style>
