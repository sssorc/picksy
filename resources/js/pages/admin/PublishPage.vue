<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps<{
    event: {
        is_published: boolean;
        start_datetime: string;
        password?: string;
        grading_password: string;
    };
}>();

async function handleSubmit() {
    console.log('submit');
}
</script>
<template>
    <Head title="Questions" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div v-if="event.is_published">
                <div>Your event is published at <a href="TODO" target="_blank">TODO</a></div>
                <ul>
                    <li v-if="event.password">
                        Use password <strong>{{ event.password }}</strong>
                    </li>
                    <li>
                        Submissions accepted starting at <span class="font-bold">{{ event.start_datetime }}</span>
                    </li>
                    <li>
                        Grade questions at <a href="TODO" target="_blank">TODO</a> with password <span class="font-bold">{{ event.grading_password }}</span>
                    </li>
                </ul>
            </div>
            <Card v-else>
                <CardHeader>
                    <CardTitle>Publish</CardTitle>
                    <CardDescription>Publish your event and make it available to participants.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="handleSubmit" class="space-y-6">TODO</form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
