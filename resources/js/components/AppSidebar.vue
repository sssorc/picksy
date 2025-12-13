<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { edit as event } from '@/routes/event';
import { index as help } from '@/routes/help';
import { index as publish } from '@/routes/publish';
import { index as questions } from '@/routes/questions';
import { type EventStatus, type NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { PhCalendarDot, PhListNumbers, PhPaperPlaneTilt } from '@phosphor-icons/vue';
import { HelpCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';
import NavFooter from './NavFooter.vue';

const page = usePage();

const eventStatus = computed(() => page.props.eventStatus as EventStatus | null);

const mainNavItems = computed((): NavItem[] => {
    const status = eventStatus.value;

    // Default status if no event status
    if (!status) {
        return [
            { title: 'Event', href: event(), status: 'open', icon: PhCalendarDot },
            { title: 'Questions', href: questions(), status: 'locked', icon: PhListNumbers },
            { title: 'Publish', href: publish(), status: 'locked', icon: PhPaperPlaneTilt },
        ];
    }

    // Event status: no event created = open, event created = complete
    const eventItemStatus = status.hasEvent ? 'complete' : 'open';

    // Questions status: no event = locked, event but no questions = open, questions saved = complete
    let questionsItemStatus: 'open' | 'complete' | 'locked';
    if (!status.hasEvent) {
        questionsItemStatus = 'locked';
    } else if (!status.hasQuestions) {
        questionsItemStatus = 'open';
    } else {
        questionsItemStatus = 'complete';
    }

    // Publish status: no questions = locked, questions saved = open, event published = complete
    let publishItemStatus: 'open' | 'complete' | 'locked';
    if (!status.hasQuestions) {
        publishItemStatus = 'locked';
    } else if (!status.isPublished) {
        publishItemStatus = 'open';
    } else {
        publishItemStatus = 'complete';
    }

    return [
        { title: 'Event', href: event(), status: eventItemStatus, icon: PhCalendarDot },
        { title: 'Questions', href: questions(), status: questionsItemStatus, icon: PhListNumbers },
        { title: 'Publish', href: publish(), status: publishItemStatus, icon: PhPaperPlaneTilt },
    ];
});

const footerNavItems: NavItem[] = [
    {
        title: 'Help',
        href: help(),
        icon: HelpCircle,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <a href="/">
                            <AppLogo />
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
