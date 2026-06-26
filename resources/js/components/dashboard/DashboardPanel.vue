<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Card } from '@/components/ui/card';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <Card
        :class="
            cn(
                'gap-0 rounded-xl border border-[#2e2f30] bg-[#242526] py-0 text-slate-200 shadow-sm',
                props.class,
            )
        "
    >
        <slot />
    </Card>
</template>
