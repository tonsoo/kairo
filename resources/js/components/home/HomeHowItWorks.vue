<script setup lang="ts">
import { Check, Play } from '@lucide/vue';
import { i18n } from '@/lib/i18n';

</script>

<template>
    <section id="como-funciona" class="border-t border-[#2e2f30] bg-[#18191a] px-6 py-24">
        <div class="mx-auto grid max-w-6xl grid-cols-1 items-center gap-16 md:grid-cols-2">
            <div>
                <h2 class="mb-6 text-3xl font-bold text-white">{{ i18n.global.t('home.how_it_works.title') }}</h2>
                <p class="mb-6 text-lg leading-relaxed text-slate-400">
                    {{ i18n.global.t('home.how_it_works.description') }}
                </p>
                <ul class="space-y-4 text-slate-300">
                    <li class="flex items-start gap-3">
                        <div class="mt-1 flex size-5 shrink-0 items-center justify-center rounded bg-teal-500/20 text-teal-400">
                            <Check class="size-3.5" />
                        </div>
                        <span>{{ i18n.global.t('home.how_it_works.point_balance') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="mt-1 flex size-5 shrink-0 items-center justify-center rounded bg-teal-500/20 text-teal-400">
                            <Check class="size-3.5" />
                        </div>
                        <span>{{ i18n.global.t('home.how_it_works.point_action') }}</span>
                    </li>
                </ul>
            </div>

            <div class="rounded-2xl border border-[#2e2f30] bg-[#242526] p-8 shadow-xl">
                <div class="space-y-6">
                    <div class="flex items-center justify-between border-b border-[#2e2f30] pb-4">
                        <div>
                            <h3 class="text-sm font-semibold tracking-wide text-slate-500 uppercase">{{ i18n.global.t('home.how_it_works.card.balance_label') }}</h3>
                            <p class="mt-1 text-3xl font-bold text-white">01:13 <span class="ml-2 text-sm font-normal text-teal-400">{{ i18n.global.t('home.how_it_works.card.balance_value_status') }}</span></p>
                        </div>
                        <div class="size-16 rounded-full border-[6px] border-[#334155] border-t-teal-500 border-r-teal-500" />
                    </div>

                    <div class="flex items-center gap-4 rounded-lg border border-[#2e2f30] bg-[#1e1f20] p-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-500/10 text-teal-400">
                            <Play :size="18" fill="currentColor" />
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-white">{{ i18n.global.t('home.how_it_works.card.shift_title') }}</h3>
                            <p class="text-xs text-slate-400">{{ i18n.global.t('home.how_it_works.card.shift_description') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
