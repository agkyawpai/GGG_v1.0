<script setup>
import { useStatusColor } from '@/Composables/useStatusColor';
const { colorFor, labelFor } = useStatusColor();
defineProps({ status: String });
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold"
        :class="colorFor(status)"
    >
        {{ labelFor(status) }}
    </span>
</template>
