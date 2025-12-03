<script setup>
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { CheckCircle2 } from 'lucide-vue-next';

defineProps({
    message: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: 'Success',
    },
});
</script>

<template>
    <Alert class="border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
        <CheckCircle2 class="size-4" />
        <AlertTitle>{{ title }}</AlertTitle>
        <AlertDescription>
            {{ message }}
        </AlertDescription>
    </Alert>
</template>
