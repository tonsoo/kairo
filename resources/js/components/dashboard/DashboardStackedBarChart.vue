<script setup lang="ts">
import { computed } from 'vue';
import type { DashboardBarItem } from '@/components/dashboard/dashboardData';

const props = withDefaults(
    defineProps<{
        items: DashboardBarItem[];
        compact?: boolean;
        stepCount?: number;
    }>(),
    {
        compact: false,
        stepCount: 4,
    },
);

const maxValue = computed(() =>
    Math.max(
        ...props.items.map((item) => item.worked + item.extra + item.missing),
        1,
    ),
);
const steps = computed(() =>
    Array.from({ length: props.stepCount + 1 }, (_, index) =>
        Math.round(
            (maxValue.value * (props.stepCount - index)) / props.stepCount,
        ),
    ),
);

function toPercent(value: number): string {
    return `${Math.max((value / maxValue.value) * 100, value > 0 ? 4 : 0)}%`;
}
</script>

<template>
    <div class="grid h-full grid-cols-[auto_minmax(0,1fr)] gap-4">
        <div
            class="flex h-full flex-col justify-between text-[11px] text-slate-500"
        >
            <span v-for="step in steps" :key="step">{{ step }}H</span>
        </div>

        <div class="relative">
            <div
                class="pointer-events-none absolute inset-0 flex flex-col justify-between"
            >
                <div
                    v-for="step in steps"
                    :key="`line-${step}`"
                    class="border-t border-dashed border-[#334155]"
                />
            </div>

            <div
                :class="[
                    'relative flex h-full items-end',
                    compact ? 'gap-1' : 'gap-3',
                ]"
            >
                <div
                    v-for="item in items"
                    :key="item.label"
                    class="flex min-w-0 flex-1 flex-col items-center gap-3"
                >
                    <div
                        :class="[
                            'flex h-full w-full flex-col justify-end overflow-hidden rounded-t-md bg-[#18191a]',
                            compact ? 'max-w-3' : 'max-w-8',
                        ]"
                    >
                        <div
                            class="bg-slate-600"
                            :style="{ height: toPercent(item.missing) }"
                        />
                        <div
                            class="bg-rose-700"
                            :style="{ height: toPercent(item.extra) }"
                        />
                        <div
                            class="bg-teal-600"
                            :style="{ height: toPercent(item.worked) }"
                        />
                    </div>
                    <span
                        :class="[
                            'text-slate-500',
                            compact ? 'text-[10px]' : 'text-xs',
                        ]"
                        >{{ item.label }}</span
                    >
                </div>
            </div>
        </div>
    </div>
</template>
