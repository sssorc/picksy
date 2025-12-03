<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ClipboardList, Trophy } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const eventSlug = computed(() => page.props.event?.slug);

// Check if we're in preview mode based on the current URL
const isPreview = computed(() => page.url.startsWith('/preview/'));

interface NavItem {
    href: string;
    label: string;
    icon: any;
    matchPattern: string;
}

const navItems = computed<NavItem[]>(() => {
    const prefix = isPreview.value ? `/preview/${eventSlug.value}` : `/${eventSlug.value}`;

    return [
        {
            href: `${prefix}/picks`,
            label: 'Picks',
            icon: ClipboardList,
            matchPattern: '/picks',
        },
        {
            href: `${prefix}/leaderboard`,
            label: 'Leaderboard',
            icon: Trophy,
            matchPattern: '/leaderboard',
        },
    ];
});

const isActive = (item: NavItem) => {
    return page.url.includes(item.matchPattern);
};
</script>

<template>
    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-gray-200 bg-white shadow-lg">
        <div class="mx-auto flex max-w-screen-md">
            <Link v-for="item in navItems" :key="item.href" :href="item.href" class="flex flex-1 flex-col items-center justify-center gap-1 py-3 text-sm transition-colors" :class="isActive(item) ? 'text-blue-600' : 'text-gray-600 hover:text-gray-900'">
                <component :is="item.icon" :class="['h-6 w-6', isActive(item) ? 'stroke-blue-600' : 'stroke-gray-600']" />
                <span class="font-medium">{{ item.label }}</span>
            </Link>
        </div>
    </nav>
</template>
