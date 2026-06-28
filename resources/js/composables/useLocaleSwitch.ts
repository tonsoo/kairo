import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { AppLocale } from '@/lib/i18n';
import { getAppLocale } from '@/lib/i18n';
import { update as updateLocale } from '@/routes/locale';

export function useLocaleSwitch() {
    const currentLocale = ref<AppLocale>(getAppLocale());
    const isSwitching = ref(false);

    function switchLocale(nextLocale: AppLocale): void {
        if (nextLocale === currentLocale.value || isSwitching.value) {
            return;
        }

        isSwitching.value = true;

        router.visit(updateLocale(nextLocale), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                window.location.reload();
            },
            onFinish: () => {
                isSwitching.value = false;
            },
        });
    }

    return {
        currentLocale,
        isSwitching,
        switchLocale,
    };
}
