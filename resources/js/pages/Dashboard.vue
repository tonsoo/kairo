<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, watch } from 'vue';
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
    getBalanceStatusLabelKey,
    getMonthEndDate,
    resolveBalanceStatus,
    resolveChartMaxMinutes,
} from '@/components/dashboard/dashboardData';
import DashboardMetricCard from '@/components/dashboard/DashboardMetricCard.vue';
import DashboardMonthCard from '@/components/dashboard/DashboardMonthCard.vue';
import DashboardSemesterCard from '@/components/dashboard/DashboardSemesterCard.vue';
import { useHoursSummary } from '@/composables/useHoursSummary';
import { useMonthJourneyShifts } from '@/composables/useMonthJourneyShifts';
import {
    getDashboardLocale,
    translateDashboard,
} from '@/lib/dashboardTranslations';

const locale = getDashboardLocale();
const { hoursSummaryData, errorMessageKey, isLoading, fetchHoursSummary } = useHoursSummary();
const { shifts: monthJourneyShifts, fetchMonthJourneyShifts } = useMonthJourneyShifts();

onMounted(() => {
    void fetchHoursSummary();
});

watch(
    () => hoursSummaryData.value,
    async (summary) => {
        if (summary === null) {
            return;
        }

        await fetchMonthJourneyShifts(
            summary.month.starts_at,
            getMonthEndDate(summary.month.starts_at),
        );
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
        monthJourneyShifts.value,
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

const monthTitle = computed(() => {
    if (hoursSummaryData.value === null) {
        return translateDashboard('dashboard.hours.month.title', locale);
    }

    return `${formatMonthHeading(hoursSummaryData.value.month.starts_at, locale)} • ${translateDashboard('dashboard.hours.month.title', locale)}: ${formatDurationMinutes(hoursSummaryData.value.month.balance_minutes, { signed: true })}`;
});
</script>

<template>
    <div class="px-8 py-8">
        <Head title="Dashboard" />

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
                    :items="semesterItems"
                    :legend="chartLegend"
                    class="lg:col-span-9"
                />
            </div>

            <DashboardMonthCard
                :title="monthTitle"
                :items="monthItems"
                :journey-items="monthJourneyItems"
                :legend="chartLegend"
                :max-minutes="monthChartMaxMinutes"
            />
        </div>
    </div>
</template>
