<script setup lang="ts">
import Question from '@/components/question/Question.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const questions = ref([]);
const saving = ref(false);
const saveError = ref('');
const saveSuccess = ref('');

function addQuestion() {
    // TODO
}

async function saveQuestions() {
    // TODO
}
</script>
<template>
    <Head title="Questions" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <h2 class="text-2xl font-bold">Questions</h2>
			
			<AlertError v-if="saveError" :errors="[saveError]" />
            <AlertError v-else-if="formErrors" title="Error saving event" :errors="formErrors" />
            <AlertSuccess v-else-if="saveSuccess" :message="saveSuccess" />

            <div class="space-y-4">
                <Question v-for="question in questions" :key="question.id" :index="question.order" :question="question"> </Question>
                <button v-if="questions.length < 16" type="button" @click="addQuestion">Add new question</button>
            </div>
			div.flex.justify-end
				<Button type="submit" :disabled="saving">
					{{ saving ? 'Saving...' : 'Save Questions' }}
				</Button>
			</div>
        </div>
    </AppLayout>
</template>
