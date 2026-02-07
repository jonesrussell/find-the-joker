<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type Board = {
    id: number;
    name: string;
    is_active: boolean;
    show_url: string;
};

type Props = {
    boards: Board[];
    urls: { index: string; store: string };
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url ?? '/' },
    { title: 'Boards', href: props.urls.index },
];

const createOpen = ref(false);
const form = useForm({ name: '' });

function submitCreate() {
    form.post(props.urls.store, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            createOpen.value = false;
        },
    });
}
</script>

<template>
    <Head title="Boards" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h1 class="text-2xl font-semibold">Boards</h1>
                <Dialog v-model:open="createOpen">
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="mr-2 size-4" />
                            Create board
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create board</DialogTitle>
                            <DialogDescription>Create a new board with 54 cards.</DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="submitCreate" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">Board name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="e.g. Skylar's Band Trip"
                                    required
                                />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>
                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="createOpen = false"
                                >
                                    Cancel
                                </Button>
                                <Button type="submit" :disabled="form.processing">
                                    Create
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="board in boards"
                    :key="board.id"
                    :href="board.show_url"
                    class="block transition-opacity hover:opacity-90"
                >
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-base">{{ board.name }}</CardTitle>
                            <Badge :variant="board.is_active ? 'default' : 'secondary'">
                                {{ board.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </CardHeader>
                        <CardContent>
                            <CardDescription>View and manage 54 cards</CardDescription>
                        </CardContent>
                    </Card>
                </Link>
            </div>
            <p v-if="boards.length === 0" class="text-muted-foreground">
                No boards yet. Create one to get started.
            </p>
        </div>
    </AppLayout>
</template>
