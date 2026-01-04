<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { PhCheckCircle, PhLockSimple } from '@phosphor-icons/vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Get Started</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title" :class="{ 'cursor-not-allowed': item.status === 'locked' }">
                <SidebarMenuButton as-child :is-active="urlIsActive(item.href, page.url)" :tooltip="item.title">
                    <Link :href="item.href" :class="{ 'pointer-events-none opacity-70': item.status === 'locked' }">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                        <PhCheckCircle v-if="item.status === 'complete'" class="ml-auto text-green-700" />
                        <PhLockSimple v-if="item.status === 'locked'" class="ml-auto" />
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
