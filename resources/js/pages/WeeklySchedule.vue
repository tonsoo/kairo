<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import WeeklyScheduleEditor from '@/components/weekly-schedule/WeeklyScheduleEditor.vue';
import { useWeeklyWorkSchedules } from '@/composables/useWeeklyWorkSchedules';
import { i18n } from '@/lib/i18n';
import type { Auth } from '@/types';

type WeeklySchedulePageProps = {
    auth: Auth;
};

const page = usePage<WeeklySchedulePageProps>();
const userTimezone = typeof page.props.auth.user.timezone === 'string'
    && page.props.auth.user.timezone.length > 0
    ? page.props.auth.user.timezone
    : 'UTC';
const {
    form,
    isLoading,
    errorMessageKey,
    fetchWorkSchedules,
    saveWorkSchedules,
} = useWeeklyWorkSchedules(userTimezone);

onMounted(() => {
    void fetchWorkSchedules();
});
</script>

<template>
    <div class="px-8 py-8">
        <Head :title="i18n.global.t('weekly_schedule.title')" />

        <WeeklyScheduleEditor
            v-model:rows="form.schedules"
            :effective-from="form.effective_from"
            :timezone="userTimezone"
            :errors="form.errors"
            :is-loading="isLoading"
            :is-submitting="form.processing"
            :is-dirty="form.isDirty"
            :recently-successful="form.recentlySuccessful"
            :error-message-key="errorMessageKey"
            @submit="void saveWorkSchedules()"
        />
    </div>
</template>
