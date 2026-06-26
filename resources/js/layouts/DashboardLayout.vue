<script setup lang="ts">
import DashboardShiftAction from '@/components/dashboard-layout/DashboardShiftAction.vue';
import DashboardSidebarNav from '@/components/dashboard-layout/DashboardSidebarNav.vue';
import DashboardUserMenu from '@/components/dashboard-layout/DashboardUserMenu.vue';
import LocaleSwitch from '@/components/LocaleSwitch.vue';
</script>

<template>
    <div class="min-h-screen bg-[#1e1f20] font-sans text-slate-300">
        <div class="flex min-h-screen">
            <aside
                class="hidden w-72 shrink-0 border-r border-[#2e2f30] bg-[#18191a] md:flex md:flex-col"
            >
                <div class="flex-1 px-4 py-6">
                    <DashboardSidebarNav />
                </div>

                <div class="space-y-4 px-4 pb-4">
                    <DashboardShiftAction />
                    <DashboardUserMenu />
                </div>
            </aside>

            <main class="relative min-w-0 flex-1 bg-[#1e1f20]">
                <div class="pt-8 px-8 flex justify-end">
                    <LocaleSwitch compact />
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
