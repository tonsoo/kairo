<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppSettingsController from '@/actions/App/Http/Controllers/Panel/Settings/AppSettingsController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import TimezoneSelect from '@/components/settings/TimezoneSelect.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { i18n } from '@/lib/i18n';
import { edit } from '@/routes/app-settings';

type Props = {
    timezone: string;
    timezones: string[];
};

const props = defineProps<Props>();
const selectedTimezone = ref(props.timezone);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'App settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="i18n.global.t('settings.app.page_title')" />

    <h1 class="sr-only">{{ i18n.global.t('settings.app.page_title') }}</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            :title="i18n.global.t('settings.app.heading')"
            :description="i18n.global.t('settings.app.description')"
        />

        <Form
            v-bind="AppSettingsController.update.form()"
            :options="{ preserveScroll: true }"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <input type="hidden" name="timezone" :value="selectedTimezone">

            <div class="grid gap-2">
                <Label for="timezone">{{ i18n.global.t('settings.app.timezone') }}</Label>
                <TimezoneSelect id="timezone" v-model="selectedTimezone" :options="props.timezones" />
                <InputError :message="errors.timezone" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">{{ i18n.global.t('shared.actions.save') }}</Button>
            </div>
        </Form>
    </div>
</template>
