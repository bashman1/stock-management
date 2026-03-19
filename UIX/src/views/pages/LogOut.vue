<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

 const router = useRouter();

 const logOut = () => {
    commonService.genericRequest('log-out', 'get', true, {}).then((response) => {
        if (response.status) {
            commonService.removeStorage();
            commonService.clearPermissionsCache(); // Clear cached permissions on logout
            commonService.redirect(router,"/auth/login");
        } else {
            commonService.showError(toast, response.message);
        }
    })
}

 onMounted(() => {
    logOut();
});

 </script>

<template>
    <div className="card">
        <h5>Log Out</h5>
        <p>Logging you out in a few seconds...</p>
    </div>
</template>
