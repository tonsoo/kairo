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
                class="flex w-full items-center gap-3 rounded-xl border border-[#2e2f30] bg-[#242526] px-3 py-2 text-left transition hover:bg-[#2a2b2d]"
            >
                <UserInfo :user="user" />
                <ChevronsUpDown class="ml-auto size-4 text-slate-500" />
            </button>
        </DropdownMenuTrigger>

        <DropdownMenuContent
            align="start"
            side="top"
            class="w-64 rounded-xl border-[#2e2f30] bg-[#1f2022] p-1 text-slate-100 shadow-2xl"
        >
            <UserMenuContent :user="user" />
        </DropdownMenuContent>
    </DropdownMenu>
</template>
