<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { computed } from 'vue'
import { cn } from '@/lib/utils'
import { Primitive, type PrimitiveProps } from 'reka-ui'
import { type ButtonVariants, buttonVariants } from '.'

interface Props extends PrimitiveProps {
  variant?: ButtonVariants['variant']
  size?: ButtonVariants['size']
  class?: HTMLAttributes['class']
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
  loading: false,
})

const spinnerClass = computed(() => {
  const lightBgVariants = ['outline', 'secondary', 'ghost', 'link']
  const isLightBg = lightBgVariants.includes(props.variant ?? 'default')
  return isLightBg ? 'text-foreground/60' : 'text-white/70'
})
</script>

<template>
  <Primitive
    data-slot="button"
    :as="as"
    :as-child="asChild"
    :class="cn(buttonVariants({ variant, size }), 'relative', props.class)"
  >
    <span :class="{ 'opacity-0': loading }">
      <slot />
    </span>
	<span v-if="loading" class="absolute left-0 right-0 mx-auto top-1/2 -translate-y-1/2 size-4">
		<svg
      :class="cn('size-4', spinnerClass)"
      style="animation: spin 0.4s linear infinite"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>
	</span>
    
  </Primitive>
</template>
