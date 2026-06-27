<script setup lang="ts">
import { Clock3 } from '@lucide/vue';
import { i18n } from '@/lib/i18n';

</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-xl bg-teal-500/90 text-white shadow-sm"
    >
        <Clock3 class="size-4" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-semibold">Shiftly</span>
        <span class="truncate text-xs text-sidebar-foreground/60">{{ i18n.global.t('app.logo.tagline') }}</span>
    </div>
</template>
