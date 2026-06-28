<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import logoPath from '../../images/logo.svg';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        :src="logoPath"
        alt="Kairo logo"
        :class="className"
        draggable="false"
        v-bind="$attrs"
    />
</template>
