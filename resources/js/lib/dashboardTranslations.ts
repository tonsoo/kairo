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
        'weekly_schedule.nav': 'Weekly schedule',
        'weekly_schedule.title': 'Weekly schedule',
        'weekly_schedule.description': 'Edit every day of the week, define goal hours or a fixed time range, and mark days off when needed.',
        'weekly_schedule.effective_from': 'Effective from',
        'weekly_schedule.timezone': 'Timezone',
        'weekly_schedule.loading': 'Loading weekly schedule...',
        'weekly_schedule.load_error': 'Unable to load the weekly schedule.',
        'weekly_schedule.save': 'Save schedule',
        'weekly_schedule.saving': 'Saving...',
        'weekly_schedule.saved': 'Saved',
        'weekly_schedule.save_error': 'Unable to save the weekly schedule.',
        'weekly_schedule.day.weekday': 'Weekday',
        'weekly_schedule.day.weekend': 'Weekend',
        'weekly_schedule.day_off_hint': 'This day will be treated as a day off.',
        'weekly_schedule.type.day_off': 'Day off',
        'weekly_schedule.type.total_time': 'Total time',
        'weekly_schedule.type.time_range': 'Time range',
        'weekly_schedule.field.day_type': 'Day type',
        'weekly_schedule.field.goal_hours': 'Goal hours',
        'weekly_schedule.field.goal_hours_hint': 'Select hours and minutes separately.',
        'weekly_schedule.field.goal_hours_hours': 'Hours',
        'weekly_schedule.field.goal_hours_minutes': 'Minutes',
        'weekly_schedule.field.starts_at': 'Starts at',
        'weekly_schedule.field.ends_at': 'Ends at',
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
        'weekly_schedule.nav': 'Escala semanal',
        'weekly_schedule.title': 'Escala semanal',
        'weekly_schedule.description': 'Edite todos os dias da semana, defina meta de horas ou horario fixo e marque folgas quando precisar.',
        'weekly_schedule.effective_from': 'Vigencia',
        'weekly_schedule.timezone': 'Timezone',
        'weekly_schedule.loading': 'Carregando escala semanal...',
        'weekly_schedule.load_error': 'Nao foi possivel carregar a escala semanal.',
        'weekly_schedule.save': 'Salvar escala',
        'weekly_schedule.saving': 'Salvando...',
        'weekly_schedule.saved': 'Salvo',
        'weekly_schedule.save_error': 'Nao foi possivel salvar a escala semanal.',
        'weekly_schedule.day.weekday': 'Dia util',
        'weekly_schedule.day.weekend': 'Fim de semana',
        'weekly_schedule.day_off_hint': 'Este dia sera tratado como folga.',
        'weekly_schedule.type.day_off': 'Folga',
        'weekly_schedule.type.total_time': 'Carga horaria',
        'weekly_schedule.type.time_range': 'Horario fixo',
        'weekly_schedule.field.day_type': 'Tipo do dia',
        'weekly_schedule.field.goal_hours': 'Meta de horas',
        'weekly_schedule.field.goal_hours_hint': 'Selecione horas e minutos separadamente.',
        'weekly_schedule.field.goal_hours_hours': 'Horas',
        'weekly_schedule.field.goal_hours_minutes': 'Minutos',
        'weekly_schedule.field.starts_at': 'Entrada',
        'weekly_schedule.field.ends_at': 'Saida',
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
