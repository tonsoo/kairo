import { createI18n } from 'vue-i18n';

type TranslationMessages = Record<string, string>;

const messages = Object.fromEntries(
    Object.entries(
        import.meta.glob<{ default: TranslationMessages }>(
            '../../../lang/*.json',
            { eager: true },
        ),
    ).map(([path, module]) => [
        path.split('/').pop()!.replace('.json', ''),
        module.default,
    ]),
) as Record<string, TranslationMessages>;

const fallbackLocale = Object.keys(messages).sort()[0] ?? 'en';

export type AppLocale = string;
export type DashboardLocale = AppLocale;

export const getDashboardLocale = getAppLocale;

export const i18n = createI18n({
    legacy: false,
    globalInjection: false,
    locale: getAppLocale(),
    fallbackLocale,
    flatJson: true,
    messages,
});

export function getAppLocale(): AppLocale {
    if (typeof document === 'undefined') {
        return fallbackLocale;
    }

    return document.documentElement.lang || fallbackLocale;
}

export function syncAppLocale(locale?: string): AppLocale {
    const nextLocale = locale ?? getAppLocale();

    i18n.global.locale.value = nextLocale;

    if (typeof document !== 'undefined') {
        document.documentElement.lang = nextLocale;
    }

    return nextLocale;
}
