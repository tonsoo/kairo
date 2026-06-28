<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import HomeCallToAction from '@/components/home/HomeCallToAction.vue';
import HomeFeatureGrid from '@/components/home/HomeFeatureGrid.vue';
import HomeFooter from '@/components/home/HomeFooter.vue';
import HomeHeader from '@/components/home/HomeHeader.vue';
import HomeHero from '@/components/home/HomeHero.vue';
import HomeHowItWorks from '@/components/home/HomeHowItWorks.vue';
import HomeTimezoneFeature from '@/components/home/HomeTimezoneFeature.vue';
import { i18n } from '@/lib/i18n';
import { dashboard, login, register } from '@/routes';
import type { User } from '@/types/auth';

type WelcomePageProps = {
    auth: {
        user: User | null;
    };
    locale: 'en' | 'pt-BR';
};

const page = usePage<WelcomePageProps>();
const canonicalUrl = 'https://kairo.alysson-thoaldo.com.br/';
const isAuthenticated = computed(() => page.props.auth.user !== null);
const dashboardHref = dashboard().url;
const loginHref = login().url;
const registerHref = register().url;
const primaryHref = computed(() =>
    isAuthenticated.value ? dashboardHref : registerHref,
);
const secondaryHref = computed(() =>
    isAuthenticated.value ? dashboardHref : loginHref,
);
const primaryLabel = computed(() =>
    isAuthenticated.value
        ? i18n.global.t('home.hero.cta.primary_auth')
        : i18n.global.t('home.hero.cta.primary_guest'),
);
const currentLocale = computed(() =>
    page.props.locale === 'pt-BR' ? 'pt-BR' : 'en',
);
const metaTitle = computed(() => i18n.global.t('home.meta.title'));
const metaDescription = computed(() => i18n.global.t('home.meta.description'));
const metaTitleContent = computed(() => `${metaTitle.value} - Kairo`);
const ogLocale = computed(() =>
    currentLocale.value === 'pt-BR' ? 'pt_BR' : 'en_US',
);
const alternateOgLocale = computed(() =>
    ogLocale.value === 'pt_BR' ? 'en_US' : 'pt_BR',
);
const structuredData = computed(() =>
    JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'SoftwareApplication',
        name: 'Kairo',
        applicationCategory: 'BusinessApplication',
        operatingSystem: 'Web',
        url: canonicalUrl,
        inLanguage: currentLocale.value,
        description: metaDescription.value,
        offers: {
            '@type': 'Offer',
            price: '0',
            priceCurrency: 'USD',
        },
    }),
);
const currentYear = new Date().getFullYear();
</script>

<template>
    <Head :title="metaTitle">
        <meta name="description" :content="metaDescription" />
        <meta
            name="robots"
            content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1"
        />
        <meta name="application-name" content="Kairo" />
        <link rel="canonical" :href="canonicalUrl" />
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="Kairo" />
        <meta property="og:title" :content="metaTitleContent" />
        <meta property="og:description" :content="metaDescription" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:locale" :content="ogLocale" />
        <meta property="og:locale:alternate" :content="alternateOgLocale" />
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" :content="metaTitleContent" />
        <meta name="twitter:description" :content="metaDescription" />
        <script
            type="application/ld+json"
            v-html="structuredData"
        />
    </Head>

    <div
        class="min-h-screen bg-[#1e1f20] font-sans text-slate-300 selection:bg-teal-500/30 selection:text-white"
    >
        <HomeHeader
            :dashboard-href="dashboardHref"
            :login-href="loginHref"
            :register-href="registerHref"
            :is-authenticated="isAuthenticated"
        />

        <main class="w-full">
            <HomeHero
                :primary-href="primaryHref"
                :secondary-href="secondaryHref"
                :primary-label="primaryLabel"
            />
            <HomeHowItWorks />
            <HomeTimezoneFeature />
            <HomeFeatureGrid />
            <HomeCallToAction :href="primaryHref" :label="primaryLabel" />
        </main>

        <HomeFooter :year="currentYear" />
    </div>
</template>
