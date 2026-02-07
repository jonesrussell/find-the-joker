<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

export type CardItem = {
    id: number;
    uuid: string;
    number: number;
    status: string;
    revealed: boolean;
    buyer_name?: string | null;
    buyer_email?: string | null;
    admin_sold?: boolean;
};

type Props = {
    cards: CardItem[] | Record<number, CardItem>;
    interactive?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    interactive: false,
});

const cardsList = computed(() => {
    const c = props.cards;
    if (Array.isArray(c)) {
        return c;
    }
    return Object.values(c).sort((a, b) => a.number - b.number);
});

const cardsByNumber = computed(() => {
    const map: Record<number, CardItem> = {};
    for (const card of cardsList.value) {
        map[card.number] = card;
    }
    return map;
});

function statusClass(status: string): string {
    switch (status) {
        case 'available':
            return 'bg-green-500/80 text-white border-green-600';
        case 'pending':
            return 'bg-yellow-500/80 text-white border-yellow-600';
        case 'sold':
            return 'bg-gray-500/80 text-white border-gray-600';
        case 'joker':
            return 'bg-red-500/80 text-white border-red-600';
        default:
            return 'bg-muted';
    }
}
</script>

<template>
    <div class="grid grid-cols-6 gap-2 sm:gap-3 md:grid-cols-9">
        <template v-for="n in 54" :key="n">
            <div
                v-if="cardsByNumber[n]"
                class="min-h-14"
            >
                <div
                    v-if="interactive && cardsByNumber[n].revealed"
                    class="card-flip-perspective h-full min-h-14"
                >
                    <div
                        class="card-flip-inner rotate-y-180 relative h-full min-h-14"
                    >
                        <div
                            :class="cn(
                                'card-flip-front absolute inset-0 flex items-center justify-center rounded-lg border p-2 text-sm font-medium',
                                statusClass(cardsByNumber[n].status)
                            )"
                        >
                            {{ cardsByNumber[n].number }}
                        </div>
                        <div
                            class="card-flip-back absolute inset-0 flex items-center justify-center rounded-lg border border-red-600 bg-red-500/80 p-2 font-bold text-white"
                        >
                            JOKER
                        </div>
                    </div>
                </div>
                <div
                    v-else-if="interactive && cardsByNumber[n].status === 'available'"
                    :class="cn(
                        'flex min-h-14 cursor-pointer flex-col items-center justify-center rounded-lg border p-2 text-center text-sm font-medium transition-colors hover:opacity-90',
                        statusClass(cardsByNumber[n].status)
                    )"
                    @click="$emit('select', cardsByNumber[n])"
                >
                    <span class="font-semibold">{{ cardsByNumber[n].number }}</span>
                </div>
                <div
                    v-else-if="interactive"
                    :class="cn(
                        'flex min-h-14 flex-col items-center justify-center rounded-lg border p-2 text-center text-sm font-medium',
                        statusClass(cardsByNumber[n].status)
                    )"
                >
                    <span class="font-semibold">{{ cardsByNumber[n].number }}</span>
                </div>
                <div
                    v-else
                    :class="cn(
                        'flex min-h-14 flex-col items-center justify-center rounded-lg border p-2 text-center text-sm font-medium transition-colors',
                        statusClass(cardsByNumber[n].status)
                    )"
                >
                    <span class="font-semibold">{{ cardsByNumber[n].number }}</span>
                    <slot name="actions" :card="cardsByNumber[n]">
                        <!-- Admin or public actions -->
                    </slot>
                </div>
            </div>
            <div
                v-else
                class="flex min-h-14 items-center justify-center rounded-lg border bg-muted text-muted-foreground text-sm"
            >
                {{ n }}
            </div>
        </template>
    </div>
</template>
