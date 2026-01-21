<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Input } from '@/components/ui/input';
import { login, register } from '@/routes';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const appName = page.props.name as string;
const isAuthenticated = computed(() => !!page.props.auth?.user);

const searchQuery = ref('');

interface FAQ {
    id: string;
    question: string;
    answer: string;
}

const faqs: FAQ[] = [
    {
        id: '1',
        question: 'What is grading?',
        answer: 'Grading is the process of selecting the correct answer for a question. This is done by the event creator or a designated person at picksy.com/<your-event>/grade. Questions can be graded in bulk or one at a time.',
    },
    {
        id: '2',
        question: 'My event already started, can I still update questions?',
        answer: 'You can still add, delete, and update questions up until the first person sumbits their picks. If they already have the Picks page open while you make changes, they will be prompted to re-submit if they try to submit with old questions.',
    },
    {
        id: '3',
        question: 'How long do I have to grade questions?',
        answer: 'You have 30 days after the event start date to grade questions. All event data will be deleted after that period.',
    },
    {
        id: '4',
        question: 'Can I have more than one event?',
        answer: 'Currently you can only have a single event. However you can create and publish a new event after the current one is deleted.',
    },
    {
        id: '5',
        question: '',
        answer: '',
    },
    {
        id: '6',
        question: '',
        answer: '',
    },
    {
        id: '7',
        question: 'Can I password-protect my event?',
        answer: 'Yes! You can set an optional password that participants must enter before accessing your event. This helps keep your event private and ensures only invited guests can participate.',
    },
    {
        id: '8',
        question: 'How do participants submit their picks?',
        answer: 'Participants visit your event\'s unique URL, enter the event password (if required), provide their name, and then select their answers to all questions. They must answer all questions before submitting.',
    },
    {
        id: '9',
        question: 'When do submissions close?',
        answer: 'Submissions are accepted until the event start date/time you specify. After that, participants can no longer submit picks. Additionally, once any question is graded, no new submissions are accepted.',
    },
    {
        id: '10',
        question: 'How long is my event stored?',
        answer: 'Events and all associated data (questions, picks, participants) are automatically deleted 60 days after your event date. This helps keep our system clean and protects participant privacy.',
    },
    {
        id: '11',
        question: 'Can I preview my event before it goes live?',
        answer: 'Yes! After publishing, you can use the preview link to see exactly what participants will see. You cannot submit actual picks in preview mode.',
    },
    {
        id: '12',
        question: 'How does the leaderboard work?',
        answer: 'The leaderboard displays all participants ranked by the number of questions they answered correctly. It updates in real-time as you grade questions, creating excitement as the rankings change throughout your event.',
    },
];

const filteredFaqs = computed(() => {
    if (!searchQuery.value.trim()) {
        return faqs;
    }

    const query = searchQuery.value.toLowerCase();
    return faqs.filter((faq) => faq.question.toLowerCase().includes(query));
});
</script>

<template>
    <Head title="Help & FAQ" />

    <div class="min-h-dvh bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-950 dark:to-zinc-900">
        <!-- Navigation -->
        <header class="fixed top-0 z-50 w-full border-b border-zinc-200/50 bg-white/80 backdrop-blur-lg dark:border-zinc-800/50 dark:bg-zinc-950/80">
            <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
                <Link href="/" class="flex items-center gap-2.5">
                    <AppLogoIcon class="size-8 text-zinc-900 dark:text-white" />
                    <span class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ appName }}</span>
                </Link>

                <div v-if="!isAuthenticated" class="flex items-center gap-3">
                    <Button variant="ghost" as-child>
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="register()">Get Started</Link>
                    </Button>
                </div>
                <div v-else>
                    <Button as-child>
                        <Link href="/dashboard">Dashboard</Link>
                    </Button>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="pt-32 pb-12">
            <div class="mx-auto max-w-4xl px-6">
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">Help & Frequently Asked Questions</h1>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400">Find answers to common questions about creating and managing your event prediction games.</p>
                </div>
            </div>
        </section>

        <!-- Search Bar -->
        <section class="pb-12">
            <div class="mx-auto max-w-4xl px-6">
                <div class="relative">
                    <Input v-model="searchQuery" type="text" placeholder="Search questions..." class="h-14 w-full rounded-xl border-zinc-300 pl-12 text-base shadow-sm dark:border-zinc-700" />
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute top-1/2 left-4 size-5 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </section>

        <!-- FAQ Accordions -->
        <section class="pb-24">
            <div class="mx-auto max-w-4xl px-6">
                <div v-if="filteredFaqs.length > 0">
                    <Accordion type="single" collapsible class="w-full space-y-4">
                        <AccordionItem v-for="faq in filteredFaqs" :key="faq.id" :value="faq.id" class="rounded-xl border border-zinc-200 bg-white px-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            <AccordionTrigger class="py-5 text-left text-base font-semibold text-zinc-900 hover:no-underline dark:text-white">
                                {{ faq.question }}
                            </AccordionTrigger>
                            <AccordionContent class="pb-5 pt-1 text-zinc-600 dark:text-zinc-400">
                                {{ faq.answer }}
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </div>
                <div v-else class="rounded-xl border border-zinc-200 bg-white p-12 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-zinc-600 dark:text-zinc-400">No questions found matching "{{ searchQuery }}"</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 bg-white py-12 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto max-w-6xl px-6">
                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                    <div class="flex items-center gap-2">
                        <AppLogoIcon class="size-6 text-zinc-900 dark:text-white" />
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ appName }}</span>
                    </div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-500">&copy; {{ new Date().getFullYear() }} {{ appName }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
