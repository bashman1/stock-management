<script setup>
import { ref, onMounted } from 'vue';
import {useRoute, useRouter} from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const memberBatch = ref(null);
const commonService = new CommonService();
const router = useRouter();
const route = useRoute()


const getNotificationTypes=()=>{
    const postData = {
        // id: 3
    };
    commonService.genericRequest('get-notification-types', 'post', true, postData).then((response) => {
        if (response.status) {
            memberBatch.value = response.data;
        } else {
            commonService.showError(toast, response.message);
        }
    })
}


const goToApprove=(event)=>{
    router.push("/approve-uploaded-buildings/"+event?.id);
}

onMounted(() => {
    getNotificationTypes();
});

</script>
<template>
    <div className="card">
        <h5>Notification Types</h5>
<!--        <p>Use this page to start from scratch and place your custom content.</p>-->
        <DataTable :value="memberBatch" :paginator="true" size="small" stripedRows :rows="10" dataKey="id"
                   :rowHover="true" filterDisplay="menu" responsiveLayout="scroll" >
            <Column field="name" header="Name" style="min-width: 10rem"/>
            <Column field="type" header="Type" style="min-width: 10rem"/>
            <Column field="description" header="Description" style="min-width: 10rem"/>
            <Column field="status" header="Status" style="min-width: 10rem"/>
            <Column header="Date" style="min-width: 10rem">
                <template #body="{ data }">
                    {{ commonService.formatDate(data.created_at) }}
                </template>
            </Column>


        </DataTable>
    </div>
</template>
