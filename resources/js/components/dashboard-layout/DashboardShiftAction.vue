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

const buttonClass = computed(() => {
    switch (currentShiftStateData.value?.next_action) {
        case 'end':
            return 'border-rose-400/25 bg-rose-500/12 text-rose-300 hover:bg-rose-500/22';
        case 'continue':
            return 'border-sky-400/25 bg-sky-500/12 text-sky-300 hover:bg-sky-500/22';
        default:
            return 'border-emerald-400/25 bg-emerald-500/12 text-emerald-300 hover:bg-emerald-500/22';
    }
});

const isBusy = computed(() => isLoading.value || isSubmitting.value);
</script>

<template>
    <button
        type="button"
        :class="[
            'flex w-full items-center justify-center gap-2 rounded-md border px-4 py-2.5 text-sm font-medium transition disabled:cursor-default disabled:opacity-70',
            buttonClass,
        ]"
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
