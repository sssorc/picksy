<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/PublishController';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { formatEventDateTime } from '@/composables/useDateFormat';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { PhConfetti } from '@phosphor-icons/vue';
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

const formattedStartDatetime = computed(() => formatEventDateTime(props.event.start_datetime));

function handleSubmit() {
    form.post(store.url());
}
</script>
<template>
    <Head title="Questions" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h2 class="mt-3 mb-4 text-2xl font-bold">Publish</h2>
            
			<div v-if="event.is_published">
                <Card>
                    <CardContent>
						<div class="flex items-center gap-4">
							<PhConfetti :size="32" class="text-primary" weight="duotone"/>
                            <h3 class="text-3xl font-bold">Your event is published!</h3>
						</div>
                            <div class="w-full space-y-4 text-left mt-6">
                                <p>
                                    Your event is published at <a :href="`${appUrl}/${event.slug}`" target="_blank" class="underline">{{ appUrl.replace(/^https?:\/\//, '') }}/{{ event.slug }}</a>
                                </p>
                                <ul class="list-inside list-disc space-y-2">
                                    <li v-if="event.password">
                                        Event password is <strong>{{ event.password }}</strong>
                                    </li>
                                    <li>
                                        Submissions will open on <span class="font-bold">{{ formattedStartDatetime }}</span>
                                    </li>
                                    <li>
                                        Grade questions at <a :href="`${appUrl}/${event.slug}/grade`" target="_blank" class="underline">{{ appUrl.replace(/^https?:\/\//, '') }}/{{ event.slug }}/grade</a> with passphrase <strong>{{ event.grading_password }}</strong>
                                    </li>
                                </ul>
                                <p class="mt-6 text-muted-foreground">All event data will be deleted 60 days after the event.</p>
                            </div>
                    </CardContent>
                </Card>
            </div>
			
            <div v-else>
                <p>Publish your event and make it available to participants.</p>
                <p class="mb-4">You can edit questions and event details and (excluding the URL) after publishing.</p>
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div class="text-sm text-foreground"></div>
                    <div class="space-y-2">
                        <Label for="tier">Select Tier</Label>
                        <div class="w-sm max-w-full">
                            <Select v-model="form.max_entries">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a tier" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="10">Free - Up to 10 entries</SelectItem>
                                    <SelectItem value="60">$15 - Up to 30 entries</SelectItem>
                                    <SelectItem value="0">$100 - Unlimited entries</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <Button type="submit" :disabled="form.processing" :loading="form.processing">
                        {{ isFree ? 'Publish Event' : 'Proceed to Payment' }}
                    </Button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
