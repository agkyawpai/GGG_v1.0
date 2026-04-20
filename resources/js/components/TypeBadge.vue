<script setup>
defineProps({ type: String });
</script>

<template>
    <span
        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
        :class="type === 'type_a' ? 'bg-purple-100 text-purple-800' : 'bg-teal-100 text-teal-800'"
    >
        {{ type === 'type_a' ? 'Type A' : 'Type B' }}
    </span>
</template>
