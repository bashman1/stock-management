<script setup>
import { ref, watch } from 'vue'
import QRCode from 'qrcode'

const props = defineProps({
  value: { type: String, required: true },
  size: { type: Number, default: 150 },
})

const imgSrc = ref('')

const generate = async () => {
  imgSrc.value = await QRCode.toDataURL(props.value, {
    width: props.size,
    margin: 2,
  })
}

watch(() => props.value, generate, { immediate: true })
</script>

<template>
  <img v-if="imgSrc" :src="imgSrc" alt="QR Code" />
</template>
