<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';
import { computed, watch } from 'vue';
import { LayoutDashboard, PackagePlus, Banknote, Truck, Warehouse, LogOut } from 'lucide-vue-next';

const page = usePage();

watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;
        if (flash.type === 'success') toast.success(flash.message);
        else if (flash.type === 'error') toast.error(flash.message);
        else toast(flash.message);
    },
    { immediate: true }
);

const user = computed(() => page.props.auth?.user);

function logout() {
    router.post(route('logout'));
}

const nav = [
    { label: 'Dashboard',   href: route('dashboard'),         path: new URL(route('dashboard')).pathname,        icon: LayoutDashboard },
    { label: 'New Order',   href: route('orders.create'),     path: new URL(route('orders.create')).pathname,    icon: PackagePlus },
    { label: 'Logistics',   href: route('logistics.index'),   path: new URL(route('logistics.index')).pathname,  icon: Truck },
    { label: 'Settlements', href: route('settlements.index'), path: new URL(route('settlements.index')).pathname, icon: Banknote },
    { label: 'Warehouse',   href: route('warehouse.index'),   path: new URL(route('warehouse.index')).pathname,  icon: Warehouse },
];
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside class="w-56 shrink-0 flex flex-col bg-white border-r border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <span class="text-xl font-bold tracking-tight text-purple-700">GGG</span>
                <span class="ml-1.5 text-xs text-gray-400">Go · Grab · Get</span>
            </div>
            <nav class="flex-1 p-3 space-y-0.5">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                    :class="{ 'bg-purple-50 text-purple-700 font-medium': $page.url.startsWith(item.path) }"
                >
                    <component :is="item.icon" class="w-4 h-4 shrink-0" />
                    {{ item.label }}
                </Link>
            </nav>

            <!-- User + logout -->
            <div class="p-3 border-t border-gray-100">
                <div class="flex items-center gap-2.5 px-3 py-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-700 truncate">{{ user?.name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ user?.email }}</p>
                    </div>
                    <button
                        @click="logout"
                        title="Sign out"
                        class="shrink-0 text-gray-400 hover:text-red-500 transition-colors"
                    >
                        <LogOut class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 overflow-auto">
            <slot />
        </main>

        <Toaster position="bottom-right" rich-colors />
    </div>
</template>
