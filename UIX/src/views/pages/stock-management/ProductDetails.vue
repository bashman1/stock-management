<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import CommonService from '@/service/CommonService'
import { useToast } from 'primevue/usetoast';
const route = useRoute()

const toast = useToast();

const commonService = new CommonService();
const router = useRouter();
const productDetails = ref(null)

const getProductDetail=()=>{
    let postData={
        id: route.params.id,
    }
    commonService.genericRequest('get-product-details', 'post', true, postData).then((response) => {
         if (response.status) {
            productDetails.value = response?.data[0];
         } else {
             commonService.showError(toast, response.message);
         }
     })
}

onMounted(() => {
    getProductDetail();
});


</script>

<template>
    <div class="grid">
        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5 class="text-500">Product Details</h5>
                <div class="field">
<!--                    <p>Product Name: <span> {{productDetails?.name}}</span></p>-->
                    <label for="name1" class="text-900">Product Name: <span> {{productDetails?.name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Product No.: <span> {{productDetails?.product_no}}</span></label>
                </div>

                <div class="field">
                    <label for="name1" class="text-900">Product Type: <span> {{productDetails?.type_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Product Category: <span> {{productDetails?.category_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Product Sub Category: <span> {{productDetails?.sub_category_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Product Gauge: <span> {{productDetails?.gauge_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Status: <span> {{productDetails?.status}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Ref. No: <span> {{productDetails?.ref_no}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">VAT: <span> {{productDetails?.tax_config}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Description: <span> {{productDetails?.description}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Institution: <span> {{productDetails?.institution_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Branch: <span> {{productDetails?.branch_name}}</span></label>
                </div>
                <div class="field">
                    <label for="name1" class="text-900">Created By: <span> {{productDetails?.user_name}}</span></label>
                </div>
            </div>

            <div class="card p-fluid">
                <h5 class="text-500">Manufacturer</h5>
                <!-- <div class="formgrid grid"> -->
                    <div class="field">
                        <label class="text-900" for="name1">Name: <span> {{productDetails?.manufacturer_name}}</span></label>
                    </div>

                    <div class="field">
                        <label class="text-900" for="name1">Website: <span> {{productDetails?.manufacturer_website}}</span></label>
                    </div>

                    <div class="field">
                        <label class="text-900" for="name1">Email: <span> {{productDetails?.manufacturer_email}}</span></label>
                    </div>

                    <div class="field">
                        <label class="text-900" for="name1">Phone Number: <span> {{productDetails?.manufacturer_phone_number}}</span></label>
                    </div>

                    <div class="field">
                        <label class="text-900" for="name1">Country: <span> {{productDetails?.manufacturer_country}}</span></label>
                    </div>

                <!-- </div> -->
            </div>
        </div>

        <div class="col-12 md:col-6">
            <div class="card p-fluid">
                <h5 class="text-500">Stock</h5>
                <div class="field">
                    <label class="text-900" for="name1">Quantity: <span> {{productDetails?.quantity}}</span></label>
                </div>
                <div class="field">
                    <label class="text-900" for="name1">Purchase Price: <span> {{productDetails?.purchase_price}}</span></label>
                </div>
                <div class="field">
                    <label class="text-900" for="name1">Selling Price: <span> {{productDetails?.selling_price}}</span></label>
                </div>
                <div class="field">
                    <label class="text-900" for="name1">Min Quantity: <span> {{productDetails?.min_quantity}}</span></label>
                </div>
                <div class="field">
                    <label class="text-900" for="name1">Max Quantity: <span> {{productDetails?.max_quantity}}</span></label>
                </div>
                <div class="field">
                    <label class="text-900" for="name1">Measurement: <span> {{productDetails?.measurement_unit}}</span></label>
                </div>
                <div class="field">
                    <label class="text-900" for="name1">Stocked: <span> {{productDetails?.stock_date}}</span></label>
                </div>
                <div class="field">
                    <label class="text-900" for="name1">Manufacturing: <span> {{productDetails?.manufactured_date}}</span></label>
                </div>
                <div class="field">
                    <label class="text-900" for="name1">Expiry: <span> {{productDetails?.expiry_date}}</span></label>
                </div>
            </div>

            <div class="card">
                <h5 class="text-500">Supplier</h5>
                <div class="field">
                    <label class="text-900" for="name1">Name: <span> {{productDetails?.supplier_name}}</span></label>
                </div>

                <div class="field">
                    <label class="text-900" for="name1">Website: <span> {{productDetails?.supplier_website}}</span></label>
                </div>

                <div class="field">
                    <label class="text-900" for="name1">Email: <span> {{productDetails?.supplier_email}}</span></label>
                </div>

                <div class="field">
                    <label class="text-900" for="name1">Phone Number: <span> {{productDetails?.supplier_phone_number}}</span></label>
                </div>

                <div class="field">
                    <label class="text-900" for="name1">Country: <span> {{productDetails?.supplier_country}}</span></label>
                </div>
            </div>
        </div>
    </div>
</template>
