<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ChevronsUpDown } from '@lucide/vue';
import { computed } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import type { User } from '@/types';

const user = computed(() => usePage().props.auth.user as User);
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <button
                type="button"
                class="flex w-full items-center gap-3 rounded-xl border border-border bg-card px-3 py-2 text-left transition hover:bg-accent"
            >
                <UserInfo :user="user" />
                <ChevronsUpDown class="ml-auto size-4 text-muted-foreground" />
            </button>
        </DropdownMenuTrigger>

        <DropdownMenuContent
            align="start"
            side="top"
            class="w-64 rounded-xl border-border bg-popover p-1 text-popover-foreground shadow-xl"
        >
            <UserMenuContent :user="user" />
        </DropdownMenuContent>
    </DropdownMenu>
</template>
