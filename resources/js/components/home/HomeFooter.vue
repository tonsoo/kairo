<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { i18n } from '@/lib/i18n';

defineProps<{
    year: number;
}>();
</script>

<template>
    <footer
        id="privacidade"
        class="border-t border-[#2e2f30] bg-[#18191a] px-6 pt-16 pb-8"
    >
        <div
            class="mx-auto mb-12 grid max-w-7xl grid-cols-1 gap-12 md:grid-cols-3"
        >
            <div class="max-w-sm md:col-span-2">
                <div class="mb-4 flex items-center gap-3">
                    <AppLogoIcon class="size-8 shrink-0" />
                    <h3 class="text-xl font-bold text-slate-200">Kairo</h3>
                </div>
                <p class="max-w-sm text-sm leading-relaxed text-slate-500">
                    {{ i18n.global.t('home.footer.description') }}
                </p>
            </div>
            <div>
                <h4 class="mb-4 font-semibold text-slate-200">
                    {{ i18n.global.t('home.footer.product') }}
                </h4>
                <ul class="space-y-3 text-sm text-slate-500">
                    <li>
                        <a
                            href="#recursos"
                            class="transition-colors hover:text-teal-400"
                            >{{
                                i18n.global.t('home.footer.product.features')
                            }}</a
                        >
                    </li>
                    <li>
                        <a
                            href="#como-funciona"
                            class="transition-colors hover:text-teal-400"
                            >{{
                                i18n.global.t(
                                    'home.footer.product.how-it-works',
                                )
                            }}</a
                        >
                    </li>
                </ul>
            </div>
        </div>

        <div
            class="mx-auto max-w-7xl border-t border-[#2e2f30] pt-8 text-center text-sm text-slate-500"
        >
            <p>
                &copy; {{ year }} Kairo.
                {{ i18n.global.t('home.footer.rights') }}
            </p>
        </div>
    </footer>
</template>
