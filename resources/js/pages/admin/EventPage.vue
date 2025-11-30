<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/EventController';
import AlertError from '@/components/AlertError.vue';
import AlertSuccess from '@/components/AlertSuccess.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

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
const saveSuccess = ref('');

const formErrors = computed(() => {
    const errors = Object.values(form.errors);
    return errors.length > 0 ? errors.flat() : null;
});

async function handleSubmit() {
    saving.value = true;
    saveError.value = '';
    saveSuccess.value = '';

    try {
        await axios.post(store().url, form.data());

        form.clearErrors();
        saveSuccess.value = 'Event saved successfully!';
    } catch (error: any) {
        if (error.response?.data?.errors) {
            form.setError(error.response.data.errors);
        } else {
            saveError.value = error.response?.data?.message || 'Failed to save event';
        }
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Head title="Event" />

    <AppLayout>
        <form @submit.prevent="handleSubmit" class="flex h-full flex-1 flex-col gap-4 p-4">
            <h2 class="text-2xl font-bold">Event</h2>

            <AlertError v-if="saveError" :errors="[saveError]" />
            <AlertError v-else-if="formErrors" title="Error saving event" :errors="formErrors" />
            <AlertSuccess v-else-if="saveSuccess" :message="saveSuccess" />

            <Card>
                <CardHeader>
                    <CardTitle>Event Information</CardTitle>
                    <!-- <CardDescription> Set up your event information. This will be visible to participants. </CardDescription> -->
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="title">Event Title *</Label>
                        <Input id="title" v-model="form.title" type="text" class="w-xs max-w-full" placeholder="e.g., John & Jane's Wedding" :disabled="saving" required />
                    </div>

                    <div class="space-y-2">
                        <Label for="intro_text">Intro Text</Label>
                        <Textarea id="intro_text" v-model="form.intro_text" class="w-sm max-w-full" placeholder="Optional welcome message for participants" :disabled="saving" rows="3" />
                    </div>

                    <div class="space-y-2">
                        <Label for="start_datetime">Event Start Date & Time *</Label>
                        <Input id="start_datetime" v-model="form.start_datetime" class="w-xs max-w-full" type="datetime-local" :disabled="saving" required />
                        <p class="text-sm text-gray-500">Participants cannot submit picks before this time</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Technical Details</CardTitle>
                    <CardDescription> Set up your event information. This will be visible to participants. </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="slug">Event Path *</Label>
                        <Input id="slug" v-model="form.slug" type="text" class="w-xs max-w-full" placeholder="e.g., john-jane-wedding" :disabled="saving || event?.slug" required />
                        <p class="text-sm text-gray-500">
                            {{ event?.slug ? 'Path cannot be changed after creation' : 'Use lowercase letters, numbers, and hyphens only' }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Event Password (Optional)</Label>
                        <Input id="password" v-model="form.password" class="w-xs max-w-full" type="text" placeholder="Leave blank for no password" :disabled="saving" />
                        <p class="text-sm text-gray-500">Participants will need this password to access your event</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="grading_password">Grading Password *</Label>
                        <Input id="grading_password" v-model="form.grading_password" class="w-xs max-w-full" type="text" placeholder="Required for grading questions" :disabled="saving" required />
                        <p class="text-sm text-gray-500">This password is required to grade questions during the event</p>
                    </div>
                </CardContent>
            </Card>

            <div class="my-4 flex gap-2">
                <Button type="submit" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Save Event' }}
                </Button>
            </div>
        </form>
    </AppLayout>
</template>
