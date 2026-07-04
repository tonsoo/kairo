<script setup lang="ts">
import { useLocaleSwitch } from '@/composables/useLocaleSwitch';
import { i18n } from '@/lib/i18n';

type Props = {
    compact?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    compact: false,
});

const { currentLocale, isSwitching, localeOptions, switchLocale } =
    useLocaleSwitch();
</script>

<template>
    <div
        :aria-label="i18n.global.t('locale.switch.aria')"
        class="inline-flex items-center gap-1 rounded-full border border-border bg-card/80 p-1"
        role="group"
    >
        <button
            v-for="option in localeOptions"
            :key="option.code"
            type="button"
            :disabled="isSwitching"
            :aria-pressed="currentLocale === option.code"
            :class="[
                'rounded-full border px-4 py-2 text-xs font-semibold tracking-[0.02em] transition-colors disabled:cursor-not-allowed disabled:opacity-70',
                props.compact ? 'px-3 py-1.5 text-[11px]' : '',
                currentLocale === option.code
                    ? 'border-teal-500/25 bg-teal-500/10 text-teal-700 dark:text-teal-300'
                    : 'border-transparent text-muted-foreground hover:border-border hover:bg-accent hover:text-accent-foreground',
            ]"
            @click="switchLocale(option)"
        >
            {{ option.code.toUpperCase() }}
        </button>
    </div>
</template>
