<script setup lang="ts">
import BottomNav from '@/components/BottomNav.vue';

interface Props {
    showBottomNav?: boolean;
}

withDefaults(defineProps<Props>(), {
    showBottomNav: true,
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 pb-20">
        <slot />
        <BottomNav v-if="showBottomNav" />
    </div>
</template>
