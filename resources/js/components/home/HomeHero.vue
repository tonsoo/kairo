<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import HomeHeroPreview from '@/components/home/HomeHeroPreview.vue';

defineProps<{
    primaryHref: string;
    secondaryHref: string;
    primaryLabel: string;
    secondaryLabel: string;
}>();
</script>

<template>
    <section class="relative overflow-hidden px-6 pb-32 pt-24">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-teal-900/20 via-[#1e1f20] to-[#1e1f20]" />

        <div class="mx-auto max-w-5xl text-center">
            <span class="mb-6 inline-block rounded-full border border-[#2e2f30] bg-[#242526] px-3 py-1 text-xs font-semibold tracking-wide text-teal-400 uppercase">
                Para profissionais independentes
            </span>

            <h1 class="mb-8 text-5xl font-extrabold leading-[1.1] tracking-tight text-white md:text-6xl lg:text-7xl">
                O controle definitivo <br class="hidden md:block" />
                da sua <span class="bg-gradient-to-r from-teal-400 to-emerald-300 bg-clip-text text-transparent">jornada de trabalho.</span>
            </h1>

            <p class="mx-auto mb-10 max-w-2xl text-lg leading-relaxed text-slate-400 md:text-xl">
                Sem planilhas complexas. Sem burocracia de RH corporativo. Um rastreador de horas calmo, preciso e privado, feito para quem gerencia o próprio tempo.
            </p>

            <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                <Link :href="primaryHref" class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-600 px-8 py-3 text-lg font-medium text-white shadow-[0_0_20px_rgba(13,148,136,0.2)] transition-all hover:bg-teal-500 hover:shadow-[0_0_30px_rgba(13,148,136,0.4)] sm:w-auto">
                    {{ primaryLabel }} <ArrowRight class="size-5" />
                </Link>
                <Link :href="secondaryHref" class="text-sm font-medium text-slate-500 transition-colors hover:text-slate-300 sm:text-base">
                    {{ secondaryLabel }}
                </Link>
            </div>

            <p class="mt-4 text-sm font-medium text-slate-500">100% Gratuito</p>
        </div>

        <div class="mx-auto mt-20 hidden max-w-5xl md:block">
            <HomeHeroPreview />
        </div>
    </section>
</template>
