<template>
    <div class="relative overflow-hidden rounded-2xl border border-[#2e2f30] bg-[#18191a] p-2 shadow-2xl" aria-hidden="true">
        <div class="absolute top-0 left-0 h-1 w-full bg-gradient-to-r from-teal-500 to-emerald-400" />

        <div class="flex items-start gap-6 rounded-xl border border-[#2e2f30] bg-[#1e1f20] p-6">
            <div class="w-1/3 space-y-4">
                <div class="flex h-32 items-center justify-center rounded-lg border border-[#2e2f30] bg-[#242526]">
                    <div class="size-16 rounded-full border-4 border-teal-500/20 border-t-teal-500" />
                </div>
                <div class="flex h-24 flex-col justify-end rounded-lg border border-[#2e2f30] bg-[#242526] p-4">
                    <div class="mb-2 h-2 w-full rounded-full bg-[#334155]">
                        <div class="h-full w-3/4 rounded-full bg-teal-500" />
                    </div>
                </div>
            </div>

            <div class="flex-1 space-y-4">
                <div class="flex h-12 items-center gap-3 rounded-lg border border-[#2e2f30] bg-[#242526] px-4">
                    <div class="size-3 rounded-full bg-teal-500" />
                    <div class="h-3 w-32 rounded bg-[#334155]" />
                </div>

                <div class="flex h-44 items-end gap-2 rounded-lg border border-[#2e2f30] bg-[#242526] p-4">
                    <div v-for="height in [40, 70, 45, 90, 65, 30, 80]" :key="height" class="flex-1 rounded-t bg-[#334155]" :style="{ height: `${height}%` }" />
                </div>
            </div>
        </div>
    </div>
</template>
