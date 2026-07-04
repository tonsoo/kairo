<script setup lang="ts">
import AppHead from '@/components/AppHead.vue';
import DashboardShiftAction from '@/components/dashboard-layout/DashboardShiftAction.vue';
import DashboardSidebarNav from '@/components/dashboard-layout/DashboardSidebarNav.vue';
import DashboardUserMenu from '@/components/dashboard-layout/DashboardUserMenu.vue';
import LocaleSwitch from '@/components/LocaleSwitch.vue';
</script>

<template>
    <AppHead />

    <div class="min-h-screen bg-background font-sans text-foreground">
        <div class="flex min-h-screen">
            <aside
                class="sticky top-0 hidden h-screen w-72 shrink-0 self-start overflow-hidden border-r border-border bg-card md:flex md:flex-col"
            >
                <div class="min-h-0 flex-1 overflow-y-auto px-4 py-6">
                    <DashboardSidebarNav />
                </div>

                <div class="space-y-4 border-t border-border px-4 py-4">
                    <DashboardShiftAction />
                    <DashboardUserMenu />
                </div>
            </aside>

            <main class="relative min-w-0 flex-1 bg-background">
                <div class="flex justify-end px-8 pt-8">
                    <LocaleSwitch compact />
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
