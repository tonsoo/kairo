<script setup lang="ts">
import { Clock3 } from '@lucide/vue';

defineProps<{
    year: number;
}>();
</script>

<template>
    <footer id="privacidade" class="border-t border-[#2e2f30] bg-[#18191a] px-6 pb-8 pt-16">
        <div class="mx-auto mb-12 grid max-w-7xl grid-cols-1 gap-12 md:grid-cols-4">
            <div class="md:col-span-2">
                <div class="mb-4 flex items-center gap-2">
                    <Clock3 class="size-6 text-teal-600" />
                    <span class="text-xl font-bold text-slate-200">HoursTracker</span>
                </div>
                <p class="max-w-sm text-sm leading-relaxed text-slate-500">
                    Um rastreador de horas focado na pessoa, projetado para clareza, precisão e respeito pelo seu tempo e privacidade.
                </p>
            </div>
            <div>
                <h4 class="mb-4 font-semibold text-slate-200">Produto</h4>
                <ul class="space-y-3 text-sm text-slate-500">
                    <li><a href="#recursos" class="transition-colors hover:text-teal-400">Funcionalidades</a></li>
                    <li><a href="#privacidade" class="transition-colors hover:text-teal-400">Segurança</a></li>
                    <li><a href="#" class="transition-colors hover:text-teal-400">Preços (Grátis)</a></li>
                </ul>
            </div>
            <div>
                <h4 class="mb-4 font-semibold text-slate-200">Legal</h4>
                <ul class="space-y-3 text-sm text-slate-500">
                    <li><a href="#" class="transition-colors hover:text-teal-400">Termos de Serviço</a></li>
                    <li><a href="#" class="transition-colors hover:text-teal-400">Política de Privacidade</a></li>
                    <li><a href="#" class="transition-colors hover:text-teal-400">Contato</a></li>
                </ul>
            </div>
        </div>

        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 border-t border-[#2e2f30] pt-8 text-sm text-slate-500 md:flex-row">
            <p>&copy; {{ year }} HoursTracker. Todos os direitos reservados.</p>
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-2"><span class="size-2 rounded-full bg-emerald-500" /> Sistemas Operacionais</span>
            </div>
        </div>
    </footer>
</template>
