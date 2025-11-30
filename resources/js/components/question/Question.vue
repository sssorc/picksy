<script setup lang="ts">
import { ref } from 'vue';
import QuestionAnswer from './QuestionAnswer.vue';

const questionText = ref('question test goes here yes please?');
const props = defineProps({
    index: {
        type: Number,
        required: true,
    },
    question: {
        type: Object,
        required: true,
    },
});

const answers = ref([
    {
        id: 1,
        answer_text: 'first anwer',
    },
    {
        id: 2,
        answer_text: 'second answer',
    },
]);

async function addAnswer() {
    // TODO
}
</script>
<template>
    <div class="flex items-start gap-4 rounded-xl border bg-card px-4 py-4 text-card-foreground shadow-sm">
        <div class="flex shrink-0 items-center justify-center text-xl font-extrabold">{{ props.index }}.</div>
        <div class="flex-1">
            <div class="group">
                <input type="text" class="w-full border border-transparent px-2 text-xl font-bold group-hover:border-input" :value="questionText" />
            </div>
            <slot />
            <div v-if="answers.length > 0" class="mt-2 w-sm space-y-2 pl-1">
                <QuestionAnswer v-for="answer in answers" :key="answer.id" :answer="answer" />
                <button v-if="answers.length < 4" type="button" class="flex cursor-pointer items-center justify-center bg-stone-100 px-1" @click="addAnswer">Add Answer</button>
            </div>
        </div>
    </div>
</template>
