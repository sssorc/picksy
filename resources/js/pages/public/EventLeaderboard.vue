<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';

interface Participant {
    name: string;
    score: number;
    tiebreaker_answer: string | null;
}

interface Props {
    event: {
        title: string;
        slug: string;
    };
    participants: Participant[];
    gradedCount: number;
    totalCount: number;
    tiebreaker_question: {
        id: number;
        question_text: string;
    } | null;
}

defineProps<Props>();

defineOptions({
    layout: PublicLayout,
});
</script>

<template>
    <div class="px-5 py-6">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold">{{ event.title }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ gradedCount }} of {{ totalCount }} questions graded</p>
        </div>

        <!-- Leaderboard -->
        <div class="space-y-4">
            <h2 class="text-xl font-semibold">Leaderboard</h2>

            <div v-if="participants.length === 0" class="rounded-lg border border-gray-200 bg-white p-8 text-center">
                <p class="text-gray-600">No participants have submitted picks yet.</p>
            </div>

            <div v-else class="space-y-2">
                <div v-for="(participant, index) in participants" :key="index" class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 font-semibold text-gray-700">
                            {{ index + 1 }}
                        </div>
                        <div>
                            <p class="font-medium">{{ participant.name }}</p>
                            <p v-if="participant.tiebreaker_answer && tiebreaker_question" class="text-xs text-gray-500">{{ tiebreaker_question.question_text }}: {{ participant.tiebreaker_answer }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900">{{ participant.score }}</p>
                        <p class="text-xs text-gray-500">points</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
