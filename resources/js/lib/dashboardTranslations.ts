export type DashboardLocale = 'en' | 'pt-BR';

const dashboardTranslations = {
    en: {
        'dashboard.hours.legend.worked': 'Worked',
        'dashboard.hours.legend.extra': 'Extra',
        'dashboard.hours.legend.missing': 'Missing',
        'dashboard.hours.legend.positive': 'Positive',
        'dashboard.hours.legend.negative': 'Negative',
        'dashboard.hours.legend.online': 'Online',
        'dashboard.hours.legend.paused': 'Paused',
        'dashboard.hours.balance.status.positive': 'Positive',
        'dashboard.hours.balance.status.negative': 'Negative',
        'dashboard.hours.balance.status.zero': 'Zero',
        'dashboard.hours.balance.title': 'Hours balance',
        'dashboard.hours.today.title': 'Today',
        'dashboard.hours.semester.title': 'Semester summary',
        'dashboard.hours.month.title': 'Hours balance',
        'dashboard.hours.month.view.summary': 'Summary',
        'dashboard.hours.month.view.journey': 'Journey',
        'dashboard.hours.loading': 'Loading dashboard data...',
        'dashboard.hours.error': 'Unable to load dashboard data.',
        'dashboard.shift.start': 'Start shift',
        'dashboard.shift.end': 'End shift',
        'dashboard.shift.continue': 'Continue shift',
        'dashboard.shift.loading': 'Loading shift...',
        'dashboard.shift.error': 'Unable to load shift state.',
    },
    'pt-BR': {
        'dashboard.hours.legend.worked': 'Trabalhadas',
        'dashboard.hours.legend.extra': 'Extras',
        'dashboard.hours.legend.missing': 'Faltando',
        'dashboard.hours.legend.positive': 'Positivo',
        'dashboard.hours.legend.negative': 'Negativo',
        'dashboard.hours.legend.online': 'Online',
        'dashboard.hours.legend.paused': 'Pausa',
        'dashboard.hours.balance.status.positive': 'Positivo',
        'dashboard.hours.balance.status.negative': 'Negativo',
        'dashboard.hours.balance.status.zero': 'Zerado',
        'dashboard.hours.balance.title': 'Banco de horas',
        'dashboard.hours.today.title': 'Hoje',
        'dashboard.hours.semester.title': 'Resumo do semestre',
        'dashboard.hours.month.title': 'Banco de horas',
        'dashboard.hours.month.view.summary': 'Resumo',
        'dashboard.hours.month.view.journey': 'Jornada',
        'dashboard.hours.loading': 'Carregando dados do dashboard...',
        'dashboard.hours.error': 'Nao foi possivel carregar os dados do dashboard.',
        'dashboard.shift.start': 'Iniciar turno',
        'dashboard.shift.end': 'Encerrar turno',
        'dashboard.shift.continue': 'Continuar turno',
        'dashboard.shift.loading': 'Carregando turno...',
        'dashboard.shift.error': 'Nao foi possivel carregar o estado do turno.',
    },
} as const;

export function getDashboardLocale(): DashboardLocale {
    if (typeof document === 'undefined') {
        return 'en';
    }

    const locale = document.documentElement.lang;

    if (locale.toLowerCase().startsWith('pt')) {
        return 'pt-BR';
    }

    return 'en';
}

export function translateDashboard(
    key: keyof (typeof dashboardTranslations)['en'] | string,
    locale: DashboardLocale = getDashboardLocale(),
): string {
    const translations = dashboardTranslations[locale] ?? dashboardTranslations.en;

    return translations[key as keyof typeof translations] ?? key;
}
