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
                'gap-0 rounded-xl border border-border bg-card py-0 text-card-foreground shadow-sm',
                props.class,
            )
        "
    >
        <slot />
    </Card>
</template>
