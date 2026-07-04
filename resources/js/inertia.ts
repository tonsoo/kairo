import type { Page } from '@inertiajs/core';
import type { App as VueApp } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { i18n, syncAppLocale } from '@/lib/i18n';
import { addUrlDefault } from './wayfinder';

export const inertiaProgress = {
    color: '#4B5563',
};

export function resolveInertiaLayout(name: string): unknown {
    switch (true) {
        case name === 'Welcome':
            return null;
        case ['Dashboard', 'History', 'WeeklySchedule'].includes(name):
            return DashboardLayout;
        case name.startsWith('auth/'):
            return AuthLayout;
        case name.startsWith('settings/'):
            return [DashboardLayout, SettingsLayout];
        default:
            return AppLayout;
    }
}

export function configureInertiaApp(
    app: VueApp,
    { page }: { page: Page; ssr: boolean },
): void {
    const routeDefaults = page.props.routeDefaults;

    if (typeof routeDefaults === 'object' && routeDefaults !== null) {
        for (const [key, value] of Object.entries(routeDefaults)) {
            if (
                typeof value === 'string'
                || typeof value === 'number'
                || typeof value === 'boolean'
            ) {
                addUrlDefault(key, value);
            }
        }
    }

    syncAppLocale(
        typeof page.props.locale === 'string' ? page.props.locale : undefined,
    );
    app.use(i18n);
}
