<script setup>
import Spinner from './views/pages/Spinner.vue';
// import {action, state} from '../store';
import { actions, state } from '../store';
import { applyPersistedAppearance } from '@/layout/composables/layout';

// re-apply the saved theme + scale on every load so they persist across reloads
applyPersistedAppearance();
</script>

<template>
    <Spinner v-if="state.isLoading"/>
    <router-view />
</template>

<style scoped></style>
