<script setup lang="ts">
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { i18n } from '@/lib/i18n';

const value = defineModel<string>({ required: true });

const props = defineProps<{
    id?: string;
    options: string[];
}>();
</script>

<template>
    <Select v-model="value">
        <SelectTrigger
            :id="props.id"
            class="h-11 w-full rounded-xl border-[#3a3b3c] bg-[#18191a] px-3 text-sm text-slate-100"
        >
            <SelectValue :placeholder="i18n.global.t('settings.app.timezone_placeholder')" />
        </SelectTrigger>
        <SelectContent class="max-h-80 border-[#2e2f30] bg-[#18191a] text-slate-100">
            <SelectItem
                v-for="timezoneOption in props.options"
                :key="timezoneOption"
                :value="timezoneOption"
                class="focus:bg-white/8 focus:text-slate-100"
            >
                {{ timezoneOption }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
