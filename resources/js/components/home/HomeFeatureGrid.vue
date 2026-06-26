<script setup lang="ts">
import { CalendarDays, History, User } from '@lucide/vue';

const features = [
    {
        title: 'Rotinas Flexíveis',
        description: 'Configure horários fixos ou carga horária diária. Mude as regras para o futuro sem quebrar o histórico do passado.',
        icon: CalendarDays,
    },
    {
        title: 'Histórico Editável',
        description: 'Esqueceu de bater o ponto? O sistema permite correções retroativas no seu histórico de turnos de forma segura e imediata.',
        icon: History,
    },
    {
        title: 'Privacidade Total',
        description: 'Não somos um painel de monitoramento de gestores. Este é o seu espaço pessoal. Você é o único dono dos seus dados de jornada.',
        icon: User,
    },
];
</script>

<template>
    <section id="recursos" class="border-t border-[#2e2f30] bg-[#18191a] px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <div class="mb-16 text-center">
                <h2 class="mb-4 text-3xl font-bold text-white">Feito para a sua realidade</h2>
                <p class="mx-auto max-w-2xl text-slate-400">Não tentamos ser um sistema de gestão completo. Fazemos uma coisa: rastrear suas horas perfeitamente.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <article v-for="feature in features" :key="feature.title" class="flex h-full flex-col rounded-2xl border border-[#2e2f30] bg-[#1e1f20] p-8 transition-colors hover:border-slate-500">
                    <div class="mb-6 flex size-12 items-center justify-center rounded-xl border border-[#2e2f30] bg-[#242526] text-slate-300">
                        <component :is="feature.icon" class="size-6" />
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-slate-200">{{ feature.title }}</h3>
                    <p class="flex-1 leading-relaxed text-slate-400">{{ feature.description }}</p>
                </article>
            </div>
        </div>
    </section>
</template>
