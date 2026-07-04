<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type PageMeta = {
    title: string;
    description?: string | null;
    robots: string;
    applicationName: string;
    canonical: string;
    alternates: Array<{
        locale: string;
        url: string;
    }>;
    openGraph: {
        type: string;
        siteName: string;
        title: string;
        description?: string | null;
        url: string;
        locale: string;
        alternateLocales: string[];
    };
    twitter: {
        card: string;
        title: string;
        description?: string | null;
    };
    structuredData?: string | null;
};

type AppHeadPageProps = {
    meta: PageMeta;
};

const page = usePage<AppHeadPageProps>();
const meta = computed(() => page.props.meta);
</script>

<template>
    <Head :title="meta.title">
        <meta
            v-if="meta.description"
            head-key="description"
            name="description"
            :content="meta.description"
        >
        <meta head-key="robots" name="robots" :content="meta.robots">
        <meta
            head-key="application-name"
            name="application-name"
            :content="meta.applicationName"
        >
        <link head-key="canonical" rel="canonical" :href="meta.canonical">
        <link
            v-for="alternate in meta.alternates"
            :key="alternate.locale"
            :head-key="`alternate-${alternate.locale}`"
            rel="alternate"
            :hreflang="alternate.locale"
            :href="alternate.url"
        >
        <meta head-key="og-type" property="og:type" :content="meta.openGraph.type">
        <meta
            head-key="og-site-name"
            property="og:site_name"
            :content="meta.openGraph.siteName"
        >
        <meta head-key="og-title" property="og:title" :content="meta.openGraph.title">
        <meta
            v-if="meta.openGraph.description"
            head-key="og-description"
            property="og:description"
            :content="meta.openGraph.description"
        >
        <meta head-key="og-url" property="og:url" :content="meta.openGraph.url">
        <meta
            head-key="og-locale"
            property="og:locale"
            :content="meta.openGraph.locale"
        >
        <meta
            v-for="alternateLocale in meta.openGraph.alternateLocales"
            :key="alternateLocale"
            :head-key="`og-locale-alternate-${alternateLocale}`"
            property="og:locale:alternate"
            :content="alternateLocale"
        >
        <meta head-key="twitter-card" name="twitter:card" :content="meta.twitter.card">
        <meta
            head-key="twitter-title"
            name="twitter:title"
            :content="meta.twitter.title"
        >
        <meta
            v-if="meta.twitter.description"
            head-key="twitter-description"
            name="twitter:description"
            :content="meta.twitter.description"
        >
        <script
            v-if="meta.structuredData"
            head-key="structured-data"
            type="application/ld+json"
            v-html="meta.structuredData"
        />
    </Head>
</template>
