<script setup lang="ts">
import { Clock3 } from '@lucide/vue';
import { getAppLocale, translate } from '@/lib/translations';

const locale = getAppLocale();
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-xl bg-teal-500/90 text-white shadow-sm"
    >
        <Clock3 class="size-4" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-semibold">Shiftly</span>
        <span class="truncate text-xs text-sidebar-foreground/60">{{ translate('app.logo.tagline', locale) }}</span>
    </div>
</template>
