<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { t } from '@/Composables/useLocale';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { useAutoAnimate } from '@formkit/auto-animate/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TypeBadge from '@/components/TypeBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Card, CardContent, CardHeader, CardTitle, CardFooter,
} from '@/components/ui/card';
import { Warehouse, Package, Store, Send, Clock } from 'lucide-vue-next';

const props = defineProps({
    items: Array,
});

// ── reactive item list ────────────────────────────────────────────────────────

// Local copy so Echo can push new arrivals without a full page reload.
const items = ref([...props.items]);

// ── AutoAnimate ───────────────────────────────────────────────────────────────

const [gridRef] = useAutoAnimate();

// ── dispatch ──────────────────────────────────────────────────────────────────

// Tracks which card's button shows a loading spinner.
const dispatchingId = ref(null);

function dispatch(item) {
    if (dispatchingId.value !== null) return;
    dispatchingId.value = item.id;

    router.patch(route('warehouse.dispatch', item.id), {}, {
        onSuccess: () => {
            items.value = items.value.filter((i) => i.id !== item.id);
            toast.success(t('item_dispatched'), {
                description: `${t('order_hash')}${item.order?.ulid} ${t('item_dispatched_desc')}`,
            });
        },
        onError: () => {
            toast.error(t('dispatch_failed'));
        },
        onFinish: () => {
            dispatchingId.value = null;
        },
        preserveScroll: true,
    });
}

// ── helpers ───────────────────────────────────────────────────────────────────

function fmtDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('en-GB', {
        day: '2-digit', month: 'short', year: 'numeric',
    });
}

const warehouseStatusLabel = {
    get in_transit() { return t('in_transit'); },
    get received()   { return t('received'); },
    get stored()     { return t('stored'); },
    get dispatched() { return t('dispatched'); },
};

// ── real-time ─────────────────────────────────────────────────────────────────

onMounted(() => {
    window.Echo.private('orders')
        .listen('ItemReceived', (e) => {
            // Prevent duplicates if the server reloads the page too.
            const exists = items.value.some((i) => i.id === e.item.id);
            if (!exists) items.value.unshift(e.item);
        });
});

onUnmounted(() => {
    window.Echo.leave('orders');
});
</script>

<template>
    <AppLayout>
        <div class="p-6 space-y-6">

            <!-- Page heading -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ t('warehouse') }}</h1>
                    <p class="mt-0.5 text-sm text-gray-400">{{ t('warehouse_subtitle') }}</p>
                </div>
                <span class="text-sm text-gray-400 tabular-nums">
                    {{ items.length }} {{ t('items_count') }}
                </span>
            </div>

            <!-- Card grid -->
            <div
                ref="gridRef"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Card
                    v-for="item in items"
                    :key="item.id"
                    class="flex flex-col"
                >
                    <!-- Card header: ULID + type badge -->
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between gap-2">
                            <a
                                :href="route('orders.show', item.order?.ulid)"
                                class="font-mono text-xs text-purple-600 hover:underline truncate"
                            >
                                {{ item.order?.ulid }}
                            </a>
                            <TypeBadge type="type_b" />
                        </div>
                    </CardHeader>

                    <!-- Card body -->
                    <CardContent class="flex-1 space-y-3 pt-0">

                        <!-- Shop -->
                        <div class="flex items-start gap-2">
                            <Store class="w-3.5 h-3.5 mt-0.5 shrink-0 text-gray-400" />
                            <span class="text-sm font-medium text-gray-800 leading-tight">
                                {{ item.order?.shop?.name ?? '—' }}
                            </span>
                        </div>

                        <!-- Item name -->
                        <div class="flex items-start gap-2">
                            <Package class="w-3.5 h-3.5 mt-0.5 shrink-0 text-gray-400" />
                            <span class="text-sm text-gray-700 leading-tight">
                                {{ item.order?.item_name ?? '—' }}
                            </span>
                        </div>

                        <!-- Received date -->
                        <div class="flex items-center gap-2">
                            <Clock class="w-3.5 h-3.5 shrink-0 text-gray-400" />
                            <span class="text-xs text-gray-500">
                                {{ fmtDate(item.received_at) }}
                            </span>
                        </div>

                        <!-- Shipped via badge -->
                        <div>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="item.shipped_via === 'bus'
                                    ? 'bg-blue-100 text-blue-700'
                                    : 'bg-amber-100 text-amber-700'"
                            >
                                {{ item.shipped_via === 'bus' ? t('bus') : t('express') }}
                            </span>
                        </div>

                        <!-- Received date chip -->
                        <div>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-500">
                                {{ fmtDate(item.received_at) }}
                            </span>
                        </div>

                    </CardContent>

                    <!-- Card footer: Dispatch button -->
                    <CardFooter class="pt-3">
                        <Button
                            class="w-full gap-2 bg-teal-600 hover:bg-teal-700 text-white min-h-[44px]"
                            :disabled="dispatchingId !== null"
                            @click="dispatch(item)"
                        >
                            <Send
                                class="w-4 h-4"
                                :class="dispatchingId === item.id ? 'animate-pulse' : ''"
                            />
                            {{ dispatchingId === item.id ? t('dispatching') : t('dispatch') }}
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- Empty state -->
            <div
                v-if="items.length === 0"
                class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-white py-20 text-center"
            >
                <Warehouse class="w-10 h-10 text-gray-300 mb-3" />
                <p class="text-sm font-medium text-gray-500">{{ t('no_items_warehouse') }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ t('warehouse_empty_subtitle') }}</p>
            </div>

        </div>
    </AppLayout>
</template>
