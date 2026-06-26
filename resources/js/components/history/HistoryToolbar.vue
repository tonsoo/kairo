<script setup lang="ts">
import { CalendarDays, ChevronLeft, ChevronRight, Download, List } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { translateDashboard } from '@/lib/dashboardTranslations';
import type { DashboardLocale } from '@/lib/dashboardTranslations';
import type { HistoryView } from '@/lib/history';

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
                <h1 class="text-3xl font-semibold tracking-tight text-slate-50">
                    {{ translateDashboard('history.page.title', props.locale) }}
                </h1>
                <p class="max-w-2xl text-sm leading-6 text-slate-400">
                    {{ translateDashboard('history.page.description', props.locale) }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 text-sm text-slate-300">
                <div class="flex items-center gap-2 rounded-full border border-[#313234] bg-[#18191a] px-2 py-1">
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="rounded-full text-slate-300 hover:bg-[#242526] hover:text-slate-100"
                        @click="emit('previous')"
                    >
                        <ChevronLeft class="size-4" />
                    </Button>
                    <span class="min-w-40 text-center font-medium text-slate-100">
                        {{ props.monthHeading }}
                    </span>
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="rounded-full text-slate-300 hover:bg-[#242526] hover:text-slate-100"
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
                class="rounded-full border border-[#313234] bg-[#18191a] px-4 text-slate-300 hover:bg-[#242526] hover:text-slate-100"
                @click="emit('export')"
            >
                <Download class="size-4" />
                {{ translateDashboard('exports.button', props.locale) }}
            </Button>

            <div class="inline-flex items-center gap-2 rounded-full border border-[#313234] bg-[#18191a] p-1.5">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-colors"
                    :class="props.currentView === 'list'
                        ? 'bg-[#d0ebba] text-[#17230f]'
                        : 'text-slate-400 hover:text-slate-100'"
                    @click="emit('update:view', 'list')"
                >
                    <List class="size-4" />
                    <span>{{ translateDashboard('history.view.list', props.locale) }}</span>
                </button>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-colors"
                    :class="props.currentView === 'calendar'
                        ? 'bg-[#d0ebba] text-[#17230f]'
                        : 'text-slate-400 hover:text-slate-100'"
                    @click="emit('update:view', 'calendar')"
                >
                    <CalendarDays class="size-4" />
                    <span>{{ translateDashboard('history.view.calendar', props.locale) }}</span>
                </button>
            </div>
        </div>
    </section>
</template>
