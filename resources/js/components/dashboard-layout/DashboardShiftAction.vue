<script setup lang="ts">
import { Square, Play } from '@lucide/vue';
import { computed, onMounted } from 'vue';
import { useCurrentShiftState } from '@/composables/useCurrentShiftState';
import {
    getDashboardLocale,
    translateDashboard,
} from '@/lib/dashboardTranslations';

const locale = getDashboardLocale();
const {
    currentShiftStateData,
    errorMessageKey,
    isLoading,
    isSubmitting,
    buttonLabelKey,
    fetchCurrentShiftState,
    submitNextAction,
} = useCurrentShiftState();

onMounted(() => {
    void fetchCurrentShiftState();
});

const iconComponent = computed(() =>
    currentShiftStateData.value?.next_action === 'end' ? Square : Play,
);

const isBusy = computed(() => isLoading.value || isSubmitting.value);
</script>

<template>
    <button
        type="button"
        class="flex w-full items-center justify-center gap-2 rounded-md border border-teal-400/20 bg-teal-500/10 px-4 py-2.5 text-sm font-medium text-teal-300 transition hover:bg-teal-500/20 disabled:cursor-default disabled:opacity-70"
        :disabled="isBusy"
        @click="void submitNextAction()"
    >
        <component :is="iconComponent" class="size-4 fill-current" />
        <span>
            {{
                isBusy
                    ? translateDashboard('dashboard.shift.loading', locale)
                    : translateDashboard(buttonLabelKey, locale)
            }}
        </span>
    </button>

    <p v-if="errorMessageKey" class="text-center text-xs text-rose-300">
        {{ translateDashboard(errorMessageKey, locale) }}
    </p>
</template>
