<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { getAppLocale, translate } from '@/lib/translations';
import { edit } from '@/routes/appearance';

const locale = getAppLocale();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="translate('settings.appearance.page_title', locale)" />

    <h1 class="sr-only">{{ translate('settings.appearance.page_title', locale) }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="translate('settings.appearance.heading', locale)"
            :description="translate('settings.appearance.description', locale)"
        />
        <AppearanceTabs />
    </div>
</template>
