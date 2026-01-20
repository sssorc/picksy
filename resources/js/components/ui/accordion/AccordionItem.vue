<script setup lang="ts">
import type { AccordionItemProps } from 'reka-ui';
import { AccordionItem } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<AccordionItemProps & { class?: HTMLAttributes['class'] }>();
</script>

<template>
    <AccordionItem data-slot="accordion-item" v-bind="props" :class="cn('border-b', props.class)">
        <slot />
    </AccordionItem>
</template>
