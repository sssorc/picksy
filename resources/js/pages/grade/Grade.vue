<script setup lang="ts">
import AlertError from '@/components/AlertError.vue';
import AlertSuccess from '@/components/AlertSuccess.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { store } from '@/routes/grade';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { CheckCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Answer {
    id: number;
    answer_text: string;
    is_correct: boolean | null;
}

interface Question {
    id: number;
    question_text: string;
    is_graded: boolean;
    graded_at: string | null;
    answers: Answer[];
}

interface Props {
    event: {
        title: string;
        slug: string;
    };
    questions: Question[];
}

const props = defineProps<Props>();

defineOptions({
    layout: (h: any, page: any) => h(PublicLayout, { showBottomNav: false }, () => page),
});

// Track selected correct answers for each question
const selectedAnswers = ref<Record<number, number | null>>({});

// Initialize with existing graded answers
props.questions.forEach((question) => {
    const correctAnswer = question.answers.find((a) => a.is_correct);
    selectedAnswers.value[question.id] = correctAnswer?.id || null;
});

const processing = ref(false);
const saveError = ref('');
const saveSuccess = ref('');

const gradedCount = computed(() => {
    return props.questions.filter((q) => q.is_graded).length;
});

const totalCount = computed(() => {
    return props.questions.length;
});

const hasChanges = computed(() => {
    return props.questions.some((question) => {
        const currentCorrect = question.answers.find((a) => a.is_correct);
        const selected = selectedAnswers.value[question.id];
        return currentCorrect?.id !== selected;
    });
});

async function handleSubmit() {
    processing.value = true;
    saveError.value = '';

    try {
        const grades = props.questions.map((question) => ({
            question_id: question.id,
            correct_answer_id: selectedAnswers.value[question.id] || null,
        }));

        await axios.post(store.url(props.event.slug), { grades });

        // Reload the page to get updated data
        window.location.reload();
    } catch (error: any) {
        if (error.response?.data?.message) {
            saveError.value = error.response.data.message;
        } else {
            saveError.value = 'Failed to save grades. Please try again.';
        }
        processing.value = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function clearQuestion(questionId: number) {
    selectedAnswers.value[questionId] = null;
}
</script>

<template>
    <Head :title="`Grade Questions - ${event.title}`" />

    <div class="px-5 py-6">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold">{{ event.title }}</h1>
            <p class="mt-2 text-sm text-gray-600">Grade Questions</p>
            <p class="mt-1 text-sm text-gray-500">{{ gradedCount }} of {{ totalCount }} question{{ totalCount !== 1 ? 's' : '' }} graded</p>
        </div>

        <AlertError v-if="saveError" :errors="[saveError]" />
        <AlertSuccess v-if="saveSuccess" :message="saveSuccess" />

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Loop through questions -->
            <div v-for="(question, index) in questions" :key="question.id" class="rounded-lg border p-6" :class="{ 'border-green-300 bg-green-50/50': question.is_graded }">
                <div class="mb-4 flex items-start justify-between">
                    <h3 class="flex-1 font-medium">{{ index + 1 }}. {{ question.question_text }}</h3>
                    <CheckCircle v-if="question.is_graded" class="ml-2 size-5 shrink-0 text-green-600" />
                </div>

                <div class="space-y-3">
                    <Label :for="`question-${question.id}`" class="text-sm text-gray-600"> Select the correct answer: </Label>

                    <div class="space-y-2">
                        <label
                            v-for="answer in question.answers"
                            :key="answer.id"
                            class="flex cursor-pointer items-center rounded-lg border p-3 transition hover:bg-gray-50"
                            :class="{
                                'border-blue-500 bg-blue-50': selectedAnswers[question.id] === answer.id,
                            }"
                        >
                            <input type="radio" :name="`question_${question.id}`" :value="answer.id" :checked="selectedAnswers[question.id] === answer.id" class="mr-3" @change="selectedAnswers[question.id] = answer.id" />
                            <span>{{ answer.answer_text }}</span>
                        </label>
                    </div>

                    <Button v-if="selectedAnswers[question.id]" type="button" variant="ghost" size="sm" class="text-gray-600" @click="clearQuestion(question.id)"> Clear answer </Button>
                </div>

                <p v-if="question.graded_at" class="mt-3 text-xs text-gray-500">Graded {{ new Date(question.graded_at).toLocaleString() }}</p>
            </div>

            <!-- Submit button -->
            <div class="sticky bottom-20 space-y-2">
                <Button type="submit" class="w-full" :disabled="processing || !hasChanges" :loading="processing">
                    {{ hasChanges ? 'Save Grades' : 'No changes to save' }}
                </Button>
            </div>
        </form>
    </div>
</template>
