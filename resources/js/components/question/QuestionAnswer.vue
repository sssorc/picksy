<script setup lang="ts">
const props = defineProps<{
    answer: {
        id: number;
        answer_text: string;
    };
}>();

async function deleteAnswer() {
    // TODO
}
</script>
<template>
    <div class="group relative flex ring-1 ring-stone-200">
        <button type="button" class="absolute top-0 right-full bottom-0 flex cursor-move items-center justify-center bg-stone-100 px-1 opacity-0 group-hover:opacity-100" tabindex="-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor">
                <path d="M5.7 9.3 3 12m0 0 2.7 2.7M3 12h18M9.3 5.7 12 3m0 0 2.7 2.7M12 3v18m2.7-2.7L12 21m0 0-2.7-2.7m9-9L21 12m0 0-2.7 2.7" />
            </svg>
        </button>
        <input type="text" class="grow rounded-none border border-transparent px-2 py-1 group-hover:border-input" :value="props.answer.answer_text" placeholder="Enter answer text..." />
        <button class="flex cursor-pointer items-center justify-center bg-stone-100 px-1 opacity-0 group-hover:opacity-100" tabindex="-1" @click="deleteAnswer">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor">
                <path
                    d="m18 9-.84 8.398c-.127 1.273-.19 1.909-.48 2.39a2.5 2.5 0 0 1-1.075.973C15.098 21 14.46 21 13.18 21h-2.36c-1.279 0-1.918 0-2.425-.24a2.5 2.5 0 0 1-1.076-.973c-.288-.48-.352-1.116-.48-2.389L6 9m7.5 6.5v-5m-3 5v-5m-6-4h4.615m0 0 .386-2.672c.112-.486.516-.828.98-.828h3.038c.464 0 .867.342.98.828l.386 2.672m-5.77 0h5.77m0 0H19.5"
                />
            </svg>
        </button>
    </div>
</template>
