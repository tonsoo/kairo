<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { CalendarDays, History, LayoutGrid } from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getAppLocale, translate } from '@/lib/translations';
import { dashboard, history, weeklySchedule } from '@/routes';

type SidebarItem = {
    title: string;
    icon: LucideIcon;
    href: NonNullable<InertiaLinkProps['href']>;
};

const locale = getAppLocale();
const { isCurrentUrl } = useCurrentUrl();

const sections: Array<{ title: string; items: SidebarItem[] }> = [
    {
        title: translate('panel.section.general', locale),
        items: [
            {
                title: translate('panel.item.dashboard', locale),
                icon: LayoutGrid,
                href: dashboard(),
            },
        ],
    },
    {
        title: translate('panel.section.hours', locale),
        items: [
            {
                title: translate('panel.item.history', locale),
                icon: History,
                href: history(),
            },
            {
                title: translate('panel.item.weekly_schedule', locale),
                icon: CalendarDays,
                href: weeklySchedule(),
            },
        ],
    },
];
</script>

<template>
    <div class="space-y-8">
        <section
            v-for="section in sections"
            :key="section.title"
            class="space-y-2"
        >
            <p
                class="px-2 text-[11px] font-semibold tracking-[0.28em] text-slate-500 uppercase"
            >
                {{ section.title }}
            </p>

            <div class="space-y-1">
                <Link
                    v-for="item in section.items"
                    :key="item.title"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 rounded-lg border px-4 py-3 text-sm font-medium transition-all',
                        isCurrentUrl(item.href)
                            ? 'border-teal-500/25 bg-teal-500/10 text-teal-300'
                            : 'border-transparent text-slate-400 hover:border-[#343538] hover:bg-[#222325] hover:text-slate-200',
                    ]"
                >
                    <component :is="item.icon" class="size-4" />
                    <span class="flex-1">{{ item.title }}</span>
                </Link>
            </div>
        </section>
    </div>
</template>
