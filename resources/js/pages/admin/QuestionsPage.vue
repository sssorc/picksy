<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/QuestionController';
import AlertError from '@/components/AlertError.vue';
import AlertSuccess from '@/components/AlertSuccess.vue';
import Question from '@/components/question/Question.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

interface Answer {
    id: number | null;
    answer_text: string;
    order: number;
}

interface Question {
    id: number | null;
    question_text: string;
    order: number;
    is_tiebreaker: boolean;
    answers: Answer[];
}

const props = defineProps<{
    event: {
        id: number;
        questions: Array<{
            id: number;
            question_text: string;
            order: number;
            is_tiebreaker: boolean;
            answers: Array<{
                id: number;
                answer_text: string;
                order: number;
            }>;
        }>;
    };
    popularQuestions?: any[];
}>();

// Separate regular questions and tiebreaker
const regularQuestions = ref<Question[]>(props.event.questions.filter((q) => !q.is_tiebreaker).map((q, index) => ({ ...q, order: index })));

const tiebreakerQuestion = ref<Question | null>(props.event.questions.find((q) => q.is_tiebreaker) || null);

// Track expanded state for each question
const expandedStates = ref<boolean[]>(regularQuestions.value.map(() => true));

const saving = ref(false);
const saveError = ref('');
const saveSuccess = ref('');
const formErrors = ref<string[]>([]);

const questionCount = computed(() => regularQuestions.value.length);

const disableSave = computed(() => {
    const hasInvalidRegularQuestions = regularQuestions.value.some((q) => q.question_text.trim() === '' || q.answers.some((a) => a.answer_text.trim() === ''));
    const hasInvalidTiebreaker = tiebreakerQuestion.value && tiebreakerQuestion.value.question_text.trim() === '';
    return hasInvalidRegularQuestions || hasInvalidTiebreaker;
});

function addQuestion() {
    if (questionCount.value >= 16) return;

    regularQuestions.value.push({
        id: null,
        question_text: '',
        order: regularQuestions.value.length,
        is_tiebreaker: false,
        answers: [
            { id: null, answer_text: '', order: 0 },
            { id: null, answer_text: '', order: 1 },
        ],
    });
    // Add expanded state for the new question
    expandedStates.value.push(true);
}

function addTiebreaker() {
    if (tiebreakerQuestion.value) return;

    tiebreakerQuestion.value = {
        id: null,
        question_text: '',
        order: 0,
        is_tiebreaker: true,
        answers: [],
    };
}

function updateTiebreaker(updatedQuestion: Question) {
    tiebreakerQuestion.value = { ...updatedQuestion };
}

function deleteTiebreaker() {
    tiebreakerQuestion.value = null;
}

function updateQuestion(index: number, updatedQuestion: Question) {
    regularQuestions.value[index] = { ...updatedQuestion, order: index };
}

function deleteQuestion(index: number) {
    regularQuestions.value.splice(index, 1);
    expandedStates.value.splice(index, 1);
    // Reorder remaining questions
    regularQuestions.value.forEach((q, i) => {
        q.order = i;
    });
}

function collapseAll() {
    expandedStates.value = expandedStates.value.map(() => false);
}

function expandAll() {
    expandedStates.value = expandedStates.value.map(() => true);
}

function updateExpandedState(index: number, value: boolean) {
    expandedStates.value[index] = value;
}

async function saveQuestions() {
    saveError.value = '';
    saveSuccess.value = '';
    formErrors.value = [];
    saving.value = true;

    try {
        const response = await axios.post(store.url(), {
            questions: regularQuestions.value,
            tiebreaker: tiebreakerQuestion.value,
        });

        saveSuccess.value = response.data.message;
        // Reload to get updated IDs and shared data (including eventStatus)
        router.reload({ only: ['event'] });
    } catch (error: any) {
        if (error.response?.data?.errors) {
            formErrors.value = Object.values(error.response.data.errors).flat() as string[];
        } else {
            saveError.value = error.response?.data?.message || 'An error occurred while saving';
        }
        console.error(error);
    } finally {
        saving.value = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}
</script>

<template>
    <Head title="Questions" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h2 class="text-2xl font-bold">Questions</h2>

            <AlertError v-if="saveError" :errors="[saveError]" />
            <AlertError v-else-if="formErrors.length > 0" title="Error saving questions" :errors="formErrors" />
            <AlertSuccess v-else-if="saveSuccess" :message="saveSuccess" />

            <div class="flex items-end justify-between">
                <div class="text-sm text-foreground">{{ questionCount }} of 16 questions created</div>
                <div class="flex justify-end gap-4">
                    <button type="button" class="cursor-pointer text-sm text-foreground hover:underline" @click="collapseAll">Collapse all</button>
                    <button type="button" class="cursor-pointer text-sm text-foreground hover:underline" @click="expandAll">Expand all</button>
                </div>
            </div>

            <div class="space-y-4">
                <Question
                    v-for="(question, index) in regularQuestions"
                    :key="question.id || `new-${index}`"
                    :index="index + 1"
                    :question="question"
                    :expanded="expandedStates[index]"
                    @update="updateQuestion(index, $event)"
                    @delete="deleteQuestion(index)"
                    @update:expanded="updateExpandedState(index, $event)"
                />

                <Question v-if="tiebreakerQuestion" :key="tiebreakerQuestion.id || 'tiebreaker'" index="TB" :question="tiebreakerQuestion" @update="updateTiebreaker($event)" @delete="deleteTiebreaker"> </Question>

                <div class="flex gap-2">
                    <Button variant="secondary" v-if="questionCount < 16" @click="addQuestion">+ Add Question</Button>
                    <Button variant="outline" v-if="!tiebreakerQuestion" @click="addTiebreaker">+ Add Tiebreaker</Button>
                </div>
            </div>

            <div class="flex justify-end">
                <Button type="button" :disabled="saving || disableSave" @click="saveQuestions">
                    {{ saving ? 'Saving...' : 'Save Questions' }}
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
