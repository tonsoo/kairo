<script setup lang="ts">
import { CalendarDays, ChevronLeft, ChevronRight, Download, List } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { HistoryView } from '@/lib/history';
import { i18n } from '@/lib/i18n';
import type { DashboardLocale } from '@/lib/i18n';

const props = defineProps<{
    locale: DashboardLocale;
    monthHeading: string;
    currentView: HistoryView;
    canGoToNextMonth: boolean;
    canExport: boolean;
}>();

const emit = defineEmits<{
    previous: [];
    next: [];
    export: [];
    'update:view': [view: HistoryView];
}>();
</script>

<template>
    <section class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-3">
            <div class="space-y-2">
                <h1 class="text-3xl font-semibold tracking-tight text-foreground">
                    {{ i18n.global.t('history.page.title') }}
                </h1>
                <p class="max-w-2xl text-sm leading-6 text-muted-foreground">
                    {{ i18n.global.t('history.page.description') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                <div class="flex items-center gap-2 rounded-full border border-border bg-card px-2 py-1">
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="rounded-full text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                        @click="emit('previous')"
                    >
                        <ChevronLeft class="size-4" />
                    </Button>
                    <span class="min-w-40 text-center font-medium text-foreground">
                        {{ props.monthHeading }}
                    </span>
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="rounded-full text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                        :disabled="! props.canGoToNextMonth"
                        @click="emit('next')"
                    >
                        <ChevronRight class="size-4" />
                    </Button>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <Button
                type="button"
                variant="ghost"
                class="rounded-full border border-border bg-background px-4 text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                @click="emit('export')"
            >
                <Download class="size-4" />
                {{ i18n.global.t('exports.button') }}
            </Button>

            <div class="inline-flex items-center gap-2 rounded-full border border-border bg-card p-1.5">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-colors"
                    :class="props.currentView === 'list'
                        ? 'bg-primary text-primary-foreground'
                        : 'text-muted-foreground hover:text-foreground'"
                    @click="emit('update:view', 'list')"
                >
                    <List class="size-4" />
                    <span>{{ i18n.global.t('history.view.list') }}</span>
                </button>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-colors"
                    :class="props.currentView === 'calendar'
                        ? 'bg-primary text-primary-foreground'
                        : 'text-muted-foreground hover:text-foreground'"
                    @click="emit('update:view', 'calendar')"
                >
                    <CalendarDays class="size-4" />
                    <span>{{ i18n.global.t('history.view.calendar') }}</span>
                </button>
            </div>
        </div>
    </section>
</template>
