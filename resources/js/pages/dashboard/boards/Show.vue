<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { RotateCcw, Trash2, Download } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import CardGrid from '@/components/boards/CardGrid.vue';
import type { CardItem } from '@/components/boards/CardGrid.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type Board = {
    id: number;
    name: string;
    is_active: boolean;
};

type Props = {
    board: Board;
    cards: CardItem[];
    urls: {
        update: string;
        destroy: string;
        reset: string;
        export: string;
        cardUpdate: string;
    };
};

const props = defineProps<Props>();
const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: (dashboard() as { url?: string })?.url ?? '/' },
    { title: 'Boards', href: '/dashboard/boards' },
    { title: props.board.name, href: '#' },
];

const cardsMap = ref<Record<number, CardItem>>({});
watch(() => props.cards, (cards) => {
    const map: Record<number, CardItem> = {};
    for (const c of cards) {
        map[c.number] = { ...c };
    }
    cardsMap.value = map;
}, { immediate: true });

const jokerConfirmOpen = ref(false);
const jokerCard = ref<CardItem | null>(null);

function openJokerConfirm(card: CardItem) {
    jokerCard.value = card;
    jokerConfirmOpen.value = true;
}

function confirmJokerReveal() {
    if (!jokerCard.value) return;
    const card = jokerCard.value;
    const prev = { ...cardsMap.value[card.number] };
    cardsMap.value[card.number] = { ...card, status: 'joker', revealed: true };
    router.patch(`${props.urls.cardUpdate}/${card.id}`, { action: 'mark_joker' }, {
        preserveScroll: true,
        onError: () => {
            cardsMap.value[card.number] = prev;
        },
    });
    jokerConfirmOpen.value = false;
    jokerCard.value = null;
}

function markAsSold(card: CardItem) {
    const prev = { ...cardsMap.value[card.number] };
    cardsMap.value[card.number] = { ...card, status: 'sold', admin_sold: true };
    router.patch(`${props.urls.cardUpdate}/${card.id}`, { action: 'mark_sold' }, {
        preserveScroll: true,
        onError: () => {
            cardsMap.value[card.number] = prev;
        },
    });
}

function resetBoard() {
    if (!confirm('Reset all cards to available? This cannot be undone.')) return;
    router.post(props.urls.reset, {}, { preserveScroll: true });
}

function deleteBoard() {
    if (!confirm('Delete this board and all its cards? This cannot be undone.')) return;
    router.delete(props.urls.destroy);
}

function toggleActive() {
    router.patch(props.urls.update, { is_active: !props.board.is_active }, { preserveScroll: true });
}
</script>

<template>
    <Head :title="board.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-semibold">{{ board.name }}</h1>
                    <Badge :variant="board.is_active ? 'default' : 'secondary'">
                        {{ board.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                    <Button variant="outline" size="sm" @click="toggleActive">
                        {{ board.is_active ? 'Deactivate' : 'Activate' }}
                    </Button>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <a :href="urls.export" download>
                            <Download class="mr-2 size-4" />
                            Export buyers CSV
                        </a>
                    </Button>
                    <Button variant="outline" size="sm" @click="resetBoard">
                        <RotateCcw class="mr-2 size-4" />
                        Reset board
                    </Button>
                    <Button variant="destructive" size="sm" @click="deleteBoard">
                        <Trash2 class="mr-2 size-4" />
                        Delete board
                    </Button>
                </div>
            </div>
            <CardGrid :cards="cardsMap" :interactive="false">
                <template #actions="{ card }">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm" class="mt-1 h-6 text-xs">
                                Actions
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent>
                            <DropdownMenuItem
                                v-if="card.status === 'pending'"
                                @click="markAsSold(card)"
                            >
                                Mark as Sold
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                v-if="card.status !== 'joker'"
                                @click="openJokerConfirm(card)"
                            >
                                Mark as Joker
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <p v-if="card.buyer_name || card.buyer_email" class="mt-0.5 truncate text-xs opacity-90">
                        {{ card.buyer_name ?? card.buyer_email }}
                    </p>
                </template>
            </CardGrid>
        </div>

        <Dialog v-model:open="jokerConfirmOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Reveal Joker?</DialogTitle>
                    <DialogDescription>
                        Are you sure? This will reveal the Joker to everyone viewing the board.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="jokerConfirmOpen = false">Cancel</Button>
                    <Button variant="destructive" @click="confirmJokerReveal">Reveal Joker</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
