<script setup lang="ts">
import { ArrowRight } from '@lucide/vue';
</script>

<template>
    <section class="border-t border-[#2e2f30] px-6 py-24">
        <div class="mx-auto grid max-w-6xl grid-cols-1 items-center gap-16 md:grid-cols-2">
            <div class="order-2 rounded-2xl border border-[#2e2f30] bg-[#242526] p-8 shadow-xl md:order-1">
                <div class="space-y-4">
                    <div class="flex items-center justify-between rounded-lg border border-[#2e2f30] bg-[#1e1f20] p-3">
                        <span class="font-medium text-slate-300">Fuso Origem</span>
                        <span class="rounded bg-[#334155] px-2 py-1 text-sm text-white">UTC-3 (Brasília)</span>
                    </div>
                    <div class="my-2 flex justify-center text-slate-500">
                        <ArrowRight class="size-5" />
                    </div>
                    <div class="flex items-center justify-between rounded-lg border border-teal-500/30 bg-[#1e1f20] p-3 ring-1 ring-teal-500/20">
                        <span class="font-medium text-teal-400">Seu Fuso Local</span>
                        <span class="rounded bg-teal-500/20 px-2 py-1 text-sm text-teal-400">UTC+1 (Lisboa)</span>
                    </div>
                    <p class="pt-4 text-center text-xs text-slate-500">O histórico se adapta automaticamente a você.</p>
                </div>
            </div>

            <div class="order-1 md:order-2">
                <h2 class="mb-6 text-3xl font-bold text-white">Fuso horário inteligente e nativo.</h2>
                <p class="mb-6 text-lg leading-relaxed text-slate-400">
                    Trabalha remotamente? Viaja com frequência? O sistema trata fusos horários como cidadãos de primeira classe. Seu histórico é salvo com precisão e sempre exibido na sua hora local.
                </p>
                <p class="text-lg leading-relaxed text-slate-400">
                    O sistema nunca parece &quot;preso&quot; no servidor. Ele entende onde você está hoje e ajusta suas jornadas passadas para fazerem sentido.
                </p>
            </div>
        </div>
    </section>
</template>
