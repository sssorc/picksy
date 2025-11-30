<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Circle, CircleCheckBig, LockKeyhole } from 'lucide-vue-next';
import { computed, type Component } from 'vue';

const props = defineProps<{
    items: NavItem[];
}>();

const page = usePage();

const getIconForStatus = (status?: 'open' | 'complete' | 'locked'): Component => {
    switch (status) {
        case 'complete':
            return CircleCheckBig;
        case 'locked':
            return LockKeyhole;
        case 'open':
        default:
            return Circle;
    }
};

const itemsWithIcons = computed(() =>
    props.items.map((item) => ({
        ...item,
        icon: item.icon || getIconForStatus(item.status),
    })),
);
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Get Started</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in itemsWithIcons" :key="item.title" :class="{ 'cursor-not-allowed': item.status === 'locked' }">
                <SidebarMenuButton as-child :is-active="urlIsActive(item.href, page.url)" :tooltip="item.title">
                    <Link :href="item.href" :class="{ 'pointer-events-none': item.status === 'locked' }">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
