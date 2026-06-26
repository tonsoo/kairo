<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    balanceSegments,
    chartLegend,
    monthItems,
    semesterItems,
    todayLegend,
    todaySegments,
} from '@/components/dashboard/dashboardData';
import DashboardMetricCard from '@/components/dashboard/DashboardMetricCard.vue';
import DashboardMonthCard from '@/components/dashboard/DashboardMonthCard.vue';
import DashboardSemesterCard from '@/components/dashboard/DashboardSemesterCard.vue';
</script>

<template>
    <Head title="Dashboard" />

    <div class="px-8 py-8">
        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="space-y-6 lg:col-span-3">
                    <DashboardMetricCard
                        title="Banco de horas:"
                        highlight="01:13"
                        meter-value="75%"
                        meter-caption="positivo"
                        :segments="balanceSegments"
                        footer-text="Detalhar"
                        show-info
                    />
                    <DashboardMetricCard
                        title="Hoje |"
                        highlight="04:44"
                        highlight-class="text-slate-100"
                        meter-value="04:44"
                        meter-caption="tempo trabalhado"
                        :segments="todaySegments"
                        :legend="todayLegend"
                    />
                </div>

                <DashboardSemesterCard
                    :items="semesterItems"
                    :legend="chartLegend"
                    class="lg:col-span-9"
                />
            </div>

            <DashboardMonthCard :items="monthItems" :legend="chartLegend" />
        </div>
    </div>
</template>
