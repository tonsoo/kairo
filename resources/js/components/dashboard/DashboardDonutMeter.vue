<script setup lang="ts">
import { computed } from 'vue';
import type { DashboardMeterSegment } from '@/components/dashboard/dashboardData';

const props = withDefaults(
    defineProps<{
        segments: DashboardMeterSegment[];
        value: string;
        caption: string;
        size?: string;
    }>(),
    {
        size: 'h-44 w-44',
    },
);

const total = computed(() =>
    props.segments.reduce((sum, segment) => sum + segment.value, 0),
);

const meterStyle = computed(() => {
    if (total.value === 0) {
        return {
            backgroundImage: 'conic-gradient(rgba(71,85,105,0.65) 0deg 360deg)',
        };
    }

    let angle = 0;
    const stops = props.segments.map((segment) => {
        const start = angle;
        angle += (segment.value / total.value) * 360;

        return `${resolveColor(segment.colorClass)} ${start}deg ${angle}deg`;
    });

    return { backgroundImage: `conic-gradient(${stops.join(', ')})` };
});

function resolveColor(colorClass: string): string {
    const colorMap: Record<string, string> = {
        'bg-teal-500': '#0d9488',
        'bg-amber-500': '#eab308',
        'bg-rose-500': '#be123c',
        'bg-slate-500/70': '#475569',
    };

    return colorMap[colorClass] ?? '#64748b';
}
</script>

<template>
    <div
        :class="[
            'relative mx-auto grid place-items-center rounded-full p-4',
            size,
        ]"
        :style="meterStyle"
    >
        <div
            class="grid h-full w-full place-items-center rounded-full bg-[#242526] text-center"
        >
            <div class="space-y-1">
                <p class="text-3xl font-semibold text-slate-100">{{ value }}</p>
                <p
                    class="text-[11px] tracking-[0.24em] text-slate-500 uppercase"
                >
                    {{ caption }}
                </p>
            </div>
        </div>
    </div>
</template>
