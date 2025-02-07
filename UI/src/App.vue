<script setup>
import Spinner from './views/pages/Spinner.vue';
// import {action, state} from '../store';
import { actions, state } from '../store';
</script>

<template>
    <Spinner v-if="state.isLoading"/>
    <router-view />
</template>

<style scoped></style>
