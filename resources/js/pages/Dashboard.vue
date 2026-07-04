<script setup lang="ts">
import { chartLegend } from '@/components/dashboard/dashboardData';
import DashboardMetricCard from '@/components/dashboard/DashboardMetricCard.vue';
import DashboardMonthCard from '@/components/dashboard/DashboardMonthCard.vue';
import DashboardSemesterCard from '@/components/dashboard/DashboardSemesterCard.vue';
import ShiftExportDialog from '@/components/shift-export/ShiftExportDialog.vue';
import { useDashboardPage } from '@/composables/useDashboardPage';
import { i18n } from '@/lib/i18n';
import type { ShiftExportFormatOption } from '@/lib/shiftExport';

const props = defineProps<{
    shiftExportFormats: ShiftExportFormatOption[];
}>();

const {
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
} = useDashboardPage();
</script>

<template>
    <div class="px-8 py-8">

        <div class="space-y-6">
            <p
                v-if="errorMessageKey"
                class="rounded-md border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-200"
            >
                {{ i18n.global.t(errorMessageKey) }}
            </p>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.25fr)]">
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-1">
                    <DashboardMetricCard
                        :title="i18n.global.t('dashboard.hours.balance.title')"
                        :highlight="balanceCard.highlight"
                        :meter-value="balanceCard.meterValue"
                        :meter-caption="balanceCard.meterCaption"
                        :segments="balanceCard.segments"
                    />

                    <DashboardMetricCard
                        :title="i18n.global.t('dashboard.hours.today.title')"
                        :highlight="todayCard.highlight"
                        :meter-value="todayCard.meterValue"
                        :meter-caption="todayCard.meterCaption"
                        :segments="todayCard.segments"
                        :legend="todayCard.legend"
                    />
                </div>

                <DashboardSemesterCard
                    :title="semesterTitle"
                    :items="semesterItems"
                    :legend="chartLegend"
                    :can-go-previous="hoursSummaryData !== null"
                    :can-go-next="canGoToNextSemester"
                    :can-export="hoursSummaryData !== null"
                    @previous="void showPreviousSemester()"
                    @next="void showNextSemester()"
                    @export="activeExportTarget = 'semester'"
                />
            </div>

            <DashboardMonthCard
                :title="monthTitle"
                :items="monthItems"
                :journey-items="monthJourneyItems"
                :legend="chartLegend"
                :max-minutes="monthChartMaxMinutes"
                :can-go-previous="hoursSummaryData !== null"
                :can-go-next="canGoToNextMonth"
                :can-export="hoursSummaryData !== null"
                @previous="void showPreviousMonth()"
                @next="void showNextMonth()"
                @export="activeExportTarget = 'month'"
            />

            <p
                v-if="isLoading"
                class="rounded-md border border-border bg-card px-4 py-3 text-sm text-muted-foreground"
            >
                {{ i18n.global.t('dashboard.hours.loading') }}
            </p>
        </div>

        <ShiftExportDialog
            v-if="activeExportRange !== null"
            :open="activeExportTarget !== null"
            :locale="locale"
            title-key="exports.dialog.title"
            :description-key="activeExportRange.descriptionKey"
            :formats="props.shiftExportFormats"
            :initial-from="activeExportRange.from"
            :initial-to="activeExportRange.to"
            :editable-range="false"
            @update:open="activeExportTarget = $event ? activeExportTarget : null"
        />
    </div>
</template>
