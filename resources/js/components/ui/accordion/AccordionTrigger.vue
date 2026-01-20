<script setup lang="ts">
import type { AccordionTriggerProps } from 'reka-ui';
import { AccordionHeader, AccordionTrigger } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<AccordionTriggerProps & { class?: HTMLAttributes['class'] }>();
</script>

<template>
    <AccordionHeader class="flex">
        <AccordionTrigger v-bind="props" data-slot="accordion-trigger" :class="cn('flex flex-1 items-center justify-between font-medium transition-all [&[data-state=open]>svg]:rotate-180', props.class)">
            <slot />
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0 transition-transform duration-200">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </AccordionTrigger>
    </AccordionHeader>
</template>
