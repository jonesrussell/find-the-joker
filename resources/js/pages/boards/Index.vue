<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type Board = { id: number; name: string };

type Props = {
    boards: Board[];
    urls: { showPath: string };
};

const props = defineProps<Props>();

const selectedId = ref<string>(String(props.boards[0]?.id ?? ''));

watch(selectedId, (id) => {
    if (id) {
        router.visit(`${props.urls.showPath}/${id}`);
    }
});
</script>

<template>
    <Head title="Find the Joker" />
    <div class="min-h-screen bg-background p-4 md:p-8">
        <div class="mx-auto max-w-2xl space-y-6 text-center">
            <h1 class="text-2xl font-bold md:text-3xl">
                Support Skylar's High School Band Trip to Toronto!
            </h1>
            <p class="text-muted-foreground">
                Pick a card for $10. Find the Joker and win!
            </p>
            <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                <Select v-model="selectedId">
                    <SelectTrigger class="w-full min-w-[200px]">
                        <SelectValue placeholder="Select a board" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="board in boards"
                            :key="board.id"
                            :value="String(board.id)"
                        >
                            {{ board.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button
                    v-if="selectedId"
                    @click="router.visit(`${urls.showPath}/${selectedId}`)"
                >
                    View board
                </Button>
            </div>
        </div>
    </div>
</template>
