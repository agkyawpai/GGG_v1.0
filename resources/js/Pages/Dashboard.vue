<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import OrderCard from '@/components/OrderCard.vue';
import { useOrderBoard } from '@/Composables/useOrderBoard';
import { t } from '@/Composables/useLocale';
import { ref } from 'vue';
import { useAutoAnimate } from '@formkit/auto-animate/vue';

const props = defineProps({ ordersByStatus: Object });
const { board, STATUSES } = useOrderBoard(props.ordersByStatus);
const activeTab = ref('pending');


const columnColors = {
    pending:    'border-t-yellow-400',
    assigned:   'border-t-blue-400',
    in_transit: 'border-t-orange-400',
    delivered:  'border-t-green-400',
    settled:    'border-t-gray-400',
};

// One animate ref per column
const [pendingRef]    = useAutoAnimate();
const [assignedRef]   = useAutoAnimate();
const [inTransitRef]  = useAutoAnimate();
const [deliveredRef]  = useAutoAnimate();
const [settledRef]    = useAutoAnimate();

const colRefs = {
    pending:    pendingRef,
    assigned:   assignedRef,
    in_transit: inTransitRef,
    delivered:  deliveredRef,
    settled:    settledRef,
};
</script>

<template>
    <AppLayout>
        <div class="p-4 md:p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4 md:mb-6">{{ t('order_board') }}</h1>

            <!-- Mobile tab bar -->
            <div class="md:hidden flex overflow-x-auto gap-1 mb-4 pb-1 scrollbar-none">
                <button
                    v-for="status in STATUSES"
                    :key="status"
                    @click="activeTab = status"
                    class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-colors border"
                    :class="activeTab === status
                        ? 'bg-purple-600 text-white border-purple-600'
                        : 'bg-white text-gray-500 border-gray-200 hover:border-gray-300'"
                >
                    {{ t(status) }}
                    <span
                        class="text-xs rounded-full px-1.5 py-0.5 font-semibold"
                        :class="activeTab === status ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'"
                    >
                        {{ board[status]?.length ?? 0 }}
                    </span>
                </button>
            </div>

            <!-- Mobile: single active column -->
            <div class="md:hidden">
                <div
                    v-for="status in STATUSES"
                    :key="status"
                    v-show="activeTab === status"
                    class="space-y-2"
                >
                    <OrderCard
                        v-for="order in board[status]"
                        :key="order.id"
                        :order="order"
                        class="w-full"
                    />
                    <p v-if="!board[status]?.length" class="text-xs text-gray-400 text-center pt-4">
                        {{ t('no_orders') }}
                    </p>
                </div>
            </div>

            <!-- Desktop: kanban columns -->
            <div class="hidden md:flex gap-4 overflow-x-auto pb-4">
                <div
                    v-for="status in STATUSES"
                    :key="status"
                    class="flex-shrink-0 w-64 flex flex-col rounded-xl border border-gray-200 bg-white border-t-4 shadow-sm"
                    :class="columnColors[status]"
                >
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <span class="font-semibold text-sm text-gray-700">{{ t(status) }}</span>
                        <span class="text-xs bg-gray-100 text-gray-600 rounded-full px-2 py-0.5 font-medium">
                            {{ board[status]?.length ?? 0 }}
                        </span>
                    </div>
                    <div :ref="colRefs[status]" class="flex-1 p-3 space-y-2 min-h-32">
                        <OrderCard
                            v-for="order in board[status]"
                            :key="order.id"
                            :order="order"
                        />
                        <p v-if="!board[status]?.length" class="text-xs text-gray-400 text-center pt-4">
                            {{ t('no_orders') }}
                        </p>
                    </div>
                </div>
            </div><!-- end desktop kanban -->
        </div>
    </AppLayout>
</template>
