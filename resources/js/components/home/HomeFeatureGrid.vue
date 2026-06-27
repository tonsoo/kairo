<script setup lang="ts">
import { CalendarDays, History, User } from '@lucide/vue';
import { computed } from 'vue';
import { i18n } from '@/lib/i18n';


const features = computed(() => [
    {
        title: i18n.global.t('home.features.flexible.title'),
        description: i18n.global.t('home.features.flexible.description'),
        icon: CalendarDays,
    },
    {
        title: i18n.global.t('home.features.history.title'),
        description: i18n.global.t('home.features.history.description'),
        icon: History,
    },
    {
        title: i18n.global.t('home.features.privacy.title'),
        description: i18n.global.t('home.features.privacy.description'),
        icon: User,
    },
]);
</script>

<template>
    <section id="recursos" class="border-t border-[#2e2f30] bg-[#18191a] px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <div class="mb-16 text-center">
                <h2 class="mb-4 text-3xl font-bold text-white">{{ i18n.global.t('home.features.title') }}</h2>
                <p class="mx-auto max-w-2xl text-slate-400">{{ i18n.global.t('home.features.description') }}</p>
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
