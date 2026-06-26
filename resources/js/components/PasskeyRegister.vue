<script setup lang="ts">
import { usePasskeyRegister } from '@laravel/passkeys/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { getAppLocale, translate } from '@/lib/translations';

const emit = defineEmits<{
    success: [];
}>();

const locale = getAppLocale();

const getDefaultPasskeyName = () => {
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
        {{ translate('settings.passkeys.unsupported', locale) }}
    </div>

    <Button v-else-if="!showForm" variant="outline" @click="showForm = true">
        {{ translate('settings.passkeys.add', locale) }}
    </Button>

    <form
        v-else
        @submit="handleSubmit"
        class="space-y-4 rounded-lg border border-border bg-muted/50 p-4"
    >
        <div class="grid gap-2">
            <Label for="passkey-name">{{ translate('settings.passkeys.name', locale) }}</Label>
            <Input
                id="passkey-name"
                type="text"
                v-model="name"
                :placeholder="translate('settings.passkeys.placeholder', locale)"
                class="mt-1 block w-full border-foreground/20"
                autofocus
            />
            <p class="text-xs text-muted-foreground">
                {{ translate('settings.passkeys.name_hint', locale) }}
            </p>
        </div>

        <InputError v-if="error" :message="error" />

        <div class="flex gap-2">
            <Button type="submit" :disabled="isLoading || !name.trim()">
                {{ isLoading ? translate('settings.passkeys.registering', locale) : translate('settings.passkeys.register', locale) }}
            </Button>
            <Button type="button" variant="ghost" @click="handleCancel">
                {{ translate('shared.actions.cancel', locale) }}
            </Button>
        </div>
    </form>
</template>
