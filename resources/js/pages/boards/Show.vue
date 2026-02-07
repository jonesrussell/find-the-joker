<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import CardGrid from '@/components/boards/CardGrid.vue';
import type { CardItem } from '@/components/boards/CardGrid.vue';
import BuyCardModal from '@/components/boards/BuyCardModal.vue';
import { getEcho } from '@/echo';

type Board = { id: number; name: string };

type Props = {
    board: Board;
    cards: CardItem[];
    emtEmail: string;
    pricePerCard: number;
    urls: { claimPath: string };
};

const props = defineProps<Props>();
const page = usePage();

const cardsMap = ref<Record<number, CardItem>>({});
watch(
    () => props.cards,
    (cards) => {
        const map: Record<number, CardItem> = {};
        for (const c of cards) {
            map[c.number] = { ...c };
        }
        cardsMap.value = map;
    },
    { immediate: true }
);

const selectedCard = ref<CardItem | null>(null);
const buyModalOpen = ref(false);

function onSelectCard(card: CardItem) {
    if (card.status !== 'available') return;
    selectedCard.value = card;
    buyModalOpen.value = true;
}

function onClaimSuccess() {
    if (selectedCard.value) {
        cardsMap.value[selectedCard.value.number] = {
            ...selectedCard.value,
            status: 'pending',
        };
    }
    selectedCard.value = null;
}

const broadcasting = computed(() => (page.props.broadcasting as { key?: string; cluster?: string }) ?? null);
watch(
    [broadcasting, () => props.board.id],
    ([config, boardId]) => {
        if (!config?.key || !boardId) return undefined;
        const echo = getEcho(config as { key: string; cluster: string });
        if (!echo) return undefined;
        echo.channel(`board.${boardId}`).listen('.JokerRevealed', (payload: Record<string, unknown>) => {
            const num = payload.number as number | undefined;
            if (num != null && cardsMap.value[num]) {
                const next = { ...cardsMap.value };
                next[num] = { ...cardsMap.value[num], ...payload, revealed: true, status: 'joker' } as CardItem;
                cardsMap.value = next;
            }
        });
        return () => {
            echo.leave(`board.${boardId}`);
        };
    },
    { immediate: true }
);

const successMessage = computed(() => (page.props.flash as { success?: string })?.success ?? null);
</script>

<template>
    <Head :title="board.name" />
    <div class="min-h-screen bg-background p-4 md:p-8">
        <div class="mx-auto max-w-4xl space-y-6">
            <h1 class="text-center text-2xl font-bold md:text-3xl">
                Support Skylar's High School Band Trip to Toronto!
            </h1>
            <p class="text-center text-muted-foreground">
                ${{ pricePerCard }} per card. Click an available (green) card to reserve.
            </p>
            <div
                v-if="successMessage"
                class="rounded-lg border border-green-200 bg-green-50 p-4 text-center text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
            >
                {{ successMessage }}
            </div>
            <CardGrid
                :cards="cardsMap"
                :interactive="true"
                @select="onSelectCard"
            />
        </div>
        <BuyCardModal
            v-model:open="buyModalOpen"
            :card="selectedCard"
            :claim-url="urls.claimPath"
            :emt-email="emtEmail"
            :on-success="onClaimSuccess"
        />
    </div>
</template>
