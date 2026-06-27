import { createI18n } from 'vue-i18n';
import en from '../../../lang/en.json';
import ptBR from '../../../lang/pt-BR.json';

export type AppLocale = 'en' | 'pt-BR';
export type DashboardLocale = AppLocale;

export const localeSwitchOptions = [
    { value: 'pt-BR', label: 'PT-BR' },
    { value: 'en', label: 'EN' },
] as const satisfies ReadonlyArray<{ value: AppLocale; label: string }>;

export const getDashboardLocale = getAppLocale;

export const i18n = createI18n({
    legacy: false,
    globalInjection: false,
    locale: getAppLocale(),
    fallbackLocale: 'en',
    flatJson: true,
    messages: {
        en,
        'pt-BR': ptBR,
    },
});

export function getAppLocale(): AppLocale {
    if (typeof document === 'undefined') {
        return 'en';
    }

    return normalizeAppLocale(document.documentElement.lang);
}

export function syncAppLocale(locale?: string): AppLocale {
    const normalizedLocale = normalizeAppLocale(locale);

    i18n.global.locale.value = normalizedLocale;

    if (typeof document !== 'undefined') {
        document.documentElement.lang = normalizedLocale;
    }

    return normalizedLocale;
}

function normalizeAppLocale(locale?: string): AppLocale {
    if (typeof locale === 'string' && locale.toLowerCase().startsWith('pt')) {
        return 'pt-BR';
    }

    return 'en';
}
