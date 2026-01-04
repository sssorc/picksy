<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { password } from '@/routes/grade';
import { Head, useForm } from '@inertiajs/vue3';

interface Props {
    event: {
        title: string;
        slug: string;
    };
}

const props = defineProps<Props>();

const form = useForm({
    password: '',
});

function handleSubmit() {
    form.post(password.url(props.event.slug), {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="flex min-h-dvh items-center justify-center bg-muted/30 px-4 py-12">
        <Head :title="`Grade Questions - ${event.title}`" />

        <div class="w-full max-w-md space-y-6">
            <div class="space-y-2 text-center">
                <h1 class="text-2xl font-bold tracking-tight">{{ event.title }}</h1>
                <p class="text-sm text-muted-foreground">Enter grading password to continue</p>
            </div>

            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="password">Grading Password</Label>
                        <Input id="password" v-model="form.password" type="password" placeholder="Enter grading password" required autofocus autocomplete="off" :disabled="form.processing" />
                        <InputError :message="form.errors.password" />
                    </div>

                    <Button type="submit" class="w-full" :disabled="form.processing" :loading="form.processing">
                        Continue
                    </Button>
                </form>
            </div>
        </div>
    </div>
</template>
