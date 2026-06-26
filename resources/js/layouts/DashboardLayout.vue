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
                class="sticky top-0 hidden h-screen w-72 shrink-0 self-start overflow-hidden border-r border-[#2e2f30] bg-[#18191a] md:flex md:flex-col"
            >
                <div class="min-h-0 flex-1 overflow-y-auto px-4 py-6">
                    <DashboardSidebarNav />
                </div>

                <div class="space-y-4 border-t border-[#2e2f30] px-4 py-4">
                    <DashboardShiftAction />
                    <DashboardUserMenu />
                </div>
            </aside>

            <main class="relative min-w-0 flex-1 bg-[#1e1f20]">
                <div class="flex justify-end px-8 pt-8">
                    <LocaleSwitch compact />
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
