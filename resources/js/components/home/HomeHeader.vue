<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Clock3 } from '@lucide/vue';

defineProps<{
    dashboardHref: string;
    loginHref: string;
    registerHref: string;
    isAuthenticated: boolean;
}>();
</script>

<template>
    <header class="sticky top-0 z-50 w-full border-b border-[#2e2f30] bg-[#1e1f20]/80 backdrop-blur-md">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
            <div class="flex items-center gap-2">
                <Clock3 class="size-6 text-teal-500" />
                <span class="text-xl font-bold tracking-tight text-slate-200">HoursTracker</span>
            </div>

            <nav aria-label="Navegação principal" class="hidden items-center gap-8 text-sm font-medium text-slate-400 md:flex">
                <a href="#como-funciona" class="transition-colors hover:text-teal-400">Como funciona</a>
                <a href="#recursos" class="transition-colors hover:text-teal-400">Recursos</a>
                <a href="#privacidade" class="transition-colors hover:text-teal-400">Privacidade</a>
            </nav>

            <div class="flex items-center gap-4">
                <Link :href="isAuthenticated ? dashboardHref : loginHref" class="text-sm font-medium text-slate-300 transition-colors hover:text-white">
                    {{ isAuthenticated ? 'Painel' : 'Entrar' }}
                </Link>
                <Link :href="isAuthenticated ? dashboardHref : registerHref" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-teal-500">
                    {{ isAuthenticated ? 'Acessar painel' : 'Criar Conta' }}
                </Link>
            </div>
        </div>
    </header>
</template>
