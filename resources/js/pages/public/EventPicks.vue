<script setup lang="ts">
import PicksQuestion from '@/components/picks/PicksQuestion.vue';
import { store } from '@/routes/picks';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

interface Answer {
    id: number;
    answer_text: string;
    is_correct?: boolean;
}

interface Question {
    id: number;
    question_text: string;
    is_tiebreaker: boolean;
    answers: Answer[];
}

interface Pick {
    question_id: number;
    question_text: string;
    is_tiebreaker: boolean;
    selected_answer_id: number | null;
    tiebreaker_answer: string | null;
    is_graded: boolean;
    is_correct: boolean | null;
    correct_answer_id: number | null;
    answers: Answer[];
}

interface Props {
    event: {
        slug: string;
        title: string;
        intro_text: string | null;
        picks_closed: boolean;
    };
    participant: {
        first_name: string;
        last_name: string;
    };
    questions: Question[];
    picks: Pick[] | null;
}

const props = defineProps<Props>();

const hasSubmittedPicks = computed(() => props.picks !== null);

// Separate regular questions from tiebreaker for easier rendering
const regularQuestions = computed(() => props.questions.filter((q) => !q.is_tiebreaker));
const tiebreakerQuestion = computed(() => props.questions.find((q) => q.is_tiebreaker));

// Track selected answers for each question (using any to allow both number and string values)
const selectedAnswers = ref<Record<number, any>>({});
const processing = ref(false);
const errors = ref<{ message?: string; picks?: string }>({});

const unansweredQuestions = computed(() => regularQuestions.value.filter((q) => !selectedAnswers.value[q.id]));

async function submitPicks() {
    processing.value = true;
    errors.value = {};

    // Transform data to match API format
    const picksData = props.questions.map((question) => {
        const answer = selectedAnswers.value[question.id];

        if (question.is_tiebreaker) {
            return {
                question_id: question.id,
                answer_id: null,
                tiebreaker_answer: (answer as string) || null,
            };
        }

        return {
            question_id: question.id,
            answer_id: answer as number,
            tiebreaker_answer: null,
        };
    });

    try {
        const { data } = await axios.post(store.url(props.event.slug), {
            picks: picksData,
        });

        if (data.redirect) {
            router.visit(data.redirect);
        } else if (data.message) {
            // Refresh the page to show submitted picks
            router.reload();
        }
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.data) {
            if (error.response.data.message) {
                errors.value.message = error.response.data.message;
            }
            if (error.response.data.errors) {
                errors.value = { ...errors.value, ...error.response.data.errors };
            }
        } else {
            errors.value.message = 'An error occurred. Please try again.';
        }
        processing.value = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}
</script>

<template>
    <div class="px-5 py-6">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold">{{ event.title }}</h1>
            <p v-if="event.intro_text" class="mt-2 text-gray-600">
                {{ event.intro_text }}
            </p>
            <p class="mt-2 text-sm text-gray-500">Welcome, {{ participant.first_name }} {{ participant.last_name }}</p>
        </div>

        <template v-if="event.picks_closed || hasSubmittedPicks">
            <div class="space-y-6">
                <h2 class="text-xl font-semibold">Your Picks</h2>

                <!-- Loop through picks -->
                <template v-for="pick in picks" :key="pick.question_id">
                    <!-- Tiebreaker question (text input) -->
                    <div v-if="pick.is_tiebreaker" class="rounded-lg border p-6">
                        <h3 class="mb-4 font-medium">
                            {{ pick.question_text }}
                        </h3>
                        <div class="rounded bg-gray-50 p-4">
                            <p class="text-sm text-gray-600">Your answer:</p>
                            <p class="font-medium">
                                {{ pick.tiebreaker_answer }}
                            </p>
                            <p v-if="pick.is_graded" class="mt-2 text-sm text-gray-500">(Tiebreaker - used only if needed)</p>
                        </div>
                    </div>

                    <!-- Multiple choice question -->
                    <PicksQuestion v-else mode="view" :question-text="pick.question_text" :answers="pick.answers" :selected-answer-id="pick.selected_answer_id" :is-graded="pick.is_graded" />
                </template>
            </div>
        </template>

        <template v-else>
            <div class="space-y-6">
                <h2 class="text-xl font-semibold">Make Your Picks</h2>

                <form @submit.prevent="submitPicks" class="space-y-6">
                    <!-- Loop through regular questions -->
                    <PicksQuestion v-for="(question, index) in regularQuestions" :key="question.id" mode="submit" :question-id="question.id" :question-text="question.question_text" :question-number="index + 1" :answers="question.answers" v-model="selectedAnswers[question.id]" />

                    <!-- Tiebreaker question -->
                    <div v-if="tiebreakerQuestion" class="rounded-lg border border-amber-200 bg-amber-50 p-6">
                        <h3 class="mb-4 font-medium">Tiebreaker: {{ tiebreakerQuestion.question_text }}</h3>
                        <input v-model="selectedAnswers[tiebreakerQuestion.id]" type="text" class="w-full rounded-lg border p-3" placeholder="Enter your answer" />
                    </div>

                    <!-- Submit button -->
                    <div class="items-center space-y-2 text-center">
                        <p class="text-sm text-gray-600">All picks are final once submitted..</p>
                        <button type="submit" :disabled="processing || unansweredQuestions.length" class="w-full rounded-lg bg-blue-600 px-6 py-3 font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                            {{ unansweredQuestions.length > 0 ? 'Answer all questions to submit' : 'Lock in Picks' }}
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </div>
</template>
