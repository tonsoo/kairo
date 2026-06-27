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
};

const page = usePage<WelcomePageProps>();
const isAuthenticated = computed(() => page.props.auth.user !== null);
const dashboardHref = dashboard().url;
const loginHref = login().url;
const registerHref = register().url;
const primaryHref = computed(() => isAuthenticated.value ? dashboardHref : registerHref);
const secondaryHref = computed(() => isAuthenticated.value ? dashboardHref : loginHref);
const primaryLabel = computed(() => isAuthenticated.value ? i18n.global.t('home.hero.cta.primary_auth') : i18n.global.t('home.hero.cta.primary_guest'));
const currentYear = new Date().getFullYear();
</script>

<template>
    <Head title="Shiftly" />

    <div class="min-h-screen bg-[#1e1f20] font-sans text-slate-300 selection:bg-teal-500/30 selection:text-white">
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
