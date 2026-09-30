<script setup lang="ts">
import { computed, onMounted } from 'vue';
import HolidaysCalendarView from '@/components/holidays/HolidaysCalendarView.vue';
import { useHolidays } from '@/composables/useHolidays';
import { formatHistoryMonthHeading } from '@/lib/history';
import { getDashboardLocale, i18n } from '@/lib/i18n';

type CountryOption = {
    code: string;
    name: string;
};

const props = defineProps<{
    countries: CountryOption[];
    initialMonth: string;
    initialCountryCode: string | null;
}>();

const locale = getDashboardLocale();
const {
    holidays,
    selectedMonthStart,
    selectedCountryCode,
    isLoading,
    isSavingHolidayId,
    errorMessageKey,
    fetchHolidays,
    showPreviousMonth,
    showNextMonth,
    setCountryCode,
    toggleFollow,
} = useHolidays(props.initialMonth, props.initialCountryCode);

const monthHeading = computed(() => formatHistoryMonthHeading(selectedMonthStart.value, locale));

onMounted(() => {
    void fetchHolidays();
});
</script>

<template>
    <div class="space-y-6 px-8 py-8">
        <section class="rounded-3xl border border-border bg-card p-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-muted-foreground">
                        {{ i18n.global.t('panel.item.holidays') }}
                    </p>
                    <h1 class="text-3xl font-semibold text-foreground">{{ i18n.global.t('holidays.page.title') }}</h1>
                    <p class="text-sm text-muted-foreground">{{ i18n.global.t('holidays.page.description') }}</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <label class="space-y-2 text-sm font-medium text-foreground">
                        <span>{{ i18n.global.t('holidays.field.country') }}</span>
                        <select
                            :value="selectedCountryCode"
                            class="min-w-56 rounded-2xl border border-border bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-teal-500/50"
                            @change="void setCountryCode(($event.target as HTMLSelectElement).value)"
                        >
                            <option value="">{{ i18n.global.t('holidays.field.all_countries') }}</option>
                            <option v-for="country in props.countries" :key="country.code" :value="country.code">
                                {{ country.name }}
                            </option>
                        </select>
                    </label>

                    <div class="flex items-center gap-2 self-start pt-7 sm:self-auto sm:pt-0">
                        <button
                            type="button"
                            class="rounded-2xl border border-border bg-background px-4 py-3 text-sm font-medium text-foreground transition hover:border-teal-500/35 hover:bg-teal-500/[0.04]"
                            @click="void showPreviousMonth()"
                        >
                            {{ i18n.global.t('holidays.action.previous_month') }}
                        </button>
                        <button
                            type="button"
                            class="rounded-2xl border border-border bg-background px-4 py-3 text-sm font-medium text-foreground transition hover:border-teal-500/35 hover:bg-teal-500/[0.04]"
                            @click="void showNextMonth()"
                        >
                            {{ i18n.global.t('holidays.action.next_month') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                <span class="rounded-full border border-border px-3 py-1">{{ monthHeading }}</span>
                <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-emerald-700 dark:text-emerald-200">
                    {{ i18n.global.t('holidays.follow_hint') }}
                </span>
            </div>
        </section>

        <p
            v-if="errorMessageKey"
            class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-200"
        >
            {{ i18n.global.t(errorMessageKey) }}
        </p>

        <p
            v-else-if="isLoading"
            class="rounded-2xl border border-border bg-card px-4 py-3 text-sm text-muted-foreground"
        >
            {{ i18n.global.t('holidays.loading') }}
        </p>

        <p
            v-else-if="holidays.length === 0"
            class="rounded-2xl border border-border bg-card px-4 py-3 text-sm text-muted-foreground"
        >
            {{ i18n.global.t('holidays.empty_month') }}
        </p>

        <HolidaysCalendarView
            v-else
            :locale="locale"
            :month-start="selectedMonthStart"
            :holidays="holidays"
            :saving-holiday-id="isSavingHolidayId"
            @toggle="void toggleFollow($event)"
        />
    </div>
</template>
