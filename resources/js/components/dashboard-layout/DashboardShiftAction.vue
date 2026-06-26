<script setup lang="ts">
import { Clock3, Play } from '@lucide/vue';
</script>

<template>
    <button
        type="button"
        class="flex w-full items-center justify-center gap-2 rounded-md border border-teal-400/20 bg-teal-500/10 px-4 py-2.5 text-sm font-medium text-teal-300 transition hover:bg-teal-500/20"
    >
        <Play class="size-4 fill-current" />
        <span>Iniciar turno</span>
    </button>
</template>
