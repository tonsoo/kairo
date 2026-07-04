<script setup lang="ts">
import AppHead from '@/components/AppHead.vue';
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <AppHead />

    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
