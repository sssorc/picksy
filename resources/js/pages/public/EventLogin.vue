<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store } from '@/routes/participant';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps<{
    event: {
        id: number;
        title: string;
        intro_text?: string;
        slug: string;
        has_password: boolean;
    };
}>();

const firstName = ref('');
const lastName = ref('');
const password = ref('');
const errors = ref<{ first_name?: string; last_name?: string; password?: string }>({});
const processing = ref(false);
const showDuplicateModal = ref(false);
const duplicateParticipant = ref<{
    id: number;
    first_name: string;
    last_name: string;
    has_submitted: boolean;
} | null>(null);

async function handleSubmit() {
    processing.value = true;
    errors.value = {};

    try {
        const { data } = await axios.post(store.url(props.event.slug), {
            first_name: firstName.value,
            last_name: lastName.value,
            password: props.event.has_password ? password.value : undefined,
        });

        if (data.duplicate) {
            duplicateParticipant.value = data.participant;
            showDuplicateModal.value = true;
            processing.value = false;
        } else if (data.redirect) {
            router.visit(data.redirect);
        }
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
        processing.value = false;
    }
}

async function confirmIdentity() {
    if (!duplicateParticipant.value) return;

    processing.value = true;

    try {
        const { data } = await axios.post(`/${props.event.slug}/confirm-identity`, {
            participant_id: duplicateParticipant.value.id,
        });

        if (data.redirect) {
            router.visit(data.redirect);
        }
    } catch (error) {
        console.error('Error confirming identity:', error);
        processing.value = false;
    }
}

function cancelDuplicate() {
    showDuplicateModal.value = false;
    duplicateParticipant.value = null;
    firstName.value = '';
    lastName.value = '';
    password.value = '';
}
</script>

<template>
    <div class="flex min-h-dvh items-center justify-center bg-muted/30 px-4 py-12">
        <Head :title="`Enter Name - ${event.title}`" />

        <div class="w-full max-w-md space-y-6">
            <div class="space-y-2 text-center">
                <h1 class="text-2xl font-bold tracking-tight">{{ event.title }}</h1>
                <p class="text-sm text-muted-foreground">Enter your name to get started</p>
            </div>

            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="first_name">First Name</Label>
                        <Input id="first_name" v-model="firstName" type="text" placeholder="Enter your first name" required :autofocus="!event.has_password" :disabled="processing" />
                    </div>

                    <div class="space-y-2">
                        <Label for="last_name">Last Name</Label>
                        <Input id="last_name" v-model="lastName" type="text" placeholder="Enter your last name" required :disabled="processing" />
                    </div>

                    <div v-if="event.has_password" class="space-y-2">
                        <Label for="password">Event Password</Label>
                        <Input id="password" v-model="password" type="password" placeholder="Enter event password" required autofocus autocomplete="off" :disabled="processing" />
                        <InputError :message="errors.password" />
                    </div>

                    <Button type="submit" class="w-full" :disabled="processing" :loading="processing"> Continue </Button>
                </form>
            </div>
        </div>

        <!-- Duplicate Participant Modal -->
        <div v-if="showDuplicateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" @click.self="cancelDuplicate">
            <div class="w-full max-w-md rounded-lg border bg-card p-6 shadow-lg">
                <h2 class="mb-4 text-xl font-semibold">Welcome Back!</h2>
                <p class="mb-6 text-sm text-muted-foreground">
                    We found an existing entry for
                    <strong>{{ duplicateParticipant?.first_name }} {{ duplicateParticipant?.last_name }}</strong
                    >.
                    <span v-if="duplicateParticipant?.has_submitted"> You've already submitted your picks. </span>
                    <span v-else> Would you like to continue? </span>
                </p>

                <div class="flex gap-3">
                    <Button variant="outline" class="flex-1" @click="cancelDuplicate" :disabled="processing"> Cancel </Button>
                    <Button class="flex-1" @click="confirmIdentity" :disabled="processing" :loading="processing"> Yes, that's me </Button>
                </div>
            </div>
        </div>
    </div>
</template>
