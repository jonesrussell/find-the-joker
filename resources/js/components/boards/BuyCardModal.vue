<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { CardItem } from '@/components/boards/CardGrid.vue';

const props = withDefaults(
    defineProps<{
        open: boolean;
        card: CardItem | null;
        claimUrl: string;
        emtEmail: string;
        onSuccess?: () => void;
    }>(),
    { onSuccess: () => {} }
);

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const form = useForm({
    name: '',
    email: '',
    website: '', // honeypot
});

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            form.reset();
        }
    }
);

function submit() {
    if (!props.card) return;
    form.post(`${props.claimUrl}/${props.card.uuid}/claim`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('update:open', false);
            props.onSuccess?.();
        },
    });
}

function close() {
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Buy card {{ card?.number }}</DialogTitle>
                <DialogDescription>
                    Enter your name and email. You will receive instructions to send EMT.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="buy-name">Name</Label>
                    <Input
                        id="buy-name"
                        v-model="form.name"
                        required
                        autocomplete="name"
                    />
                    <p v-if="form.errors.name" class="text-sm text-destructive">
                        {{ form.errors.name }}
                    </p>
                </div>
                <div class="space-y-2">
                    <Label for="buy-email">Email</Label>
                    <Input
                        id="buy-email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                    />
                    <p v-if="form.errors.email" class="text-sm text-destructive">
                        {{ form.errors.email }}
                    </p>
                </div>
                <div class="hidden" aria-hidden="true">
                    <Label for="buy-website">Website</Label>
                    <Input id="buy-website" v-model="form.website" tabindex="-1" />
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="close">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        Reserve card
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
