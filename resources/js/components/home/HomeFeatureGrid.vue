<script setup lang="ts">
import { CalendarDays, History, User } from '@lucide/vue';
import { computed } from 'vue';
import { getAppLocale, translate } from '@/lib/translations';

const locale = getAppLocale();

const features = computed(() => [
    {
        title: translate('home.features.flexible.title', locale),
        description: translate('home.features.flexible.description', locale),
        icon: CalendarDays,
    },
    {
        title: translate('home.features.history.title', locale),
        description: translate('home.features.history.description', locale),
        icon: History,
    },
    {
        title: translate('home.features.privacy.title', locale),
        description: translate('home.features.privacy.description', locale),
        icon: User,
    },
]);
</script>

<template>
    <section id="recursos" class="border-t border-[#2e2f30] bg-[#18191a] px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <div class="mb-16 text-center">
                <h2 class="mb-4 text-3xl font-bold text-white">{{ translate('home.features.title', locale) }}</h2>
                <p class="mx-auto max-w-2xl text-slate-400">{{ translate('home.features.description', locale) }}</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <article v-for="feature in features" :key="feature.title" class="flex h-full flex-col rounded-2xl border border-[#2e2f30] bg-[#1e1f20] p-8 transition-colors hover:border-slate-500">
                    <div class="mb-6 flex size-12 items-center justify-center rounded-xl border border-[#2e2f30] bg-[#242526] text-slate-300">
                        <component :is="feature.icon" class="size-6" />
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-slate-200">{{ feature.title }}</h3>
                    <p class="flex-1 leading-relaxed text-slate-400">{{ feature.description }}</p>
                </article>
            </div>
        </div>
    </section>
</template>
