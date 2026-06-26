export type DashboardLegendItem = {
    label: string;
    colorClass: string;
};

export type DashboardMeterSegment = DashboardLegendItem & {
    value: number;
};

export type DashboardBarItem = {
    label: string;
    worked: number;
    extra: number;
    missing: number;
};

export const balanceSegments: DashboardMeterSegment[] = [
    { label: 'Positivo', value: 75, colorClass: 'bg-teal-500' },
    { label: 'Restante', value: 25, colorClass: 'bg-slate-500/70' },
];

export const todaySegments: DashboardMeterSegment[] = [
    { label: 'Online', value: 284, colorClass: 'bg-teal-500' },
    { label: 'Pausa', value: 28, colorClass: 'bg-amber-500' },
    { label: 'Faltando', value: 168, colorClass: 'bg-slate-500/70' },
];

export const todayLegend: DashboardLegendItem[] = todaySegments.map(
    ({ label, colorClass }) => ({
        label,
        colorClass,
    }),
);

export const chartLegend: DashboardLegendItem[] = [
    { label: 'Trabalhadas', colorClass: 'bg-teal-500' },
    { label: 'Extras', colorClass: 'bg-rose-500' },
    { label: 'Faltando', colorClass: 'bg-slate-500/70' },
];

export const semesterItems: DashboardBarItem[] = [
    { label: 'Jan/26', worked: 160, extra: 0, missing: 0 },
    { label: 'Fev/26', worked: 165, extra: 5, missing: 0 },
    { label: 'Mar/26', worked: 170, extra: 10, missing: 0 },
    { label: 'Abr/26', worked: 155, extra: 0, missing: 5 },
    { label: 'Mai/26', worked: 40, extra: 0, missing: 120 },
    { label: 'Jun/26', worked: 0, extra: 0, missing: 0 },
];

export const monthItems: DashboardBarItem[] = [
    { label: '1', worked: 8, extra: 0, missing: 0 },
    { label: '2', worked: 8, extra: 1, missing: 0 },
    { label: '3', worked: 7, extra: 0, missing: 1 },
    { label: '4', worked: 8, extra: 0, missing: 0 },
    { label: '5', worked: 8, extra: 0, missing: 0 },
    { label: '6', worked: 7, extra: 0, missing: 1 },
    { label: '7', worked: 8, extra: 0, missing: 0 },
    { label: '8', worked: 8, extra: 0, missing: 0 },
    { label: '9', worked: 8, extra: 1, missing: 0 },
    { label: '10', worked: 7, extra: 0, missing: 1 },
    { label: '11', worked: 8, extra: 0, missing: 0 },
    { label: '12', worked: 8, extra: 0, missing: 0 },
    { label: '13', worked: 7, extra: 0, missing: 1 },
    { label: '14', worked: 8, extra: 0, missing: 0 },
    { label: '15', worked: 8, extra: 1, missing: 0 },
    { label: '16', worked: 8, extra: 0, missing: 0 },
    { label: '17', worked: 8, extra: 0, missing: 0 },
    { label: '18', worked: 7, extra: 0, missing: 1 },
    { label: '19', worked: 8, extra: 0, missing: 0 },
    { label: '20', worked: 8, extra: 1, missing: 0 },
    { label: '21', worked: 7, extra: 0, missing: 1 },
    { label: '22', worked: 8, extra: 0, missing: 0 },
    { label: '23', worked: 8, extra: 0, missing: 0 },
    { label: '24', worked: 8, extra: 1, missing: 0 },
    { label: '25', worked: 7, extra: 0, missing: 1 },
    { label: '26', worked: 8, extra: 0, missing: 0 },
    { label: '27', worked: 8, extra: 0, missing: 0 },
    { label: '28', worked: 8, extra: 1, missing: 0 },
    { label: '29', worked: 7, extra: 0, missing: 1 },
    { label: '30', worked: 8, extra: 0, missing: 0 },
];
