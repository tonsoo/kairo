<script setup lang="ts">
defineProps<{
    eyebrow?: string;
    title: string;
    description: string;
    reverse?: boolean;
}>();
</script>

<template>
    <section class="border-t border-[#2e2f30] px-6 py-24" :id="eyebrow === 'Como funciona' ? 'como-funciona' : undefined">
        <div class="mx-auto grid w-full max-w-6xl gap-14 lg:grid-cols-2 lg:items-center" :class="reverse ? 'lg:[&>*:first-child]:order-2 lg:[&>*:last-child]:order-1' : ''">
            <div class="space-y-6">
                <p v-if="eyebrow" class="text-xs font-semibold tracking-[0.18em] text-teal-300 uppercase">{{ eyebrow }}</p>
                <h2 class="text-3xl font-bold tracking-[-0.03em] text-white md:text-4xl">{{ title }}</h2>
                <p class="text-lg leading-8 text-slate-400">{{ description }}</p>
                <div class="space-y-4 text-slate-300">
                    <slot name="details" />
                </div>
            </div>
            <div>
                <slot name="visual" />
            </div>
        </div>
    </section>
</template>
