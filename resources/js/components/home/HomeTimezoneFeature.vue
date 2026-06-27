<script setup lang="ts">
import { ArrowRight } from '@lucide/vue';
import { i18n } from '@/lib/i18n';

</script>

<template>
    <section class="border-t border-[#2e2f30] px-6 py-24">
        <div class="mx-auto grid max-w-6xl grid-cols-1 items-center gap-16 md:grid-cols-2">
            <div class="order-2 rounded-2xl border border-[#2e2f30] bg-[#242526] p-8 shadow-xl md:order-1">
                <div class="space-y-4">
                    <div class="flex items-center justify-between rounded-lg border border-[#2e2f30] bg-[#1e1f20] p-3">
                        <h3 class="font-medium text-slate-300">{{ i18n.global.t('home.timezone.origin') }}</h3>
                        <span class="rounded bg-[#334155] px-2 py-1 text-sm text-white">UTC-3 (Brasilia)</span>
                    </div>
                    <div class="my-2 flex justify-center text-slate-500">
                        <ArrowRight class="size-5" />
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-teal-500/30 bg-[#1e1f20] p-3 ring-1 ring-teal-500/20">
                        <h3 class="font-medium text-teal-400">{{ i18n.global.t('home.timezone.local') }}</h3>
                        <span class="rounded bg-teal-500/20 px-2 py-1 text-sm text-teal-400">UTC+1 (Lisboa)</span>
                    </div>
                    <p class="pt-4 text-center text-xs text-slate-500">{{ i18n.global.t('home.timezone.caption') }}</p>
                </div>
            </div>

            <div class="order-1 md:order-2">
                <h2 class="mb-6 text-3xl font-bold text-white">{{ i18n.global.t('home.timezone.title') }}</h2>
                <p class="mb-6 text-lg leading-relaxed text-slate-400">
                    {{ i18n.global.t('home.timezone.description_1') }}
                </p>
                <p class="text-lg leading-relaxed text-slate-400">
                    {{ i18n.global.t('home.timezone.description_2') }}
                </p>
            </div>
        </div>
    </section>
</template>
