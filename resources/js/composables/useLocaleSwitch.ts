import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { AppLocale } from '@/lib/i18n';

type LocaleOption = {
    code: AppLocale;
    url: string;
};

type SharedPageProps = {
    locale: AppLocale;
    localeOptions: LocaleOption[];
};

export function useLocaleSwitch() {
    const page = usePage<SharedPageProps>();
    const currentLocale = computed<AppLocale>(() => page.props.locale);
    const localeOptions = computed<LocaleOption[]>(
        () => page.props.localeOptions,
    );
    const isSwitching = ref(false);

    function switchLocale(option: LocaleOption): void {
        if (option.code === currentLocale.value || isSwitching.value) {
            return;
        }

        isSwitching.value = true;
        window.location.assign(option.url);
    }

    return {
        currentLocale,
        localeOptions,
        isSwitching,
        switchLocale,
    };
}
