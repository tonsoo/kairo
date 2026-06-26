<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppSettingsController from '@/actions/App/Http/Controllers/Settings/AppSettingsController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import TimezoneSelect from '@/components/settings/TimezoneSelect.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { getAppLocale, translate } from '@/lib/translations';
import { edit } from '@/routes/app-settings';

type Props = {
    timezone: string;
    timezones: string[];
};

const props = defineProps<Props>();
const locale = getAppLocale();
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
    <Head :title="translate('settings.app.page_title', locale)" />

    <h1 class="sr-only">{{ translate('settings.app.page_title', locale) }}</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            :title="translate('settings.app.heading', locale)"
            :description="translate('settings.app.description', locale)"
        />

        <Form
            v-bind="AppSettingsController.update.form()"
            :options="{ preserveScroll: true }"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <input type="hidden" name="timezone" :value="selectedTimezone">

            <div class="grid gap-2">
                <Label for="timezone">{{ translate('settings.app.timezone', locale) }}</Label>
                <TimezoneSelect id="timezone" v-model="selectedTimezone" :options="props.timezones" />
                <InputError :message="errors.timezone" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing">{{ translate('shared.actions.save', locale) }}</Button>
            </div>
        </Form>
    </div>
</template>
