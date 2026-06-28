<script setup lang="ts">
import { computed } from 'vue';
import type { DashboardJourneyItem } from '@/components/dashboard/dashboardData';

const props = defineProps<{
    items: DashboardJourneyItem[];
}>();

const hourLabels = computed(() =>
    Array.from({ length: 25 }, (_, index) => 24 - index),
);

function getSegmentStyle(
    item: DashboardJourneyItem,
    index: number,
): { bottom: string; height: string } {
    const segment = item.segments[index];

    if (segment === undefined) {
        return {
            bottom: '0%',
            height: '0%',
        };
    }

    const previousSegment = item.segments[index - 1];
    const nextSegment = item.segments[index + 1];
    const hasBreakBelow = previousSegment !== undefined && previousSegment.endHour < segment.startHour;
    const hasBreakAbove = nextSegment !== undefined && segment.endHour < nextSegment.startHour;
    const bottomPercent = (segment.startHour / 24) * 100;
    const heightPercent = Math.max(((segment.endHour - segment.startHour) / 24) * 100, 0.35);
    const gapAdjustment = Number(hasBreakBelow) + Number(hasBreakAbove);

    return {
        bottom: hasBreakBelow ? `calc(${bottomPercent}% + 1px)` : `${bottomPercent}%`,
        height: gapAdjustment > 0
            ? `calc(${heightPercent}% - ${gapAdjustment}px)`
            : `${heightPercent}%`,
    };
}
</script>

<template>
    <div class="grid h-full grid-cols-[auto_minmax(0,1fr)] gap-4">
        <div
            class="flex h-full flex-col justify-between text-[11px] text-muted-foreground"
        >
            <span v-for="hour in hourLabels" :key="hour">{{ hour }}H</span>
        </div>

        <div class="grid min-w-0 grid-rows-[minmax(0,1fr)_auto] gap-3">
            <div class="relative">
                <div
                    class="pointer-events-none absolute inset-0 flex flex-col justify-between"
                >
                    <div
                        v-for="hour in hourLabels"
                        :key="`line-${hour}`"
                        class="border-t border-dashed border-border/70"
                    />
                </div>

                <div
                    class="absolute inset-0 grid"
                    :style="{ gridTemplateColumns: `repeat(${props.items.length}, minmax(0, 1fr))` }"
                >
                    <div
                        v-for="item in props.items"
                        :key="`grid-${item.label}`"
                        class="border-r border-border/60 last:border-r-0"
                    />
                </div>

                <div
                    class="relative grid h-full"
                    :style="{ gridTemplateColumns: `repeat(${props.items.length}, minmax(0, 1fr))` }"
                >
                    <div
                        v-for="item in props.items"
                        :key="item.label"
                        class="relative h-full"
                    >
                        <div
                            v-for="(segment, index) in item.segments"
                            :key="`${item.label}-${index}`"
                            :class="[
                                'absolute left-1/2 w-[74%] -translate-x-1/2 rounded-sm',
                                segment.colorClass,
                            ]"
                            :style="getSegmentStyle(item, index)"
                        />
                    </div>
                </div>
            </div>

            <div
                class="grid text-[10px] text-muted-foreground"
                :style="{ gridTemplateColumns: `repeat(${props.items.length}, minmax(0, 1fr))` }"
            >
                <span
                    v-for="item in props.items"
                    :key="`label-${item.label}`"
                    class="text-center"
                >
                    {{ item.label }}
                </span>
            </div>
        </div>
    </div>
</template>
