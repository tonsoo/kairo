<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import {
    buildBalanceSegments,
    buildLegendFromSegments,
    buildMonthChartItems,
    buildMonthJourneyItems,
    buildSemesterChartItems,
    buildTodaySegments,
    chartLegend,
    formatDurationMinutes,
    formatMonthHeading,
    formatSemesterHeading,
    getBalanceStatusLabelKey,
    getCurrentSemesterStart,
    getMonthStartFromDateTime,
    resolveBalanceStatus,
    resolveChartMaxMinutes,
    shiftMonthStart,
} from '@/components/dashboard/dashboardData';
import DashboardMetricCard from '@/components/dashboard/DashboardMetricCard.vue';
import DashboardMonthCard from '@/components/dashboard/DashboardMonthCard.vue';
import DashboardSemesterCard from '@/components/dashboard/DashboardSemesterCard.vue';
import { useHoursSummary } from '@/composables/useHoursSummary';
import { useShiftsInRange } from '@/composables/useShiftsInRange';
import {
    getDashboardLocale,
    translateDashboard,
} from '@/lib/dashboardTranslations';

const locale = getDashboardLocale();
const { hoursSummaryData, errorMessageKey, isLoading, fetchHoursSummary } = useHoursSummary();
const { shifts: monthJourneyShifts, fetchShiftsInRange: fetchMonthJourneyShifts } = useShiftsInRange();
const { shifts: todayShifts, fetchShiftsInRange: fetchTodayShifts } = useShiftsInRange();
const selectedMonthStart = ref<string | null>(null);
const selectedSemesterStart = ref<string | null>(null);

onMounted(() => {
    void fetchDashboardData();
});

watch(
    () => hoursSummaryData.value,
    async (summary) => {
        if (summary === null) {
            return;
        }

        if (selectedMonthStart.value === null) {
            selectedMonthStart.value = summary.month.starts_at;
        }

        if (selectedSemesterStart.value === null) {
            selectedSemesterStart.value = summary.semester.starts_at;
        }

        await Promise.all([
            fetchMonthJourneyShifts(
                summary.month.starts_at,
                summary.month.ends_at,
            ),
            fetchTodayShifts(
                summary.today.date,
                summary.today.date,
            ),
        ]);
    },
);

const balanceCard = computed(() => {
    if (hoursSummaryData.value === null) {
        return {
            highlight: '--:--',
            meterValue: '00:00',
            meterCaption: translateDashboard('dashboard.hours.balance.status.zero', locale),
            segments: [],
        };
    }

    const { balance } = hoursSummaryData.value;
    const status = resolveBalanceStatus(balance.balance_minutes);
    const varianceMinutes = status === 'positive'
        ? balance.positive_minutes
        : status === 'negative'
            ? balance.negative_minutes
            : 0;

    return {
        highlight: formatDurationMinutes(balance.balance_minutes, { signed: true }),
        meterValue: formatDurationMinutes(varianceMinutes),
        meterCaption: translateDashboard(getBalanceStatusLabelKey(status), locale),
        segments: buildBalanceSegments(balance),
    };
});

const todayCard = computed(() => {
    if (hoursSummaryData.value === null) {
        return {
            highlight: '--:--',
            segments: [],
            legend: [],
        };
    }

    const segments = buildTodaySegments(
        hoursSummaryData.value.today,
        hoursSummaryData.value.generated_at,
        todayShifts.value,
    );

    return {
        highlight: formatDurationMinutes(hoursSummaryData.value.today.worked_minutes),
        segments,
        legend: buildLegendFromSegments(segments),
    };
});

const semesterItems = computed(() =>
    hoursSummaryData.value === null
        ? []
        : buildSemesterChartItems(hoursSummaryData.value.semester.items, locale),
);

const monthItems = computed(() =>
    hoursSummaryData.value === null
        ? []
        : buildMonthChartItems(
            hoursSummaryData.value.month.starts_at,
            hoursSummaryData.value.month.items,
        ),
);

const monthChartMaxMinutes = computed(() => {
    if (hoursSummaryData.value === null) {
        return 60;
    }

    const { today } = hoursSummaryData.value;
    const fallbackMinutes = Math.max(
        today.expected_minutes,
        today.regular_minutes + today.extra_minutes,
        today.regular_minutes + today.missing_minutes,
    );

    return resolveChartMaxMinutes(monthItems.value, fallbackMinutes);
});

const monthJourneyItems = computed(() => {
    if (hoursSummaryData.value === null) {
        return [];
    }

    return buildMonthJourneyItems(
        hoursSummaryData.value.month.starts_at,
        hoursSummaryData.value.generated_at,
        monthJourneyShifts.value,
    );
});

const currentMonthStart = computed(() =>
    hoursSummaryData.value === null
        ? null
        : getMonthStartFromDateTime(hoursSummaryData.value.generated_at),
);

const currentSemesterStart = computed(() =>
    hoursSummaryData.value === null
        ? null
        : getCurrentSemesterStart(hoursSummaryData.value.generated_at),
);

const canGoToNextMonth = computed(() =>
    selectedMonthStart.value !== null
    && currentMonthStart.value !== null
    && selectedMonthStart.value < currentMonthStart.value,
);

const canGoToNextSemester = computed(() =>
    selectedSemesterStart.value !== null
    && currentSemesterStart.value !== null
    && selectedSemesterStart.value < currentSemesterStart.value,
);

const monthTitle = computed(() => {
    if (hoursSummaryData.value === null) {
        return translateDashboard('dashboard.hours.month.title', locale);
    }

    return `${formatMonthHeading(hoursSummaryData.value.month.starts_at, locale)} • ${translateDashboard('dashboard.hours.month.title', locale)}: ${formatDurationMinutes(hoursSummaryData.value.month.balance_minutes, { signed: true })}`;
});

const semesterTitle = computed(() => {
    if (hoursSummaryData.value === null) {
        return translateDashboard('dashboard.hours.semester.title', locale);
    }

    return `${formatSemesterHeading(
        hoursSummaryData.value.semester.starts_at,
        hoursSummaryData.value.semester.ends_at,
        locale,
    )} • ${translateDashboard('dashboard.hours.semester.title', locale)}`;
});

async function fetchDashboardData(): Promise<void> {
    await fetchHoursSummary({
        month: selectedMonthStart.value ?? undefined,
        semesterStart: selectedSemesterStart.value ?? undefined,
    });
}

async function showPreviousMonth(): Promise<void> {
    if (selectedMonthStart.value === null) {
        return;
    }

    selectedMonthStart.value = shiftMonthStart(selectedMonthStart.value, -1);

    await fetchDashboardData();
}

async function showNextMonth(): Promise<void> {
    if (! canGoToNextMonth.value || selectedMonthStart.value === null) {
        return;
    }

    selectedMonthStart.value = shiftMonthStart(selectedMonthStart.value, 1);

    await fetchDashboardData();
}

async function showPreviousSemester(): Promise<void> {
    if (selectedSemesterStart.value === null) {
        return;
    }

    selectedSemesterStart.value = shiftMonthStart(selectedSemesterStart.value, -6);

    await fetchDashboardData();
}

async function showNextSemester(): Promise<void> {
    if (! canGoToNextSemester.value || selectedSemesterStart.value === null) {
        return;
    }

    selectedSemesterStart.value = shiftMonthStart(selectedSemesterStart.value, 6);

    await fetchDashboardData();
}
</script>

<template>
    <div class="px-8 py-8">
        <Head :title="translateDashboard('dashboard.page.title', locale)" />

        <div class="space-y-6">
            <p
                v-if="errorMessageKey"
                class="rounded-md border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"
            >
                {{ translateDashboard(errorMessageKey, locale) }}
            </p>

            <p
                v-else-if="isLoading && hoursSummaryData === null"
                class="rounded-md border border-slate-700 bg-[#18191a] px-4 py-3 text-sm text-slate-400"
            >
                {{ translateDashboard('dashboard.hours.loading', locale) }}
            </p>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="space-y-6 lg:col-span-3">
                    <DashboardMetricCard
                        :title="translateDashboard('dashboard.hours.balance.title', locale)"
                        :highlight="balanceCard.highlight"
                        :meter-value="balanceCard.meterValue"
                        :meter-caption="balanceCard.meterCaption"
                        :segments="balanceCard.segments"
                    />
                    <DashboardMetricCard
                        :title="translateDashboard('dashboard.hours.today.title', locale)"
                        :highlight="todayCard.highlight"
                        highlight-class="text-slate-100"
                        :meter-value="todayCard.highlight"
                        :meter-caption="translateDashboard('dashboard.hours.today.title', locale)"
                        :segments="todayCard.segments"
                        :legend="todayCard.legend"
                    />
                </div>

                <DashboardSemesterCard
                    :title="semesterTitle"
                    :items="semesterItems"
                    :legend="chartLegend"
                    :can-go-previous="true"
                    :can-go-next="canGoToNextSemester"
                    class="lg:col-span-9"
                    @previous="void showPreviousSemester()"
                    @next="void showNextSemester()"
                />
            </div>

            <DashboardMonthCard
                :title="monthTitle"
                :items="monthItems"
                :journey-items="monthJourneyItems"
                :legend="chartLegend"
                :max-minutes="monthChartMaxMinutes"
                :can-go-previous="true"
                :can-go-next="canGoToNextMonth"
                @previous="void showPreviousMonth()"
                @next="void showNextMonth()"
            />
        </div>
    </div>
</template>
