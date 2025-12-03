<script setup lang="ts">
import {
  SelectContent,
  SelectPortal,
  SelectRoot,
  SelectTrigger,
  SelectValue,
  type SelectRootEmits,
  type SelectRootProps,
} from 'reka-ui'
import { ChevronDown } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

interface Props extends SelectRootProps {
  placeholder?: string
}

interface Emits extends SelectRootEmits {}

defineProps<Props>()
const emit = defineEmits<Emits>()
</script>

<template>
  <SelectRoot v-bind="$props" @update:model-value="emit('update:modelValue', $event)">
    <SelectTrigger
      :class="cn(
        'placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 items-center justify-between rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
        'focus-visible:border-ring',
        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
      )"
    >
      <SelectValue :placeholder="placeholder" />
      <ChevronDown class="h-4 w-4 opacity-50" />
    </SelectTrigger>

    <SelectPortal>
      <SelectContent
        :class="cn(
          'relative z-50 max-h-[--reka-select-content-available-height] min-w-[--reka-select-trigger-width] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md',
          'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
          'data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2'
        )"
        position="popper"
        :side-offset="4"
      >
        <div class="p-1">
          <slot />
        </div>
      </SelectContent>
    </SelectPortal>
  </SelectRoot>
</template>
