<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { AppLocale } from '@/lib/translations';
import {
    getAppLocale,
    localeSwitchOptions,
    translate,
} from '@/lib/translations';
import { update as updateLocale } from '@/routes/locale';

type Props = {
    compact?: boolean;
};

withDefaults(defineProps<Props>(), {
    compact: false,
});

const currentLocale = ref<AppLocale>(getAppLocale());
const isSwitching = ref(false);

function switchLocale(nextLocale: AppLocale): void {
    if (nextLocale === currentLocale.value || isSwitching.value) {
        return;
    }

    isSwitching.value = true;

    router.visit(updateLocale(nextLocale), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            window.location.reload();
        },
        onFinish: () => {
            isSwitching.value = false;
        },
    });
}
</script>

<template>
    <div
        :aria-label="translate('locale.switch.aria')"
        class="inline-flex items-center gap-1 rounded-full border border-[#7fa08d] bg-[#1f2822] p-1"
        role="group"
    >
        <button
            v-for="option in localeSwitchOptions"
            :key="option.value"
            type="button"
            :disabled="isSwitching"
            :aria-pressed="currentLocale === option.value"
            :class="[
                'rounded-full px-4 py-2 text-xs font-semibold tracking-[0.02em] transition-colors disabled:cursor-not-allowed disabled:opacity-70',
                compact ? 'px-3 py-1.5 text-[11px]' : '',
                currentLocale === option.value
                    ? 'bg-[#d4f0b5] text-[#0f1b12]'
                    : 'text-[#9fc1a8] hover:bg-[#2b382f] hover:text-[#d4f0b5]',
            ]"
            @click="switchLocale(option.value)"
        >
            {{ option.label }}
        </button>
    </div>
</template>
