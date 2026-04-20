import { onMounted, onUnmounted } from 'vue';

export function useEchoOrders(callbacks = {}) {
    onMounted(() => {
        window.Echo.private('orders')
            .listen('OrderRegistered', (e) => callbacks.onRegistered?.(e))
            .listen('BikerAssigned', (e) => callbacks.onBikerAssigned?.(e))
            .listen('OrderStatusChanged', (e) => callbacks.onStatusChanged?.(e))
            .listen('SettlementRecorded', (e) => callbacks.onSettled?.(e));
    });

    onUnmounted(() => {
        window.Echo.leave('orders');
    });
}
