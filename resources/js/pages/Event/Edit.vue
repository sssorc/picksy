<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { store } from '@/actions/App/Http/Controllers/EventController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { AlertError } from '@/components';
import { Head, useForm } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';

interface Event {
    id?: number;
    title: string;
    intro_text?: string;
    slug: string;
    password?: string;
    grading_password: string;
    start_datetime: string;
    is_published: boolean;
}

interface Props {
    event?: Event | null;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Event',
        href: '/event',
    },
];

const form = useForm({
    title: props.event?.title || '',
    intro_text: props.event?.intro_text || '',
    slug: props.event?.slug || '',
    password: props.event?.password || '',
    grading_password: props.event?.grading_password || '',
    start_datetime: props.event?.start_datetime || '',
});

const saving = ref(false);
const saveError = ref('');

const handleSubmit = async () => {
    saving.value = true;
    saveError.value = '';

    try {
        const response = await fetch(store().url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(form.data()),
        });

        const data = await response.json();

        if (!response.ok) {
            if (data.errors) {
                form.setError(data.errors);
            } else {
                saveError.value = data.message || 'Failed to save event';
            }
        } else {
            // Success
            form.clearErrors();
        }
    } catch (error) {
        saveError.value = 'An error occurred while saving';
    } finally {
        saving.value = false;
    }
};
</script>

<template>
    <Head title="Event Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Event Details</CardTitle>
                    <CardDescription>
                        Set up your event information. This will be visible to participants.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <AlertError v-if="saveError" class="mb-4">
                        {{ saveError }}
                    </AlertError>

                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="title">Event Title *</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                type="text"
                                placeholder="e.g., John & Jane's Wedding"
                                :disabled="saving"
                                required
                            />
                            <p v-if="form.errors.title" class="text-sm text-red-500">
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="intro_text">Intro Text</Label>
                            <Textarea
                                id="intro_text"
                                v-model="form.intro_text"
                                placeholder="Optional welcome message for participants"
                                :disabled="saving"
                                rows="3"
                            />
                            <p v-if="form.errors.intro_text" class="text-sm text-red-500">
                                {{ form.errors.intro_text }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="slug">Event Path *</Label>
                            <Input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                placeholder="e.g., john-jane-wedding"
                                :disabled="saving || event?.slug"
                                required
                            />
                            <p class="text-sm text-gray-500">
                                {{ event?.slug ? 'Path cannot be changed after creation' : 'Use lowercase letters, numbers, and hyphens only' }}
                            </p>
                            <p v-if="form.errors.slug" class="text-sm text-red-500">
                                {{ form.errors.slug }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="start_datetime">Event Start Date & Time *</Label>
                            <Input
                                id="start_datetime"
                                v-model="form.start_datetime"
                                type="datetime-local"
                                :disabled="saving"
                                required
                            />
                            <p class="text-sm text-gray-500">
                                Participants cannot submit picks before this time
                            </p>
                            <p v-if="form.errors.start_datetime" class="text-sm text-red-500">
                                {{ form.errors.start_datetime }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="password">Event Password (Optional)</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Leave blank for no password"
                                :disabled="saving"
                            />
                            <p class="text-sm text-gray-500">
                                Participants will need this password to access your event
                            </p>
                            <p v-if="form.errors.password" class="text-sm text-red-500">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="grading_password">Grading Password *</Label>
                            <Input
                                id="grading_password"
                                v-model="form.grading_password"
                                type="password"
                                placeholder="Required for grading questions"
                                :disabled="saving"
                                required
                            />
                            <p class="text-sm text-gray-500">
                                This password is required to grade questions during the event
                            </p>
                            <p v-if="form.errors.grading_password" class="text-sm text-red-500">
                                {{ form.errors.grading_password }}
                            </p>
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" :disabled="saving">
                                {{ saving ? 'Saving...' : 'Save Event' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

