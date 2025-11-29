<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

async function handleSubmit() {
    console.log('submit');
}
</script>
<template>
    <Head title="Questions" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Questions</CardTitle>
                    <CardDescription>Create the questinos and answers for your event.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="handleSubmit" class="space-y-6">TODO</form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
