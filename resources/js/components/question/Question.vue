<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import QuestionAnswer from './QuestionAnswer.vue';

const props = defineProps<{
    index: number;
    question: {
        id: number | null;
        question_text: string;
        order: number;
        is_tiebreaker: boolean;
        answers: Array<{
            id: number | null;
            answer_text: string;
            order: number;
        }>;
    };
}>();

const emit = defineEmits<{
    update: [question: typeof props.question];
    delete: [];
}>();

const questionText = ref(props.question.question_text);
const answers = ref([...props.question.answers]);

// Watch for prop changes (e.g., after save)
watch(
    () => props.question,
    (newQuestion) => {
        questionText.value = newQuestion.question_text;
        answers.value = [...newQuestion.answers];
    },
    { deep: true },
);

const maxAnswers = computed(() => (props.question.is_tiebreaker ? 0 : 6));
const canAddAnswer = computed(() => !props.question.is_tiebreaker && answers.value.length < maxAnswers.value);

function updateQuestionText(event: Event) {
    questionText.value = (event.target as HTMLInputElement).value;
    emitUpdate();
}

function addAnswer() {
    if (!canAddAnswer.value) return;

    answers.value.push({
        id: null,
        answer_text: '',
        order: answers.value.length,
    });
    emitUpdate();
}

function updateAnswer(index: number, updatedAnswer: any) {
    answers.value[index] = { ...updatedAnswer, order: index };
    emitUpdate();
}

function deleteAnswer(index: number) {
    answers.value.splice(index, 1);
    // Reorder remaining answers
    answers.value.forEach((a, i) => {
        a.order = i;
    });
    emitUpdate();
}

function deleteQuestion() {
    if (confirm('Are you sure you want to delete this question?')) {
        emit('delete');
    }
}

function emitUpdate() {
    emit('update', {
        ...props.question,
        question_text: questionText.value,
        answers: answers.value,
    });
}
</script>

<template>
    <div class="flex items-start gap-4 rounded-xl border bg-card px-4 py-4 text-card-foreground shadow-sm">
        <div class="flex shrink-0 items-center justify-center text-xl font-extrabold">{{ props.index }}.</div>
        <div class="flex-1">
            <div class="group relative">
                <input type="text" class="w-full border border-transparent px-2 text-xl font-bold group-hover:border-input" :value="questionText" placeholder="Enter question..." @input="updateQuestionText" />
                <button type="button" class="absolute top-0 right-0 flex size-8 items-center justify-center text-muted-foreground opacity-0 transition group-hover:opacity-100 hover:text-destructive" title="Delete question" @click="deleteQuestion">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor">
                        <path
                            d="m18 9-.84 8.398c-.127 1.273-.19 1.909-.48 2.39a2.5 2.5 0 0 1-1.075.973C15.098 21 14.46 21 13.18 21h-2.36c-1.279 0-1.918 0-2.425-.24a2.5 2.5 0 0 1-1.076-.973c-.288-.48-.352-1.116-.48-2.389L6 9m7.5 6.5v-5m-3 5v-5m-6-4h4.615m0 0 .386-2.672c.112-.486.516-.828.98-.828h3.038c.464 0 .867.342.98.828l.386 2.672m-5.77 0h5.77m0 0H19.5"
                        />
                    </svg>
                </button>
            </div>
            <slot />
            <div v-if="!question.is_tiebreaker" class="mt-2 w-sm space-y-2 pl-1">
                <QuestionAnswer v-for="(answer, index) in answers" :key="answer.id || `new-${index}`" :answer="answer" @update="updateAnswer(index, $event)" @delete="deleteAnswer(index)" />
                <button
                    v-if="canAddAnswer"
                    type="button"
                    class="flex w-full cursor-pointer items-center justify-center border-2 border-dashed border-muted-foreground/25 bg-background px-2 py-2 text-xs text-muted-foreground transition hover:border-muted-foreground/50 hover:text-foreground"
                    @click="addAnswer"
                >
                    + Add Answer
                </button>
            </div>
        </div>
    </div>
</template>
