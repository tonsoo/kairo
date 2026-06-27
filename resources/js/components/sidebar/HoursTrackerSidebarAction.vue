<script setup lang="ts">
import { Clock3, Play } from '@lucide/vue';
import { i18n } from '@/lib/i18n';

</script>

<template>
    <div class="rounded-2xl border border-teal-500/20 bg-teal-500/8 p-3">
        <div class="mb-3 flex items-center justify-between gap-3">
            <div
                class="flex items-center gap-2 text-xs tracking-[0.22em] text-teal-600 uppercase dark:text-teal-400"
            >
                <Clock3 class="size-3.5" />
                <span>{{ i18n.global.t('panel.action.title') }}</span>
            </div>
            <span
                class="rounded-full bg-white/80 px-2 py-0.5 text-[10px] font-semibold text-slate-500 dark:bg-white/8 dark:text-slate-300"
            >
                {{ i18n.global.t('panel.action.badge') }}
            </span>
        </div>

        <button
            type="button"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-400"
        >
            <Play class="size-4 fill-current" />
            <span>{{ i18n.global.t('dashboard.shift.start') }}</span>
        </button>
    </div>
</template>
