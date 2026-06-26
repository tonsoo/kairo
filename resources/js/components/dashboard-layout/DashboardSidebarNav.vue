<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { CalendarDays, History, LayoutGrid } from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard, weeklySchedule } from '@/routes';

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
        title: 'Horas',
        items: [
            { title: 'Historico', icon: History, badge: 'Em breve' },
            { title: 'Escala semanal', icon: CalendarDays, href: weeklySchedule() },
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
                <template v-for="item in section.items" :key="item.title">
                    <Link
                        v-if="item.href"
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

                    <div
                        v-else
                        class="flex items-center gap-3 rounded-lg border border-transparent px-4 py-3 text-sm text-slate-500"
                    >
                        <component :is="item.icon" class="size-4" />
                        <span class="flex-1">{{ item.title }}</span>
                        <span
                            class="rounded-full border border-[#343538] px-2 py-0.5 text-[10px] tracking-[0.16em] text-slate-400 uppercase"
                        >
                            {{ item.badge }}
                        </span>
                    </div>
                </template>
            </div>
        </section>
    </div>
</template>
