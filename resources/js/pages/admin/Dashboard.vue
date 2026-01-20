<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { formatEventDateTime } from '@/composables/useDateFormat';
import AppLayout from '@/layouts/AppLayout.vue';
import { edit as eventRoute } from '@/routes/event';
import { Head, Link } from '@inertiajs/vue3';
import { PhCalendarDot, PhCheckCircle, PhCircleDashed, PhClipboardText, PhKey, PhPlay, PhRocketLaunch, PhUsers } from '@phosphor-icons/vue';
import { computed } from 'vue';

interface Participant {
    name: string;
    score: number;
    tiebreaker_answer: string | null;
}

const props = defineProps<{
    event: {
        id: number;
        title: string;
        slug: string;
        password: string | null;
        grading_password: string;
        start_datetime: string | null;
        has_started: boolean;
        is_published: boolean;
    } | null;
    participants: Participant[];
    gradedCount: number;
    totalCount: number;
    tiebreaker_question: {
        id: number;
        question_text: string;
    } | null;
}>();

const eventStatus = computed(() => {
    if (!props.event) return null;
    if (!props.event.is_published) return 'draft';
    if (!props.event.has_started) return 'scheduled';

    // Check if all questions are graded
    if (props.gradedCount === props.totalCount && props.totalCount > 0) {
        return 'completed';
    }

    // Check if any questions are graded
    if (props.gradedCount > 0) {
        return 'live-grading';
    }

    // Event is live and accepting picks
    return 'live-accepting';
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <h2 class="text-2xl font-bold">Dashboard</h2>

            <!-- No Event State -->
            <Alert v-if="!props.event">
                <PhCalendarDot class="size-5" />
                <AlertTitle>No event yet</AlertTitle>
                <AlertDescription>
                    Create your event to get started.
                    <div class="mt-3">
                        <Button as-child size="sm">
                            <Link :href="eventRoute()">Create Your Event</Link>
                        </Button>
                    </div>
                </AlertDescription>
            </Alert>

            <!-- Event Exists -->
            <template v-else>
                <Card>
                    <CardHeader>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <CardTitle class="text-xl">{{ props.event.title }}</CardTitle>
                                <CardDescription v-if="props.event.start_datetime" class="mt-1">
                                    {{ formatEventDateTime(props.event.start_datetime) }}
                                </CardDescription>
                            </div>
                            <!-- Status Badge -->
                            <span v-if="eventStatus === 'completed'" class="inline-flex items-center gap-1.5 rounded-full bg-purple-100 px-2.5 py-1 text-xs font-medium text-purple-700 dark:bg-purple-900/50 dark:text-purple-300">
                                <PhCheckCircle class="size-3.5" weight="fill" />
                                Completed
                            </span>
                            <span v-else-if="eventStatus === 'live-grading'" class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                                <PhClipboardText class="size-3.5" />
                                Live - Grading
                            </span>
                            <span v-else-if="eventStatus === 'live-accepting'" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                                <PhPlay class="size-3.5" weight="fill" />
                                Live - Accepting Picks
                            </span>
                            <span v-else-if="eventStatus === 'scheduled'" class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                                <PhRocketLaunch class="size-3.5" />
                                Scheduled
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                <PhCircleDashed class="size-3.5" />
                                Draft
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-3">
							<div class="rounded-lg border bg-muted/40 p-4">
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <PhCalendarDot class="size-4" />
                                    Event URL
                                </div>
                                <div class="mt-2 grid gap-1 text-sm">
                                    <span>
                                        <a v-if="props.event.is_published" :href="'/' + props.event.slug" target="_blank" class="font-mono text-primary hover:underline">/{{ props.event.slug }}</a>
                                        <span v-else class="font-mono text-muted-foreground">/{{ props.event.slug }}</span>
                                    </span>
                                    <span v-if="props.event.password" class="text-muted-foreground">
                                        Password: <span class="font-mono text-primary">{{ props.event.password }}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="rounded-lg border bg-muted/40 p-4">
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <PhUsers class="size-4" />
                                    Participants
                                </div>
                                <p class="mt-1 text-2xl font-semibold">{{ props.participants.length }}</p>
                            </div>
                            <div class="rounded-lg border bg-muted/40 p-4">
                                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <PhCheckCircle class="size-4" />
                                    Questions Graded
                                </div>
                                <p class="mt-1 text-2xl font-semibold">{{ props.gradedCount }} / {{ props.totalCount }}</p>
                            </div>
                            
                        </div>

                        <!-- Grading Info -->
                        <div v-if="props.event.is_published" class="rounded-lg border bg-muted/40 p-4">
                            <div class="flex items-center gap-2 text-sm font-medium">
                                <PhKey class="size-4" />
                                Grading
                            </div>
                            <div class="mt-2 grid gap-3 text-sm">
                                <span class="text-muted-foreground">
                                    Page:
                                    <a :href="'/' + props.event.slug + '/grade'" target="_blank" class="font-mono text-primary hover:underline">/{{ props.event.slug }}/grade</a>
                                </span>
                                <span class="text-muted-foreground">
                                    Passphrase: <span class="font-mono text-primary">{{ props.event.grading_password }}</span>
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Leaderboard Preview -->
                <Card v-if="props.participants.length > 0">
                    <CardHeader>
                        <CardTitle>Leaderboard</CardTitle>
                        <CardDescription>Top participants by score</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="divide-y">
                            <div v-for="(participant, index) in props.participants.slice(0, 10)" :key="participant.name" class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex size-7 items-center justify-center rounded-full text-sm font-medium"
                                        :class="{
                                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300': index === 0,
                                            'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400': index !== 0,
                                        }"
                                    >
                                        {{ index + 1 }}
                                    </span>
                                    <span class="font-medium">{{ participant.name }}</span>
                                </div>
                                <span class="text-lg font-semibold">{{ participant.score }}</span>
                            </div>
                        </div>
                        <p v-if="props.participants.length > 10" class="mt-4 text-center text-sm text-muted-foreground">and {{ props.participants.length - 10 }} more participants...</p>
                    </CardContent>
                </Card>

                <!-- No Participants Yet - Scheduled -->
                <Alert v-else-if="eventStatus === 'scheduled'" class="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950/50">
                    <PhCalendarDot class="size-5 text-blue-600 dark:text-blue-400" />
                    <AlertTitle class="text-blue-800 dark:text-blue-200">Event scheduled</AlertTitle>
                    <AlertDescription class="text-blue-700 dark:text-blue-300"> Your event is published but hasn't started yet. Participants will be able to submit picks once the event starts. </AlertDescription>
                </Alert>

                <!-- No Participants Yet - Live Accepting -->
                <Alert v-else-if="eventStatus === 'live-accepting'" class="border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950/50">
                    <PhUsers class="size-5 text-emerald-600 dark:text-emerald-400" />
                    <AlertTitle class="text-emerald-800 dark:text-emerald-200">Waiting for participants</AlertTitle>
                    <AlertDescription class="text-emerald-700 dark:text-emerald-300"> Your event is live! Share the link with your guests to start collecting picks. </AlertDescription>
                </Alert>

                <!-- No Participants Yet - Live Grading -->
                <Alert v-else-if="eventStatus === 'live-grading'" class="border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/50">
                    <PhClipboardText class="size-5 text-amber-600 dark:text-amber-400" />
                    <AlertTitle class="text-amber-800 dark:text-amber-200">Grading in progress</AlertTitle>
                    <AlertDescription class="text-amber-700 dark:text-amber-300"> Questions are being graded. No new picks can be accepted once grading has started. </AlertDescription>
                </Alert>

                <!-- No Participants Yet - Completed -->
                <Alert v-else-if="eventStatus === 'completed'" class="border-purple-200 bg-purple-50 dark:border-purple-800 dark:bg-purple-950/50">
                    <PhCheckCircle class="size-5 text-purple-600 dark:text-purple-400" />
                    <AlertTitle class="text-purple-800 dark:text-purple-200">Event completed</AlertTitle>
                    <AlertDescription class="text-purple-700 dark:text-purple-300"> All questions have been graded. Unfortunately, no participants submitted picks for this event. </AlertDescription>
                </Alert>
            </template>
        </div>
    </AppLayout>
</template>
