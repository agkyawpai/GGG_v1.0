<template>
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-30 flex bg-white border-t border-gray-200">
        <Link
            v-for="item in nav"
            :key="item.href"
            :href="item.href"
            class="flex flex-1 flex-col items-center justify-center gap-1 py-2 text-xs text-gray-400 transition-colors"
            :class="{ 'text-purple-600': $page.url.startsWith(item.path) }"
        >
            <component
                :is="item.icon"
                class="w-5 h-5"
                :class="$page.url.startsWith(item.path) ? 'text-purple-600' : 'text-gray-400'"
            />
            <span>{{ item.label }}</span>
        </Link>
    </nav>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { LayoutDashboard, Plus, Truck, Banknote, Warehouse } from 'lucide-vue-next';
import { t } from '@/Composables/useLocale';

const nav = computed(() => [
    { label: t('dashboard'),   href: route('dashboard'),         path: new URL(route('dashboard')).pathname,         icon: LayoutDashboard },
    { label: t('new_order'),   href: route('orders.create'),     path: new URL(route('orders.create')).pathname,     icon: Plus },
    { label: t('logistics'),   href: route('logistics.index'),   path: new URL(route('logistics.index')).pathname,   icon: Truck },
    { label: t('settlements'), href: route('settlements.index'), path: new URL(route('settlements.index')).pathname,  icon: Banknote },
    { label: t('warehouse'),   href: route('warehouse.index'),   path: new URL(route('warehouse.index')).pathname,   icon: Warehouse },
]);
</script>
