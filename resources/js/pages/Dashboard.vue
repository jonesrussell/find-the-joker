<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { LayoutGrid } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: (dashboard() as { url?: string })?.url ?? '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Link href="/dashboard/boards" class="block transition-opacity hover:opacity-90">
                    <Card class="h-full">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-base">Find the Joker</CardTitle>
                            <LayoutGrid class="size-5 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <CardDescription>
                                Manage boards and cards. Create boards, mark cards sold, reveal the Joker.
                            </CardDescription>
                            <Button variant="outline" class="mt-2">
                                Manage boards
                            </Button>
                        </CardContent>
                    </Card>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
