<script setup>
import { Link } from '@inertiajs/vue3';
import { MapPin, Truck } from 'lucide-vue-next';
import TypeBadge from './TypeBadge.vue';

defineProps({ order: Object });
</script>

<template>
    <Link
        :href="route('orders.show', order.ulid)"
        class="block rounded-xl border border-gray-200 bg-white p-3 shadow-sm hover:shadow-md transition-shadow cursor-pointer"
    >
        <div class="flex items-center justify-between mb-1.5">
            <span class="font-mono text-xs text-gray-500 truncate">{{ order.ulid }}</span>
            <TypeBadge :type="order.type" />
        </div>
        <div class="flex items-center gap-1.5 text-sm font-medium text-gray-800 truncate">
            <MapPin v-if="order.type === 'type_a'" class="w-3.5 h-3.5 text-purple-500 shrink-0" />
            <Truck v-else class="w-3.5 h-3.5 text-teal-500 shrink-0" />
            {{ order.shop?.name }}
        </div>
        <div class="mt-1 text-xs text-gray-500 truncate">{{ order.customer?.name }}</div>
        <div class="mt-2 text-sm font-semibold text-gray-900">
            {{ Number(order.cod_amount).toLocaleString() }} MMK
        </div>
    </Link>
</template>
