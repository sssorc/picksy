<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/EventController';
import AlertError from '@/components/AlertError.vue';
import AlertSuccess from '@/components/AlertSuccess.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { PhWarning } from '@phosphor-icons/vue';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
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
    appUrl: string;
}

const props = defineProps<Props>();

const form = useForm({
    title: props.event?.title || '',
    intro_text: props.event?.intro_text || '',
    slug: props.event?.slug || '',
    password: props.event?.password || '',
    grading_password: props.event?.grading_password || '',
    start_datetime: props.event?.start_datetime ? new Date(props.event.start_datetime) : '',
});

const saving = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const showDeleteEventModal = ref(false);

const formErrors = computed(() => {
    const errors = Object.values(form.errors);
    return errors.length > 0 ? errors.flat() : null;
});

const dateFormat = {
    month: 'LLL',
    year: 'yyyy',
    day: 'd',
    input: 'LLL d, yyyy, h:mma',
};

async function handleSubmit() {
    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        const data = { ...form.data() };

        // Convert Date object to ISO string for backend
        if (data.start_datetime instanceof Date) {
            data.start_datetime = data.start_datetime.toISOString();
        }

        await axios.post(store().url, data);

        form.clearErrors();
        successMessage.value = 'Event saved successfully!';

        // Reload to update shared data (including eventStatus)
        router.reload();
    } catch (error: any) {
        if (error.response?.data?.errors) {
            form.setError(error.response.data.errors);
        } else {
            errorMessage.value = error.response?.data?.message || 'Failed to save event';
        }
    } finally {
        saving.value = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

async function deleteEvent() {
    // TODO
}
</script>

<template>
    <Head title="Event" />

    <AppLayout>
        <form @submit.prevent="handleSubmit" class="flex h-full flex-1 flex-col gap-4 p-4">
            <h2 class="text-2xl font-bold">Event</h2>

            <AlertError v-if="errorMessage" :errors="[errorMessage]" />
            <AlertError v-else-if="formErrors" title="Error saving event" :errors="formErrors" />
            <AlertSuccess v-else-if="successMessage" :message="successMessage" />

            <Card>
                <CardHeader>
                    <CardTitle>Event Information</CardTitle>
                    <!-- <CardDescription> Set up your event information. This will be visible to participants. </CardDescription> -->
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="title">Event Title</Label>
                            <Input id="title" v-model="form.title" type="text" class="w-xs max-w-full" placeholder="e.g., John & Jane's Wedding" :disabled="saving" required />
                        </div>
                        <div class="w-xs max-w-full space-y-2">
                            <Label for="start_datetime">Event Start Date & Time</Label>
                            <VueDatePicker id="start_datetime" v-model="form.start_datetime" :disabled="saving" :enable-time-picker="true" :formats="dateFormat" :min-date="new Date()" placeholder="Select date and time" required />
                            <p class="text-sm text-gray-500">Users cannot submit picks before this time.</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="intro_text">Intro Text <small class="text-gray-500">(Optional)</small></Label>
                        <p class="text-sm text-gray-500">This will display above the questions when users make their picks.</p>
                        <Textarea id="intro_text" v-model="form.intro_text" class="w-sm max-w-full" placeholder="Optional welcome message for participants" :disabled="saving" rows="3" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Choose URL</CardTitle>
                </CardHeader>
                <CardContent class="space-y-8">
                    <div class="space-y-2">
                        <Label for="slug">Event URL</Label>
                        <p v-if="event?.is_published" class="text-sm text-gray-500">
                            <a :href="`${appUrl}/${event.slug}`" target="_blank" class="underline">{{ appUrl.replace(/^https?:\/\//, '') }}/{{ event.slug }}</a
                            >/
                        </p>
                        <div v-else class="flex items-center">
                            <span class="text-gray-500">{{ appUrl.replace(/^https?:\/\//, '') }}/</span>
                            <Input id="slug" v-model="form.slug" type="text" class="w-xs max-w-full" placeholder="e.g., john-jane-wedding" :disabled="saving || event?.is_published" required />
                        </div>
                        <template v-if="!event?.is_published">
                            <p class="text-sm text-gray-500">Letters, numbers, and hyphens only.</p>
                            <p class="flex items-center gap-2 text-sm text-gray-500"><PhWarning class="text-yellow-400" weight="fill" />Cannot be changed after publishing.</p>
                        </template>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Password</CardTitle>
                </CardHeader>
                <CardContent class="space-y-8">
                    <div class="space-y-2">
                        <Label for="password">Event Password <small class="text-gray-500">(Optional)</small></Label>
                        <Input id="password" v-model="form.password" class="w-xs max-w-full" type="text" placeholder="Leave blank for no password" :disabled="saving" />
                        <p class="text-sm text-gray-500">Participants will need this password to access your event</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="grading_password">Grading Passphrase</Label>
                        <p v-if="event?.grading_password" class="text-sm text-gray-500">{{ event.grading_password }}</p>
                        <p v-else class="text-sm text-gray-500">This will be auto-generated after saving.</p>
                    </div>
                </CardContent>
            </Card>

            <div class="my-4 flex justify-between gap-2">
                <Dialog>
                    <DialogTrigger as-child>
                        <button type="button" class="cursor-pointer text-sm text-red-500 hover:underline">Delete event</button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Delete event</DialogTitle>
                            <DialogDescription>All event data will be deleted and all pages inaccessible. This action cannot be undone.</DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <DialogClose as-child>
                                <Button type="button" variant="secondary" @click="showDeleteEventModal = false">Cancel</Button>
                            </DialogClose>
                            <Button type="button" variant="destructive" @click="deleteEvent">Delete event</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
                <Button type="submit" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Save Event' }}
                </Button>
            </div>
        </form>
    </AppLayout>
</template>
