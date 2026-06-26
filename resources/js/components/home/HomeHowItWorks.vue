<script setup lang="ts">
import { Check, Play } from '@lucide/vue';
</script>

<template>
    <section id="como-funciona" class="border-t border-[#2e2f30] bg-[#18191a] px-6 py-24">
        <div class="mx-auto grid max-w-6xl grid-cols-1 items-center gap-16 md:grid-cols-2">
            <div>
                <h2 class="mb-6 text-3xl font-bold text-white">Acompanhe seu saldo em tempo real.</h2>
                <p class="mb-6 text-lg leading-relaxed text-slate-400">
                    Abra o aplicativo e entenda seu status no mesmo segundo. Nosso painel converte suas batidas de ponto em informações claras: horas trabalhadas hoje, saldo acumulado do mês e histórico semestral.
                </p>
                <ul class="space-y-4 text-slate-300">
                    <li class="flex items-start gap-3">
                        <div class="mt-1 flex size-5 shrink-0 items-center justify-center rounded bg-teal-500/20 text-teal-400">
                            <Check class="size-3.5" />
                        </div>
                        <span>Saiba exatamente se você está adiantado ou atrasado.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="mt-1 flex size-5 shrink-0 items-center justify-center rounded bg-teal-500/20 text-teal-400">
                            <Check class="size-3.5" />
                        </div>
                        <span>Uma única ação central: iniciar, pausar ou continuar.</span>
                    </li>
                </ul>
            </div>

            <div class="rounded-2xl border border-[#2e2f30] bg-[#242526] p-8 shadow-xl">
                <div class="space-y-6">
                    <div class="flex items-center justify-between border-b border-[#2e2f30] pb-4">
                        <div>
                            <p class="text-sm font-semibold tracking-wide text-slate-500 uppercase">Banco de horas</p>
                            <p class="mt-1 text-3xl font-bold text-white">01:13 <span class="ml-2 text-sm font-normal text-teal-400">Positivo</span></p>
                        </div>
                        <div class="size-16 rounded-full border-[6px] border-[#334155] border-t-teal-500 border-r-teal-500" />
                    </div>

                    <div class="flex items-center gap-4 rounded-lg border border-[#2e2f30] bg-[#1e1f20] p-4">
                        <div class="w-10 h-10 rounded-full bg-teal-500/10 text-teal-400 flex items-center justify-center">
                            <Play size="18" fill="currentColor" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">Pronto para iniciar</p>
                            <p class="text-xs text-slate-400">Nenhum turno ativo hoje</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
