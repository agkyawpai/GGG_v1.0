<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';
import { computed, watch, onMounted, ref } from 'vue';
import { LayoutDashboard, PackagePlus, Banknote, Truck, Warehouse, LogOut, Menu } from 'lucide-vue-next';
import { Sheet, SheetContent, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import BottomNav from '@/components/BottomNav.vue';
import LanguagePickerModal from '@/components/LanguagePickerModal.vue';
import LanguageToggle from '@/components/LanguageToggle.vue';
import { setLocale, t } from '@/Composables/useLocale';

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
const mobileOpen = ref(false);

onMounted(() => {
    const serverLocale = page.props.locale;
    if (serverLocale) setLocale(serverLocale);
});

function logout() {
    router.post(route('logout'));
}

const nav = computed(() => [
    { label: t('dashboard'),   href: route('dashboard'),         path: new URL(route('dashboard')).pathname,        icon: LayoutDashboard },
    { label: t('new_order'),   href: route('orders.create'),     path: new URL(route('orders.create')).pathname,    icon: PackagePlus },
    { label: t('logistics'),   href: route('logistics.index'),   path: new URL(route('logistics.index')).pathname,  icon: Truck },
    { label: t('settlements'), href: route('settlements.index'), path: new URL(route('settlements.index')).pathname, icon: Banknote },
    { label: t('warehouse'),   href: route('warehouse.index'),   path: new URL(route('warehouse.index')).pathname,  icon: Warehouse },
]);
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Mobile topbar -->
        <header class="md:hidden fixed top-0 inset-x-0 z-30 flex items-center justify-between px-4 h-14 bg-white border-b border-gray-200">
            <div>
                <img src="/images/ggg_logo.png" alt="GGG" class="h-8 w-auto" />
            </div>
            <button @click="mobileOpen = true" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                <Menu class="w-5 h-5" />
            </button>
        </header>

        <!-- Mobile Sheet -->
        <Sheet :open="mobileOpen" @update:open="mobileOpen = $event">
            <SheetContent side="left" class="w-64 p-0 flex flex-col">
                <SheetHeader class="px-5 py-4 border-b border-gray-100">
                    <SheetTitle class="text-left">
                        <img src="/images/ggg_logo.png" alt="GGG" class="h-10 w-auto" />
                    </SheetTitle>
                </SheetHeader>
                <nav class="flex-1 p-3 space-y-0.5">
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        @click="mobileOpen = false"
                        class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                        :class="{ 'bg-purple-50 text-purple-700 font-medium': $page.url.startsWith(item.path) }"
                    >
                        <component :is="item.icon" class="w-4 h-4 shrink-0" />
                        {{ item.label }}
                    </Link>
                </nav>
                <div class="p-3 border-t border-gray-100">
                    <div class="flex items-center gap-2.5 px-3 py-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-700 truncate">{{ user?.name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ user?.email }}</p>
                        </div>
                        <LanguageToggle />
                        <button @click="logout" title="Sign out" class="shrink-0 text-gray-400 hover:text-red-500 transition-colors">
                            <LogOut class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </SheetContent>
        </Sheet>

        <!-- Sidebar -->
        <aside class="hidden md:flex w-56 shrink-0 flex-col bg-white border-r border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <img src="/images/ggg_logo.png" alt="GGG" class="h-10 w-auto" />
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
                    <LanguageToggle />
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
        <main class="flex-1 overflow-auto pt-14 md:pt-0 pb-16 md:pb-0">
            <slot />
        </main>

        <BottomNav />
        <Toaster position="bottom-right" rich-colors />
        <LanguagePickerModal />
    </div>
</template>
