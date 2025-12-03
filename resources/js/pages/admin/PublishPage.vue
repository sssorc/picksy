<script setup lang="ts">
import { publishWithoutPayment } from '@/actions/App/Http/Controllers/PublishController';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// TODO errorhandling

defineProps<{
    event: {
        is_published: boolean;
        start_datetime: string;
        password?: string;
        grading_password: string;
        slug: string;
    };
    appUrl: string;
}>();

const form = useForm({});

function handleSubmit() {
    form.post(publishWithoutPayment.url());
}
</script>
<template>
    <Head title="Questions" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h2 class="mt-3 mb-6 text-2xl font-bold">Publish</h2>
            <div v-if="event.is_published">
                <div class="rounded-lg bg-green-50 p-4 ring ring-green-900">
                    Your event is published at <a :href="`${appUrl}/${event.slug}`" target="_blank" class="underline">{{ appUrl.replace(/^https?:\/\//, '') }}/{{ event.slug }}</a>
                </div>
                <ul class="mt-6 list-inside list-disc space-y-2 pl-3">
                    <li v-if="event.password">
                        Use password <strong>{{ event.password }}</strong>
                    </li>
                    <li>
                        Submissions accepted starting at <span class="font-bold">{{ event.start_datetime }}</span>
                    </li>
                    <li>
                        Grade questions at <a :href="`${appUrl}/${event.slug}/grade`" target="_blank" class="underline">{{ appUrl.replace(/^https?:\/\//, '') }}/{{ event.slug }}/grade</a> with password <span class="font-bold">{{ event.grading_password }}</span>
                    </li>
                </ul>
                <p class="mt-12">All event data will be deleted 60 days after the event.</p>
            </div>
            <Card v-else>
                <CardHeader>
                    <CardTitle>Publish</CardTitle>
                    <CardDescription>Publish your event and make it available to participants.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <div class="text-sm text-muted-foreground">
                            <p class="mb-4">Publishing your event will make it available to participants at the URL you specified.</p>
                            <p class="font-medium text-foreground">Temporary: Publishing without payment for testing purposes.</p>
                        </div>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Publishing...' : 'Publish Event' }}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
