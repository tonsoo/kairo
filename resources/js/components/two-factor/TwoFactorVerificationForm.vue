<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { nextTick, onMounted, useTemplateRef } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { i18n } from '@/lib/i18n';
import { confirm } from '@/routes/two-factor';

const code = defineModel<string>({ required: true });

defineEmits<{
    back: [];
    success: [];
}>();

const pinInputContainerRef = useTemplateRef('pinInputContainerRef');

onMounted(() => {
    void nextTick(() => {
        pinInputContainerRef.value?.querySelector('input')?.focus();
    });
});
</script>

<template>
    <Form
        v-bind="confirm.form()"
        error-bag="confirmTwoFactorAuthentication"
        reset-on-error
        @finish="code = ''"
        @success="$emit('success')"
        v-slot="{ errors, processing }"
    >
        <input type="hidden" name="code" :value="code" />
        <div ref="pinInputContainerRef" class="relative w-full space-y-3">
            <div
                class="flex w-full flex-col items-center justify-center space-y-3 py-2"
            >
                <InputOTP
                    id="otp"
                    v-model="code"
                    :maxlength="6"
                    :disabled="processing"
                    autofocus
                >
                    <InputOTPGroup>
                        <InputOTPSlot
                            v-for="index in 6"
                            :key="index"
                            :index="index - 1"
                        />
                    </InputOTPGroup>
                </InputOTP>
                <InputError :message="errors?.code" />
            </div>

            <div class="flex w-full items-center space-x-5">
                <Button
                    type="button"
                    variant="outline"
                    class="w-auto flex-1"
                    :disabled="processing"
                    @click="$emit('back')"
                >
                    {{ i18n.global.t('settings.two_factor.modal.back') }}
                </Button>
                <Button
                    type="submit"
                    class="w-auto flex-1"
                    :disabled="processing || code.length < 6"
                >
                    {{ i18n.global.t('settings.two_factor.modal.confirm') }}
                </Button>
            </div>
        </div>
    </Form>
</template>
