<script setup lang="ts">
import { ArrowRight } from '@lucide/vue';
import { getAppLocale, translate } from '@/lib/translations';

const locale = getAppLocale();
</script>

<template>
    <section class="border-t border-[#2e2f30] px-6 py-24">
        <div class="mx-auto grid max-w-6xl grid-cols-1 items-center gap-16 md:grid-cols-2">
            <div class="order-2 rounded-2xl border border-[#2e2f30] bg-[#242526] p-8 shadow-xl md:order-1">
                <div class="space-y-4">
                    <div class="flex items-center justify-between rounded-lg border border-[#2e2f30] bg-[#1e1f20] p-3">
                        <span class="font-medium text-slate-300">{{ translate('home.timezone.origin', locale) }}</span>
                        <span class="rounded bg-[#334155] px-2 py-1 text-sm text-white">UTC-3 (Brasilia)</span>
                    </div>
                    <div class="my-2 flex justify-center text-slate-500">
                        <ArrowRight class="size-5" />
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-teal-500/30 bg-[#1e1f20] p-3 ring-1 ring-teal-500/20">
                        <span class="font-medium text-teal-400">{{ translate('home.timezone.local', locale) }}</span>
                        <span class="rounded bg-teal-500/20 px-2 py-1 text-sm text-teal-400">UTC+1 (Lisboa)</span>
                    </div>
                    <p class="pt-4 text-center text-xs text-slate-500">{{ translate('home.timezone.caption', locale) }}</p>
                </div>
            </div>

            <div class="order-1 md:order-2">
                <h2 class="mb-6 text-3xl font-bold text-white">{{ translate('home.timezone.title', locale) }}</h2>
                <p class="mb-6 text-lg leading-relaxed text-slate-400">
                    {{ translate('home.timezone.description_1', locale) }}
                </p>
                <p class="text-lg leading-relaxed text-slate-400">
                    {{ translate('home.timezone.description_2', locale) }}
                </p>
            </div>
        </div>
    </section>
</template>
