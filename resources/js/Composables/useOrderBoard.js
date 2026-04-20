import { reactive } from 'vue';
import { useEchoOrders } from './useEchoOrders';

const STATUSES = ['pending', 'assigned', 'in_transit', 'delivered', 'settled'];

export function useOrderBoard(initialOrdersByStatus) {
    const board = reactive(
        Object.fromEntries(STATUSES.map((s) => [s, initialOrdersByStatus[s] ?? []]))
    );

    function addOrder(order) {
        board[order.status]?.unshift(order);
    }

    function moveOrder(orderId, newStatus, updatedOrder) {
        for (const col of STATUSES) {
            const idx = board[col].findIndex((o) => o.id === orderId);
            if (idx !== -1) {
                board[col].splice(idx, 1);
                break;
            }
        }
        board[newStatus]?.unshift(updatedOrder);
    }

    useEchoOrders({
        onRegistered: ({ order }) => addOrder(order),
        onBikerAssigned: ({ order }) => moveOrder(order.id, order.status, order),
        onStatusChanged: ({ order_id, new_status, order }) => moveOrder(order_id, new_status, order),
    });

    return { board, STATUSES };
}
