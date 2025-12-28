<script setup lang="ts">
import { PhTrash } from '@phosphor-icons/vue';
import { computed, ref, watch } from 'vue';
import QuestionAnswer from './QuestionAnswer.vue';

const props = withDefaults(
    defineProps<{
        index: number | string;
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
        expanded?: boolean;
    }>(),
    {
        expanded: true,
    },
);

const emit = defineEmits<{
    update: [question: typeof props.question];
    delete: [];
    'update:expanded': [value: boolean];
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

// Drag and drop for reordering answers
const draggedAnswerIndex = ref<number | null>(null);

function handleAnswerDragStart(index: number) {
    draggedAnswerIndex.value = index;
}

function handleAnswerDragOver(event: DragEvent, index: number) {
    event.preventDefault();
    if (draggedAnswerIndex.value === null || draggedAnswerIndex.value === index) return;

    // Reorder the array
    const draggedAnswer = answers.value[draggedAnswerIndex.value];
    answers.value.splice(draggedAnswerIndex.value, 1);
    answers.value.splice(index, 0, draggedAnswer);

    // Update dragged index to the new position
    draggedAnswerIndex.value = index;
}

function handleAnswerDrop() {
    draggedAnswerIndex.value = null;
    // Reorder all answers
    answers.value.forEach((a, i) => {
        a.order = i;
    });
    emitUpdate();
}

function emitUpdate() {
    emit('update', {
        ...props.question,
        question_text: questionText.value,
        answers: answers.value,
    });
}

function toggleExpanded() {
    emit('update:expanded', !props.expanded);
}
</script>

<template>
    <div class="group relative flex items-start gap-4 rounded-xl border bg-card px-4 py-4 text-card-foreground shadow-sm">
        <button type="button" @click="deleteQuestion" aria-label="Delete question" class="absolute top-4 right-4 opacity-0 transition-opacity duration-200 group-hover:opacity-100 hover:text-destructive">
            <PhTrash />
        </button>

        <div class="flex shrink-0 items-center gap-2">
            <button v-if="!question.is_tiebreaker" type="button" class="flex size-6 items-center justify-center text-muted-foreground transition hover:text-foreground" :title="expanded ? 'Collapse' : 'Expand'" @click="toggleExpanded">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor" class="transition-transform" :class="{ 'rotate-180': !expanded }">
                    <path d="m6 9 6 6 6-6" />
                </svg>
            </button>
            <div class="flex items-center justify-center text-xl font-extrabold">{{ props.index }}.</div>
        </div>
        <div class="flex-1 pr-20">
            <div class="group/text relative">
                <input type="text" class="w-full border border-transparent px-2 text-xl font-bold group-hover/text:border-input" :value="questionText" placeholder="Enter question..." @input="updateQuestionText" />
            </div>
            <div v-if="!question.is_tiebreaker" v-show="expanded" class="mt-4 w-sm space-y-2.5 pl-1">
                <QuestionAnswer
                    v-for="(answer, index) in answers"
                    :key="answer.id || `new-${index}`"
                    :answer="answer"
                    :is-dragging="draggedAnswerIndex === index"
                    @update="updateAnswer(index, $event)"
                    @delete="deleteAnswer(index)"
                    @drag-start="handleAnswerDragStart(index)"
                    @drag-over="handleAnswerDragOver($event, index)"
                    @drop="handleAnswerDrop"
                    class=""
                />
                <button v-if="canAddAnswer" type="button" class="mt-2 cursor-pointer text-sm font-semibold text-sky-600 transition hover:underline" @click="addAnswer">+ Add answer</button>
            </div>
        </div>
    </div>
</template>
