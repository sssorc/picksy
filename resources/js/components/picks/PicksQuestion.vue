<script setup lang="ts">
interface Answer {
    id: number;
    answer_text: string;
    is_correct?: boolean;
}

interface Props {
    questionText: string;
    answers: Answer[];
    questionNumber?: number;
    mode: 'submit' | 'view';
    // For view mode
    selectedAnswerId?: number | null;
    isGraded?: boolean;
    // For submit mode
    questionId?: number;
    modelValue?: number;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const handleAnswerSelect = (answerId: number) => {
    if (props.mode === 'submit') {
        emit('update:modelValue', answerId);
    }
};
</script>

<template>
    <div class="rounded-lg border p-6">
        <!-- Question header -->
        <h3 class="mb-4 font-medium">
            <template v-if="mode === 'submit' && questionNumber"> {{ questionNumber }}. {{ questionText }} </template>
            <template v-else>
                {{ questionText }}
            </template>
        </h3>

        <!-- Submit mode: Radio buttons -->
        <template v-if="mode === 'submit'">
            <div class="space-y-2">
                <label v-for="answer in answers" :key="answer.id" class="flex cursor-pointer items-center rounded-lg border p-3 transition hover:bg-gray-50">
                    <input type="radio" :name="`question_${questionId}`" :value="answer.id" :checked="modelValue === answer.id" class="mr-3" @change="handleAnswerSelect(answer.id)" />
                    <span>{{ answer.answer_text }}</span>
                </label>
            </div>
        </template>

        <!-- View mode: Display selected and correct answers -->
        <template v-else>
            <div class="space-y-2">
                <div
                    v-for="answer in answers"
                    :key="answer.id"
                    :class="[
                        'rounded-lg border p-3',
                        {
                            'border-green-500 bg-green-50': isGraded && answer.is_correct,
                            'border-red-500 bg-red-50': isGraded && answer.id === selectedAnswerId && !answer.is_correct,
                            'border-blue-500 bg-blue-50': answer.id === selectedAnswerId && !isGraded,
                            'border-gray-200': answer.id !== selectedAnswerId && !answer.is_correct,
                        },
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <span>{{ answer.answer_text }}</span>
                        <span class="text-sm">
                            <template v-if="answer.id === selectedAnswerId">
                                <span v-if="isGraded" :class="answer.is_correct ? 'text-green-600' : 'text-red-600'">
                                    {{ answer.is_correct ? '✓ Your pick (Correct!)' : '✗ Your pick (Incorrect)' }}
                                </span>
                                <span v-else class="text-blue-600"> Your pick </span>
                            </template>
                            <template v-else-if="isGraded && answer.is_correct">
                                <span class="text-green-600"> ✓ Correct answer </span>
                            </template>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Grading status -->
            <p v-if="!isGraded" class="mt-3 text-sm text-gray-500">Not graded yet</p>
        </template>
    </div>
</template>
