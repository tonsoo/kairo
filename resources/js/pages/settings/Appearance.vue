<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { i18n } from '@/lib/i18n';
import { edit } from '@/routes/appearance';


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
    <Head :title="i18n.global.t('settings.appearance.page_title')" />

    <h1 class="sr-only">{{ i18n.global.t('settings.appearance.page_title') }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="i18n.global.t('settings.appearance.heading')"
            :description="i18n.global.t('settings.appearance.description')"
        />
        <AppearanceTabs />
    </div>
</template>
