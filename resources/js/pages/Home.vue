<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const appName = page.props.name as string;
</script>

<template>
    <Head title="Wedding Prop Bets & Event Predictions" />

    <div class="min-h-dvh bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-950 dark:to-zinc-900">
        <!-- Navigation -->
        <header class="fixed top-0 z-50 w-full border-b border-zinc-200/50 bg-white/80 backdrop-blur-lg dark:border-zinc-800/50 dark:bg-zinc-950/80">
            <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
                <Link href="/" class="flex items-center gap-2.5">
                    <AppLogoIcon class="size-8 text-zinc-900 dark:text-white" />
                    <span class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ appName }}</span>
                </Link>

                <div class="flex items-center gap-3">
                    <Button variant="ghost" as-child>
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="register()">Get Started</Link>
                    </Button>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-32 pb-20 lg:pt-40 lg:pb-32">
            <!-- Background decoration -->
            <div class="absolute inset-0 -z-10">
                <div class="absolute top-20 left-1/4 size-72 rounded-full bg-rose-200/30 blur-3xl dark:bg-rose-900/20" />
                <div class="absolute top-40 right-1/4 size-96 rounded-full bg-amber-200/30 blur-3xl dark:bg-amber-900/20" />
            </div>

            <div class="mx-auto max-w-6xl px-6">
                <div class="mx-auto max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-4 py-1.5 text-sm font-medium text-zinc-600 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                        </span>
                        Perfect for weddings & special events
                    </div>

                    <h1 class="bg-gradient-to-br from-zinc-900 via-zinc-700 to-zinc-900 bg-clip-text text-5xl font-bold tracking-tight text-transparent sm:text-6xl lg:text-7xl dark:from-white dark:via-zinc-300 dark:to-white">
                        Make Your Event
                        <span class="block text-rose-600 dark:text-rose-400">Unforgettable</span>
                    </h1>

                    <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-zinc-600 dark:text-zinc-400">Create fun prediction games for your wedding, party, or special event. Guests compete to guess the outcomes &mdash; from first dance songs to speech lengths.</p>

                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <Button size="lg" class="h-12 px-8 text-base" as-child>
                            <Link :href="register()">Create Your Event</Link>
                        </Button>
                        <Button variant="outline" size="lg" class="h-12 px-8 text-base" as-child>
                            <Link href="#how-it-works">See How It Works</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="how-it-works" class="border-t border-zinc-200 bg-white py-24 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto max-w-6xl px-6">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">How It Works</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400">Three simple steps to create memorable moments at your event</p>
                </div>

                <div class="mt-16 grid gap-8 md:grid-cols-3">
                    <!-- Step 1 -->
                    <div class="group relative rounded-2xl border border-zinc-200 bg-zinc-50 p-8 transition-all hover:border-rose-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-950 dark:hover:border-rose-900">
                        <div class="mb-6 flex size-14 items-center justify-center rounded-xl bg-gradient-to-br from-rose-500 to-rose-600 text-2xl font-bold text-white shadow-lg shadow-rose-500/25">1</div>
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Create Questions</h3>
                        <p class="mt-3 text-zinc-600 dark:text-zinc-400">Add up to 16 multiple choice questions about your event. Choose from popular templates or write your own.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="group relative rounded-2xl border border-zinc-200 bg-zinc-50 p-8 transition-all hover:border-amber-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-950 dark:hover:border-amber-900">
                        <div class="mb-6 flex size-14 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 text-2xl font-bold text-white shadow-lg shadow-amber-500/25">2</div>
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Share With Guests</h3>
                        <p class="mt-3 text-zinc-600 dark:text-zinc-400">Get a unique link and QR code to share. Guests submit their predictions before the event starts.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="group relative rounded-2xl border border-zinc-200 bg-zinc-50 p-8 transition-all hover:border-emerald-200 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-950 dark:hover:border-emerald-900">
                        <div class="mb-6 flex size-14 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-2xl font-bold text-white shadow-lg shadow-emerald-500/25">3</div>
                        <h3 class="text-xl font-semibold text-zinc-900 dark:text-white">Live Leaderboard</h3>
                        <p class="mt-3 text-zinc-600 dark:text-zinc-400">Grade answers as they happen and watch the leaderboard update in real-time. Crown the winner!</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Example Questions Section -->
        <section class="border-t border-zinc-200 py-24 dark:border-zinc-800">
            <div class="mx-auto max-w-6xl px-6">
                <div class="grid items-center gap-16 lg:grid-cols-2">
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">Questions Your Guests Will Love</h2>
                        <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">Get creative with predictions about the big day. Here are some crowd favorites:</p>

                        <ul class="mt-8 space-y-4">
                            <li class="flex items-start gap-3">
                                <span class="mt-1 flex size-6 shrink-0 items-center justify-center rounded-full bg-rose-100 text-sm text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">?</span>
                                <span class="text-zinc-700 dark:text-zinc-300">How long will the best man's speech be?</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 flex size-6 shrink-0 items-center justify-center rounded-full bg-rose-100 text-sm text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">?</span>
                                <span class="text-zinc-700 dark:text-zinc-300">What song will play for the first dance?</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 flex size-6 shrink-0 items-center justify-center rounded-full bg-rose-100 text-sm text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">?</span>
                                <span class="text-zinc-700 dark:text-zinc-300">Who will cry first during the ceremony?</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 flex size-6 shrink-0 items-center justify-center rounded-full bg-rose-100 text-sm text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">?</span>
                                <span class="text-zinc-700 dark:text-zinc-300">How many times will "I do" be said?</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 flex size-6 shrink-0 items-center justify-center rounded-full bg-rose-100 text-sm text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">?</span>
                                <span class="text-zinc-700 dark:text-zinc-300">Who will catch the bouquet?</span>
                            </li>
                        </ul>
                    </div>

                    <div class="relative">
                        <div class="absolute -inset-4 rounded-3xl bg-gradient-to-br from-rose-200 via-amber-100 to-emerald-200 opacity-30 blur-2xl dark:from-rose-900 dark:via-amber-900 dark:to-emerald-900" />
                        <div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                            <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-950">
                                <div class="flex items-center gap-2">
                                    <div class="size-3 rounded-full bg-rose-500" />
                                    <div class="size-3 rounded-full bg-amber-500" />
                                    <div class="size-3 rounded-full bg-emerald-500" />
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="rounded-lg bg-zinc-100 p-4 dark:bg-zinc-800">
                                        <p class="font-medium text-zinc-900 dark:text-white">How long will the ceremony last?</p>
                                        <div class="mt-3 space-y-2">
                                            <div class="flex items-center gap-2">
                                                <div class="size-4 rounded-full border-2 border-zinc-300 dark:border-zinc-600" />
                                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Under 20 minutes</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="size-4 rounded-full border-2 border-rose-500 bg-rose-500" />
                                                <span class="text-sm font-medium text-zinc-900 dark:text-white">20-30 minutes</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="size-4 rounded-full border-2 border-zinc-300 dark:border-zinc-600" />
                                                <span class="text-sm text-zinc-600 dark:text-zinc-400">30-45 minutes</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="size-4 rounded-full border-2 border-zinc-300 dark:border-zinc-600" />
                                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Over 45 minutes</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="border-t border-zinc-200 bg-zinc-900 py-24 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto max-w-6xl px-6 text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Ready to Make Your Event Memorable?</h2>
                <p class="mx-auto mt-4 max-w-xl text-lg text-zinc-400">Create your prediction game in minutes. Your guests will talk about it for years.</p>
                <div class="mt-10">
                    <Button size="lg" class="h-14 bg-white px-10 text-base text-zinc-900 hover:bg-zinc-100" as-child>
                        <Link :href="register()">Get Started Free</Link>
                    </Button>
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
