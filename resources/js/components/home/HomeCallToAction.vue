<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import { getAppLocale, translate } from '@/lib/translations';

defineProps<{
    href: string;
    label: string;
}>();

const locale = getAppLocale();
</script>

<template>
    <section class="bg-gradient-to-b from-[#1e1f20] to-[#18191a] px-6 py-24">
        <div class="relative mx-auto max-w-4xl overflow-hidden rounded-3xl border border-[#2e2f30] bg-[#242526] p-12 text-center shadow-2xl md:p-16">
            <div class="pointer-events-none absolute top-0 right-0 -mr-20 -mt-20 size-64 rounded-full bg-teal-500/10 blur-3xl" />

            <h2 class="relative z-10 mb-6 text-3xl font-bold text-white md:text-4xl">{{ translate('home.cta.title', locale) }}</h2>
            <p class="relative z-10 mx-auto mb-10 max-w-xl text-lg text-slate-400">
                {{ translate('home.cta.description', locale) }}
            </p>
            <Link :href="href" class="relative z-10 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-10 py-4 text-lg font-medium text-white shadow-lg transition-all hover:bg-teal-500">
                {{ label }} <ArrowRight class="size-5" />
            </Link>
        </div>
    </section>
</template>
