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
import { i18n } from '@/lib/i18n';
import { dashboard, history, weeklySchedule } from '@/routes';

type SidebarItem = {
    title: string;
    icon: LucideIcon;
    href: NonNullable<InertiaLinkProps['href']>;
};

const { isCurrentUrl } = useCurrentUrl();

const sections: Array<{ title: string; items: SidebarItem[] }> = [
    {
        title: i18n.global.t('panel.section.general'),
        items: [
            {
                title: i18n.global.t('panel.item.dashboard'),
                icon: LayoutGrid,
                href: dashboard(),
            },
            {
                title: i18n.global.t('panel.item.history'),
                icon: History,
                href: history(),
            },
            {
                title: i18n.global.t('panel.item.weekly_schedule'),
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
