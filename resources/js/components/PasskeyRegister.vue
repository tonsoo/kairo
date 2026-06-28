<script setup lang="ts">
import { usePasskeyRegister } from '@laravel/passkeys/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { i18n } from '@/lib/i18n';

const emit = defineEmits<{
    success: [];
}>();

const getDefaultPasskeyName = () => {
    if (typeof navigator === 'undefined') {
        return '';
    }

    const ua = navigator.userAgent;

    const browser = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'].find(
        (browser) => new RegExp(browser).test(ua),
    );

    const os = ['iPhone', 'iPad', 'Android', 'Mac', 'Windows'].find((os) =>
        new RegExp(os).test(ua),
    );

    return [browser, os].filter(Boolean).join(' on ') || '';
};

const name = ref(getDefaultPasskeyName());
const showForm = ref(false);

const { register, isLoading, error, isSupported } = usePasskeyRegister({
    onSuccess: () => {
        name.value = '';
        showForm.value = false;
        emit('success');
    },
});

const handleSubmit = async (event: Event) => {
    event.preventDefault();

    if (!name.value.trim()) {
        return;
    }

    await register(name.value);
};

const handleCancel = () => {
    showForm.value = false;
    name.value = '';
};
</script>

<template>
    <div v-if="!isSupported" class="text-sm text-muted-foreground">
        {{ i18n.global.t('settings.passkeys.unsupported') }}
    </div>

    <Button v-else-if="!showForm" variant="outline" @click="showForm = true">
        {{ i18n.global.t('settings.passkeys.add') }}
    </Button>

    <form
        v-else
        @submit="handleSubmit"
        class="space-y-4 rounded-lg border border-border bg-muted/50 p-4"
    >
        <div class="grid gap-2">
            <Label for="passkey-name">{{
                i18n.global.t('settings.passkeys.name')
            }}</Label>
            <Input
                id="passkey-name"
                type="text"
                v-model="name"
                :placeholder="i18n.global.t('settings.passkeys.placeholder')"
                class="mt-1 block w-full border-foreground/20"
                autofocus
            />
            <p class="text-xs text-muted-foreground">
                {{ i18n.global.t('settings.passkeys.name_hint') }}
            </p>
        </div>

        <InputError v-if="error" :message="error" />

        <div class="flex gap-2">
            <Button type="submit" :disabled="isLoading || !name.trim()">
                {{
                    isLoading
                        ? i18n.global.t('settings.passkeys.registering')
                        : i18n.global.t('settings.passkeys.register')
                }}
            </Button>
            <Button type="button" variant="ghost" @click="handleCancel">
                {{ i18n.global.t('shared.actions.cancel') }}
            </Button>
        </div>
    </form>
</template>
