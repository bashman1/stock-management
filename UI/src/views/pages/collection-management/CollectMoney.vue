<script setup>
import {ref, onMounted} from 'vue';
import { useRouter } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const commonService = new CommonService();

const router = useRouter();
const searchKey = ref(null);
const mName = ref(null);
const mNumber = ref(null);
const mContact = ref(null);
const tranAmt = ref(null);
const tType = ref(null);
const tDate = ref(null);
const description = ref(null);


</script>

<template>
    <div className="card">
        <h5>Collect Money</h5>
        <p>Use this page to start from scratch and place your custom content.</p>
        <Toast/>
        <div class="grid p-fluid mt-3">
            <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <InputText type="text" id="search" v-model="searchKey"  />  <!-- class="p-invalid"-->
                        <label for="search">Search Member</label>
                    </span>
            </div>
            <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="name" v-model="mName"  />  <!-- class="p-invalid"-->
                        <label for="name">Member Name</label>
                    </span>
            </div>
            <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="number" v-model="mNumber"  />  <!-- class="p-invalid"-->
                        <label for="number">Member Number</label>
                    </span>
            </div>
            <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="contact" v-model="mContact"  />  <!-- class="p-invalid"-->
                        <label for="contact">Member Contact</label>
                    </span>
            </div>
            <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="amount" v-model="tranAmt"  />  <!-- class="p-invalid"-->
                        <label for="amount">Amount</label>
                    </span>
            </div>
            <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="type" v-model="tType"  />  <!-- class="p-invalid"-->
                        <label for="type">Transact type</label>
                    </span>
            </div>
            <div class="field col-12 md:col-4">
                    <span class="p-float-label">
                        <InputText type="text" id="date" v-model="tDate"  />  <!-- class="p-invalid"-->
                        <label for="date">Date</label>
                    </span>
            </div>
            <div class="field col-12 md:col-12">
                    <span class="p-float-label">
                        <Textarea  id="textarea" rows="3" cols="30" v-model="description"></Textarea>
                        <label for="textarea">Description</label>
                    </span>
            </div>

            <div class="field col-12 md:col-8"></div>
            <div class="field col-12 md:col-4">
                <Button @click="onSubmit" label="SUBMIT" class="p-button-outlined mr-2 mb-2" />
            </div>

        </div>
    </div>
</template>
