<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/PublishController';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectItem } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { PhConfetti } from '@phosphor-icons/vue';
import { format, getMinutes } from 'date-fns';
import { computed } from 'vue';

// TODO errorhandling

const props = defineProps<{
    event: {
        is_published: boolean;
        start_datetime: string;
        password?: string;
        grading_password: string;
        slug: string;
    };
    appUrl: string;
}>();

const form = useForm({
    max_entries: '10',
});

const isFree = computed(() => form.max_entries === '10');

const formattedStartDatetime = computed(() => {
    const date = new Date(props.event.start_datetime);
    const minutes = getMinutes(date);
    const timeFormat = minutes === 0 ? 'haaa' : 'h:mmaaa';
    return format(date, `${timeFormat} 'on' MMMM do`);
});

function handleSubmit() {
    form.post(store.url());
}
</script>
<template>
    <Head title="Questions" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h2 class="mt-3 mb-6 text-2xl font-bold">Publish</h2>
            <div v-if="event.is_published">
                <Alert>
                    <PhConfetti />
                    <AlertTitle>Your event is published!</AlertTitle>
                    <AlertDescription>
                        <p>
                            Your event is published at <a :href="`${appUrl}/${event.slug}`" target="_blank" class="underline">{{ appUrl.replace(/^https?:\/\//, '') }}/{{ event.slug }}</a>
                        </p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            <li v-if="event.password">
                                Use password <strong>{{ event.password }}</strong>
                            </li>
                            <li>
                                Submissions will open at <span class="font-bold">{{ formattedStartDatetime }}</span>
                            </li>
                            <li>
                                Grade questions at <a :href="`${appUrl}/${event.slug}/grade`" target="_blank" class="underline">{{ appUrl.replace(/^https?:\/\//, '') }}/{{ event.slug }}/grade</a> with password <span class="font-bold">{{ event.grading_password }}</span>
                            </li>
                        </ul>
                        <p class="mt-12">All event data will be deleted 60 days after the event.</p>
                    </AlertDescription>
                </Alert>
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
                        </div>
                        <div class="space-y-2">
                            <Label for="tier">Select Tier</Label>
                            <Select v-model="form.max_entries" placeholder="Select a tier">
                                <SelectItem value="10">Free - Up to 10 entries</SelectItem>
                                <SelectItem value="60">$15 - Up to 60 entries</SelectItem>
                                <SelectItem value="0">$100 - Unlimited entries</SelectItem>
                            </Select>
                        </div>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? (isFree ? 'Publishing...' : 'Proceed to Payment...') : isFree ? 'Publish Event' : 'Proceed to Payment' }}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
