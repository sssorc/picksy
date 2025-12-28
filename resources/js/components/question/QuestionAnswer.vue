<script setup lang="ts">
import { PhDotsSixVertical, PhTrash } from '@phosphor-icons/vue';
import { ref, watch } from 'vue';

const props = defineProps<{
    answer: {
        id: number | null;
        answer_text: string;
        order: number;
    };
    isDragging?: boolean;
}>();

const emit = defineEmits<{
    update: [answer: typeof props.answer];
    delete: [];
    dragStart: [];
    dragOver: [event: DragEvent];
    drop: [];
}>();

const answerText = ref(props.answer.answer_text);

// Watch for prop changes (e.g., after save)
watch(
    () => props.answer.answer_text,
    (newText) => {
        answerText.value = newText;
    },
);

function updateAnswerText(event: Event) {
    answerText.value = (event.target as HTMLInputElement).value;
    emit('update', {
        ...props.answer,
        answer_text: answerText.value,
    });
}

function deleteAnswer() {
    emit('delete');
}

function handleDragStart(event: DragEvent) {
    emit('dragStart');
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
    }
}

function handleDragOver(event: DragEvent) {
    emit('dragOver', event);
}

function handleDrop(event: DragEvent) {
    event.preventDefault();
    emit('drop');
}
</script>

<template>
    <div draggable="true" class="group/answer relative flex" :class="{ 'opacity-50': isDragging }" @dragstart="handleDragStart" @dragover="handleDragOver" @drop="handleDrop">
        <button type="button" class="absolute top-0 right-full bottom-0 flex cursor-move items-center justify-center opacity-0 group-hover/answer:opacity-100" tabindex="-1" title="Drag to reorder">
            <PhDotsSixVertical size="20" weight="bold" />
        </button>

        <input type="text" class="grow rounded-none border border-input/80 bg-stone-50 px-2 py-1 group-hover/answer:border-input focus:bg-white" :value="answerText" placeholder="Enter answer text..." @input="updateAnswerText" />

        <button type="button" class="ml-2 cursor-pointer opacity-0 group-hover/answer:opacity-100 hover:text-destructive" tabindex="-1" title="Delete answer" @click="deleteAnswer">
            <PhTrash />
        </button>
    </div>
</template>
