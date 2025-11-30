<script setup lang="ts">
import Question from '@/components/question/Question.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>
<template>
    <Head title="Questions" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h2 class="text-2xl font-bold">Questions</h2>
            <div>
                <Question :index="1" />
            </div>
            <Card>
                <CardHeader>
                    <CardTitle>Questions</CardTitle>
                    <CardDescription>Create the questinos and answers for your event.</CardDescription>
                </CardHeader>
                <CardContent> </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
