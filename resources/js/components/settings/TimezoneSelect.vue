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
            class="h-11 w-full rounded-xl px-3 text-sm"
        >
            <SelectValue :placeholder="i18n.global.t('settings.app.timezone_placeholder')" />
        </SelectTrigger>
        <SelectContent class="max-h-80 rounded-xl">
            <SelectItem
                v-for="timezoneOption in props.options"
                :key="timezoneOption"
                :value="timezoneOption"

            >
                {{ timezoneOption }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
