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
            commonService.removeStorage()
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
        <p>Use this page to start from scratch and place your custom content.</p>
    </div>
</template>
