<script setup lang="ts">
import { ScanLine } from '@lucide/vue';
import { toRef } from 'vue';
import TwoFactorSetupIntro from '@/components/two-factor/TwoFactorSetupIntro.vue';
import TwoFactorVerificationForm from '@/components/two-factor/TwoFactorVerificationForm.vue';
import { useTwoFactorSetupModal } from '@/components/two-factor/useTwoFactorSetupModal';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useAppearance } from '@/composables/useAppearance';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';

type Props = {
    requiresConfirmation: boolean;
    twoFactorEnabled: boolean;
};

const { resolvedAppearance } = useAppearance();

const props = defineProps<Props>();
const isOpen = defineModel<boolean>('isOpen');

const { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } =
    useTwoFactorAuth();
const {
    showVerificationStep,
    code,
    modalConfig,
    handleModalNextStep,
    handleVerificationSuccess,
} = useTwoFactorSetupModal({
    isOpen,
    requiresConfirmation: toRef(props, 'requiresConfirmation'),
    twoFactorEnabled: toRef(props, 'twoFactorEnabled'),
    qrCodeSvg,
    clearSetupData,
    fetchSetupData,
});
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader class="flex items-center justify-center">
                <div
                    class="mb-3 w-auto rounded-full border border-border bg-card p-0.5 shadow-sm"
                >
                    <div
                        class="relative overflow-hidden rounded-full border border-border bg-muted p-2.5"
                    >
                        <div
                            class="absolute inset-0 grid grid-cols-5 opacity-50"
                        >
                            <div
                                v-for="i in 5"
                                :key="`col-${i}`"
                                class="border-r border-border last:border-r-0"
                            />
                        </div>
                        <div
                            class="absolute inset-0 grid grid-rows-5 opacity-50"
                        >
                            <div
                                v-for="i in 5"
                                :key="`row-${i}`"
                                class="border-b border-border last:border-b-0"
                            />
                        </div>
                        <ScanLine
                            class="relative z-20 size-6 text-foreground"
                        />
                    </div>
                </div>
                <DialogTitle>{{ modalConfig.title }}</DialogTitle>
                <DialogDescription class="text-center">
                    {{ modalConfig.description }}
                </DialogDescription>
            </DialogHeader>

            <TwoFactorSetupIntro
                v-if="!showVerificationStep"
                :qr-code-svg="qrCodeSvg"
                :manual-setup-key="manualSetupKey"
                :errors="errors"
                :button-text="modalConfig.buttonText"
                :resolved-appearance="resolvedAppearance"
                @continue="handleModalNextStep"
            />

            <TwoFactorVerificationForm
                v-else
                v-model="code"
                @back="showVerificationStep = false"
                @success="handleVerificationSuccess"
            />
        </DialogContent>
    </Dialog>
</template>
