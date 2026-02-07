<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';

const page = usePage();

watch(
    () => (page.props.flash as { success?: string; error?: string } | undefined) ?? {},
    (flash) => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    },
    { immediate: true }
);
</script>

<template>
    <span class="sr-only" aria-hidden="true" />
</template>
