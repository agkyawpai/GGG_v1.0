export function useStatusColor() {
    const statusClasses = {
        pending:    'bg-yellow-100 text-yellow-800',
        assigned:   'bg-blue-100 text-blue-800',
        in_transit: 'bg-orange-100 text-orange-800',
        delivered:  'bg-green-100 text-green-800',
        returned:   'bg-red-100 text-red-800',
        settled:    'bg-gray-100 text-gray-700',
    };

    const statusLabel = {
        pending:    'Pending',
        assigned:   'Assigned',
        in_transit: 'In Transit',
        delivered:  'Delivered',
        returned:   'Returned',
        settled:    'Settled',
    };

    function colorFor(status) {
        return statusClasses[status] ?? 'bg-gray-100 text-gray-600';
    }

    function labelFor(status) {
        return statusLabel[status] ?? status;
    }

    return { colorFor, labelFor };
}
