import type { ApexOptions } from 'apexcharts';
import type { DashboardBarItem } from '@/components/dashboard/dashboardData';
import {
    formatDurationMinutes,
    resolveChartMaxMinutes,
} from '@/components/dashboard/dashboardData';
import { i18n } from '@/lib/i18n';
import type { ResolvedAppearance } from '@/types';

type BuildDashboardStackedBarChartOptions = {
    items: DashboardBarItem[];
    appearance: ResolvedAppearance;
    compact: boolean;
    maxMinutes?: number;
    stepCount: number;
};

export function buildDashboardStackedBarChartSeries(
    items: DashboardBarItem[],
): { name: string; data: number[] }[] {
    return [
        {
            name: i18n.global.t('dashboard.hours.legend.worked'),
            data: items.map((item) => item.workedMinutes),
        },
        {
            name: i18n.global.t('dashboard.hours.legend.extra'),
            data: items.map((item) => item.extraMinutes),
        },
        {
            name: i18n.global.t('dashboard.hours.legend.missing'),
            data: items.map((item) => item.missingMinutes),
        },
    ];
}

export function buildDashboardStackedBarChartOptions({
    items,
    appearance,
    compact,
    maxMinutes,
    stepCount,
}: BuildDashboardStackedBarChartOptions): ApexOptions {
    const chartMaxMinutes = maxMinutes ?? resolveChartMaxMinutes(items);
    const isDark = appearance === 'dark';
    const foregroundColor = isDark ? '#94a3b8' : '#737373';
    const borderColor = isDark ? '#334155' : '#e5e5e5';

    return {
        chart: {
            type: 'bar',
            stacked: true,
            animations: {
                enabled: false,
            },
            toolbar: {
                show: false,
            },
            zoom: {
                enabled: false,
            },
            parentHeightOffset: 0,
            foreColor: foregroundColor,
        },
        colors: ['#0d9488', '#be123c', '#a8a3c5'],
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false,
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: compact ? '52%' : '62%',
                borderRadius: 2,
            },
        },
        grid: {
            borderColor,
            strokeDashArray: 4,
            xaxis: {
                lines: {
                    show: true,
                },
            },
            yaxis: {
                lines: {
                    show: true,
                },
            },
            padding: {
                top: 0,
                right: 8,
                bottom: 0,
                left: 0,
            },
        },
        stroke: {
            show: false,
        },
        tooltip: {
            enabled: true,
            shared: true,
            intersect: false,
            theme: isDark ? 'dark' : 'light',
            y: {
                formatter(value: number): string {
                    return formatDurationMinutes(Math.round(value));
                },
            },
        },
        xaxis: {
            categories: items.map((item) => item.label),
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
            labels: {
                style: {
                    colors: foregroundColor,
                    fontSize: compact ? '10px' : '12px',
                },
            },
        },
        yaxis: {
            min: 0,
            max: chartMaxMinutes,
            tickAmount: stepCount,
            forceNiceScale: false,
            labels: {
                formatter(value: number): string {
                    return formatDashboardAxisMinutes(value);
                },
                style: {
                    colors: foregroundColor,
                    fontSize: '11px',
                },
            },
        },
    };
}

function formatDashboardAxisMinutes(value: number): string {
    const roundedMinutes = Math.max(Math.round(value), 0);
    const hours = Math.floor(roundedMinutes / 60);
    const minutes = roundedMinutes % 60;

    if (minutes === 0) {
        return `${hours}H`;
    }

    return `${hours}H${String(minutes).padStart(2, '0')}`;
}
