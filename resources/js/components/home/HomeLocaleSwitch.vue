<script setup lang="ts">
import { useLocaleSwitch } from '@/composables/useLocaleSwitch';
import { i18n } from '@/lib/i18n';

const { currentLocale, isSwitching, localeOptions, switchLocale } =
    useLocaleSwitch();
</script>

<template>
    <div
        :aria-label="i18n.global.t('locale.switch.aria')"
        class="inline-flex items-center gap-1 rounded-xl border border-[#2f3336] bg-[#17191b] p-1"
        role="group"
    >
        <button
            v-for="option in localeOptions"
            :key="option.code"
            type="button"
            :disabled="isSwitching"
            :aria-pressed="currentLocale === option.code"
            :class="[
                'rounded-lg border px-3 py-1.5 text-[11px] font-semibold tracking-[0.02em] transition-colors disabled:cursor-not-allowed disabled:opacity-70',
                currentLocale === option.code
                    ? 'border-teal-500/25 bg-teal-500/10 text-teal-300'
                    : 'border-transparent text-[#8fa29a] hover:border-[#2a2d30] hover:bg-[#1f2225] hover:text-slate-200',
            ]"
            @click="switchLocale(option)"
        >
            {{ option.code.toUpperCase() }}
        </button>
    </div>
</template>
