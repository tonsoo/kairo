import type { Ref } from 'vue';
import { computed, ref, watch } from 'vue';
import { i18n } from '@/lib/i18n';
import type { TwoFactorConfigContent } from '@/types';

type UseTwoFactorSetupModalOptions = {
    isOpen: Ref<boolean | undefined>;
    requiresConfirmation: Ref<boolean>;
    twoFactorEnabled: Ref<boolean>;
    qrCodeSvg: Ref<string | null>;
    clearSetupData: () => void;
    fetchSetupData: () => Promise<void>;
};

export const useTwoFactorSetupModal = ({
    isOpen,
    requiresConfirmation,
    twoFactorEnabled,
    qrCodeSvg,
    clearSetupData,
    fetchSetupData,
}: UseTwoFactorSetupModalOptions) => {
    const showVerificationStep = ref(false);
    const code = ref('');

    const modalConfig = computed<TwoFactorConfigContent>(() => {
        if (twoFactorEnabled.value) {
            return {
                title: i18n.global.t('settings.two_factor.modal.enabled_title'),
                description: i18n.global.t(
                    'settings.two_factor.modal.enabled_description',
                ),
                buttonText: i18n.global.t(
                    'settings.two_factor.modal.close',
                ),
            };
        }

        if (showVerificationStep.value) {
            return {
                title: i18n.global.t('settings.two_factor.modal.verify_title'),
                description: i18n.global.t(
                    'settings.two_factor.modal.verify_description',
                ),
                buttonText: i18n.global.t(
                    'settings.two_factor.modal.continue',
                ),
            };
        }

        return {
            title: i18n.global.t('settings.two_factor.modal.enable_title'),
            description: i18n.global.t(
                'settings.two_factor.modal.enable_description',
            ),
            buttonText: i18n.global.t('settings.two_factor.modal.continue'),
        };
    });

    const handleModalNextStep = (): void => {
        if (requiresConfirmation.value) {
            showVerificationStep.value = true;

            return;
        }

        clearSetupData();
        isOpen.value = false;
    };

    const handleVerificationSuccess = (): void => {
        isOpen.value = false;
    };

    const resetModalState = (): void => {
        if (twoFactorEnabled.value) {
            clearSetupData();
        }

        showVerificationStep.value = false;
        code.value = '';
    };

    watch(
        isOpen,
        async (nextIsOpen) => {
            if (!nextIsOpen) {
                resetModalState();

                return;
            }

            if (!qrCodeSvg.value) {
                await fetchSetupData();
            }
        },
        { immediate: true },
    );

    return {
        showVerificationStep,
        code,
        modalConfig,
        handleModalNextStep,
        handleVerificationSuccess,
    };
};
