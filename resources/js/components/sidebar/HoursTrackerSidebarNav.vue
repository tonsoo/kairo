<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { CalendarDays, History, LayoutGrid } from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getAppLocale, translate } from '@/lib/translations';
import { dashboard, history, weeklySchedule } from '@/routes';

const locale = getAppLocale();

type SidebarItem = {
    title: string;
    icon: LucideIcon;
    href: NonNullable<InertiaLinkProps['href']>;
};

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
    <div class="space-y-4 px-2 py-1">
        <SidebarGroup
            v-for="section in sections"
            :key="section.title"
            class="px-0 py-0"
        >
            <SidebarGroupLabel
                class="px-2 text-[11px] tracking-[0.24em] uppercase"
                >{{ section.title }}</SidebarGroupLabel
            >
            <SidebarMenu>
                <SidebarMenuItem
                    v-for="item in section.items"
                    :key="item.title"
                >
                    <SidebarMenuButton
                        as-child
                        :is-active="isCurrentUrl(item.href)"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroup>
    </div>
</template>
