<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { i18n } from '@/lib/i18n';
</script>

<template>
    <AppLogoIcon class="size-8 shrink-0" />
    <div class="ml-1 grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-semibold">Kairo</span>
        <span class="truncate text-xs text-sidebar-foreground/60">{{
            i18n.global.t('app.logo.tagline')
        }}</span>
    </div>
</template>
