import { computed, onMounted, ref, watch } from 'vue';
import {
    buildBalanceSegments,
    buildLegendFromSegments,
    buildMonthChartItems,
    buildMonthJourneyItems,
    buildSemesterChartItems,
    buildTodaySegments,
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
import { useHoursSummary } from '@/composables/useHoursSummary';
import { useShiftsInRange } from '@/composables/useShiftsInRange';
import { getDashboardLocale, i18n } from '@/lib/i18n';

export type DashboardExportTarget = 'month' | 'semester';

export const useDashboardPage = () => {
    const locale = getDashboardLocale();
    const { hoursSummaryData, errorMessageKey, isLoading, fetchHoursSummary } =
        useHoursSummary();
    const {
        shifts: monthJourneyShifts,
        fetchShiftsInRange: fetchMonthJourneyShifts,
    } = useShiftsInRange();
    const {
        shifts: todayShifts,
        fetchShiftsInRange: fetchTodayShifts,
    } = useShiftsInRange();
    const selectedMonthStart = ref<string | null>(null);
    const selectedSemesterStart = ref<string | null>(null);
    const activeExportTarget = ref<DashboardExportTarget | null>(null);

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
                fetchTodayShifts(summary.today.date, summary.today.date),
            ]);
        },
    );

    const balanceCard = computed(() => {
        if (hoursSummaryData.value === null) {
            return {
                highlight: '--:--',
                meterValue: '00:00',
                meterCaption: i18n.global.t(
                    'dashboard.hours.balance.status.zero',
                ),
                segments: [],
            };
        }

        const { balance } = hoursSummaryData.value;
        const status = resolveBalanceStatus(balance.balance_minutes);

        return {
            highlight: formatDurationMinutes(balance.balance_minutes, {
                signed: true,
            }),
            meterValue: formatDurationMinutes(balance.balance_minutes),
            meterCaption: i18n.global.t(getBalanceStatusLabelKey(status)),
            segments: buildBalanceSegments(balance),
        };
    });

    const todayCard = computed(() => {
        if (hoursSummaryData.value === null) {
            return {
                highlight: '--:--',
                meterValue: '--:--',
                meterCaption: '',
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
            highlight: formatDurationMinutes(
                hoursSummaryData.value.today.worked_minutes,
            ),
            meterValue: formatDurationMinutes(
                hoursSummaryData.value.today.worked_minutes,
            ),
            meterCaption: '',
            segments,
            legend: buildLegendFromSegments(segments),
        };
    });

    const semesterItems = computed(() =>
        hoursSummaryData.value === null
            ? []
            : buildSemesterChartItems(
                hoursSummaryData.value.semester.items,
                locale,
            ),
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

    const canGoToNextMonth = computed(
        () =>
            selectedMonthStart.value !== null
            && currentMonthStart.value !== null
            && selectedMonthStart.value < currentMonthStart.value,
    );

    const canGoToNextSemester = computed(
        () =>
            selectedSemesterStart.value !== null
            && currentSemesterStart.value !== null
            && selectedSemesterStart.value < currentSemesterStart.value,
    );

    const monthTitle = computed(() => {
        if (hoursSummaryData.value === null) {
            return i18n.global.t('dashboard.hours.month.title');
        }

        return `${formatMonthHeading(hoursSummaryData.value.month.starts_at, locale)} • ${i18n.global.t('dashboard.hours.month.title')}: ${formatDurationMinutes(hoursSummaryData.value.month.balance_minutes, { signed: true })}`;
    });

    const semesterTitle = computed(() => {
        if (hoursSummaryData.value === null) {
            return i18n.global.t('dashboard.hours.semester.title');
        }

        return `${formatSemesterHeading(
            hoursSummaryData.value.semester.starts_at,
            hoursSummaryData.value.semester.ends_at,
            locale,
        )} • ${i18n.global.t('dashboard.hours.semester.title')}`;
    });

    const activeExportRange = computed(() => {
        if (
            hoursSummaryData.value === null
            || activeExportTarget.value === null
        ) {
            return null;
        }

        if (activeExportTarget.value === 'month') {
            return {
                from: hoursSummaryData.value.month.starts_at,
                to: hoursSummaryData.value.month.ends_at,
                descriptionKey: 'exports.dialog.description.month',
            };
        }

        return {
            from: hoursSummaryData.value.semester.starts_at,
            to: hoursSummaryData.value.semester.ends_at,
            descriptionKey: 'exports.dialog.description.semester',
        };
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
        if (!canGoToNextMonth.value || selectedMonthStart.value === null) {
            return;
        }

        selectedMonthStart.value = shiftMonthStart(selectedMonthStart.value, 1);

        await fetchDashboardData();
    }

    async function showPreviousSemester(): Promise<void> {
        if (selectedSemesterStart.value === null) {
            return;
        }

        selectedSemesterStart.value = shiftMonthStart(
            selectedSemesterStart.value,
            -6,
        );

        await fetchDashboardData();
    }

    async function showNextSemester(): Promise<void> {
        if (
            !canGoToNextSemester.value
            || selectedSemesterStart.value === null
        ) {
            return;
        }

        selectedSemesterStart.value = shiftMonthStart(
            selectedSemesterStart.value,
            6,
        );

        await fetchDashboardData();
    }

    return {
        locale,
        hoursSummaryData,
        errorMessageKey,
        isLoading,
        balanceCard,
        todayCard,
        semesterItems,
        monthItems,
        monthChartMaxMinutes,
        monthJourneyItems,
        canGoToNextMonth,
        canGoToNextSemester,
        monthTitle,
        semesterTitle,
        activeExportTarget,
        activeExportRange,
        showPreviousMonth,
        showNextMonth,
        showPreviousSemester,
        showNextSemester,
    };
};
