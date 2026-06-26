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
import { dashboard } from '@/routes';

type SidebarItem = {
    title: string;
    icon: LucideIcon;
    href?: NonNullable<InertiaLinkProps['href']>;
    badge?: string;
};

const { isCurrentUrl } = useCurrentUrl();

const sections: Array<{ title: string; items: SidebarItem[] }> = [
    {
        title: 'Geral',
        items: [{ title: 'Dashboard', icon: LayoutGrid, href: dashboard() }],
    },
    {
        title: 'Em breve',
        items: [
            { title: 'Historico', icon: History, badge: 'Em breve' },
            { title: 'Escala semanal', icon: CalendarDays, badge: 'Em breve' },
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
                        v-if="item.href"
                        as-child
                        :is-active="isCurrentUrl(item.href)"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                    <div
                        v-else
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-400/80"
                    >
                        <component :is="item.icon" class="size-4" />
                        <span class="flex-1">{{ item.title }}</span>
                        <span
                            class="rounded-full border border-dashed border-slate-300 px-2 py-0.5 text-[10px] tracking-[0.16em] uppercase dark:border-white/10"
                        >
                            {{ item.badge }}
                        </span>
                    </div>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroup>
    </div>
</template>
