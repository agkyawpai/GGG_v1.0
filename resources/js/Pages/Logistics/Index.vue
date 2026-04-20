<script setup>
import { computed, h, ref } from 'vue';
import {
    useVueTable,
    createColumnHelper,
    getCoreRowModel,
    getFilteredRowModel,
    getSortedRowModel,
    FlexRender,
} from '@tanstack/vue-table';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/Layouts/AppLayout.vue';
import TypeBadge from '@/Components/TypeBadge.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Dialog, DialogContent, DialogHeader,
    DialogTitle, DialogFooter,
} from '@/components/ui/dialog';
import {
    Table, TableBody, TableCell, TableHead,
    TableHeader, TableRow, TableEmpty,
} from '@/components/ui/table';
import { UserPlus, ChevronsUpDown, ChevronUp, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
    orders:    Array,
    bikers:    Array,
    townships: Array,
});

// ── filter refs ───────────────────────────────────────────────────────────────

const filterBiker    = ref('');
const filterTownship = ref('');
const filterStatus   = ref('');

// Pre-filter before TanStack so dropdowns compose correctly.
const filtered = computed(() => {
    return props.orders.filter((o) => {
        if (filterBiker.value    && String(o.biker_id)    !== filterBiker.value)    return false;
        if (filterTownship.value && String(o.township_id) !== filterTownship.value) return false;
        if (filterStatus.value   && o.status              !== filterStatus.value)   return false;
        return true;
    });
});

// ── inline assign modal ───────────────────────────────────────────────────────

const selectedOrder   = ref(null);
const showAssignModal = ref(false);

const assignForm = useForm({
    biker_id:    '',
    township_id: '',
});

function openAssign(order) {
    selectedOrder.value      = order;
    assignForm.biker_id      = order.biker_id    ? String(order.biker_id)    : '';
    assignForm.township_id   = order.township_id ? String(order.township_id) : '';
    showAssignModal.value    = true;
}

function submitAssign() {
    assignForm.patch(route('logistics.assign', selectedOrder.value.ulid), {
        onSuccess: () => {
            showAssignModal.value = false;
            toast.success('Biker assigned', {
                description: `Order #${selectedOrder.value.ulid}`,
            });
        },
    });
}

// ── columns ───────────────────────────────────────────────────────────────────

const col = createColumnHelper();

const columns = [
    col.accessor('ulid', {
        id:     'ulid',
        header: 'Order',
        cell:   ({ getValue, row }) =>
            h('a', {
                href:  route('orders.show', getValue()),
                class: 'font-mono text-xs text-purple-600 hover:underline',
            }, getValue()),
    }),

    col.accessor('type', {
        id:     'type',
        header: 'Type',
        cell:   ({ getValue }) => h(TypeBadge, { type: getValue() }),
    }),

    col.accessor((row) => row.biker?.name ?? '', {
        id:     'biker',
        header: 'Biker',
        cell:   ({ getValue }) =>
            h('span', {
                class: getValue() ? 'text-sm text-gray-800' : 'text-sm text-gray-400 italic',
            }, getValue() || 'Unassigned'),
    }),

    col.accessor((row) => row.township?.name ?? '', {
        id:     'township',
        header: 'Township',
        cell:   ({ getValue }) =>
            h('span', { class: 'text-sm text-gray-700' }, getValue() || '—'),
    }),

    col.accessor('status', {
        id:     'status',
        header: 'Status',
        cell:   ({ getValue }) => h(StatusBadge, { status: getValue() }),
    }),

    col.accessor('ordered_at', {
        id:        'ordered_at',
        header:    'Ordered At',
        sortingFn: 'datetime',
        cell:      ({ getValue }) => {
            const v = getValue();
            if (!v) return '—';
            return h('span', { class: 'text-xs text-gray-500 tabular-nums' },
                new Date(v).toLocaleDateString('en-GB', {
                    day: '2-digit', month: 'short', year: 'numeric',
                }),
            );
        },
    }),

    col.accessor('cod_amount', {
        id:     'cod',
        header: 'COD',
        cell:   ({ getValue }) =>
            h('span', { class: 'tabular-nums text-sm font-medium text-gray-900' },
                `${Number(getValue()).toLocaleString()} MMK`,
            ),
    }),

    // Actions column — not sortable, not part of data
    col.display({
        id:     'actions',
        header: '',
        cell:   ({ row }) =>
            h(Button, {
                size:    'sm',
                variant: 'outline',
                class:   'gap-1.5 text-xs',
                onClick: () => openAssign(row.original),
            }, () => [
                h(UserPlus, { class: 'w-3.5 h-3.5' }),
                row.original.biker_id ? 'Reassign' : 'Assign',
            ]),
    }),
];

// ── table instance ────────────────────────────────────────────────────────────

const table = useVueTable({
    get data() { return filtered.value; },
    columns,
    getCoreRowModel:     getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getSortedRowModel:   getSortedRowModel(),
});

// ── helpers ───────────────────────────────────────────────────────────────────

// Dedupe the bikers/townships that actually appear in the current order list
// so the filter dropdowns only show relevant options.
const activeBikers = computed(() =>
    props.bikers.filter((b) => props.orders.some((o) => o.biker_id === b.id)),
);

const activeTownships = computed(() =>
    props.townships.filter((t) => props.orders.some((o) => o.township_id === t.id)),
);
</script>

<template>
    <AppLayout>
        <div class="p-6 space-y-6">

            <!-- Page heading -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Logistics</h1>
                    <p class="mt-0.5 text-sm text-gray-400">
                        Active orders — pending, assigned, in transit
                    </p>
                </div>
                <span class="text-sm text-gray-400 tabular-nums">
                    {{ filtered.length }} order{{ filtered.length === 1 ? '' : 's' }}
                </span>
            </div>

            <!-- Filter toolbar -->
            <div class="flex flex-wrap items-center gap-3">

                <!-- Biker filter -->
                <div class="flex items-center gap-1.5">
                    <label for="filter-biker" class="text-xs text-gray-500 shrink-0">Biker</label>
                    <select
                        id="filter-biker"
                        v-model="filterBiker"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    >
                        <option value="">All bikers</option>
                        <option
                            v-for="b in activeBikers"
                            :key="b.id"
                            :value="String(b.id)"
                        >
                            {{ b.name }}
                        </option>
                    </select>
                </div>

                <!-- Township filter -->
                <div class="flex items-center gap-1.5">
                    <label for="filter-township" class="text-xs text-gray-500 shrink-0">Township</label>
                    <select
                        id="filter-township"
                        v-model="filterTownship"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    >
                        <option value="">All townships</option>
                        <option
                            v-for="t in activeTownships"
                            :key="t.id"
                            :value="String(t.id)"
                        >
                            {{ t.name }}
                        </option>
                    </select>
                </div>

                <!-- Status filter -->
                <div class="flex items-center gap-1.5">
                    <label for="filter-status" class="text-xs text-gray-500 shrink-0">Status</label>
                    <select
                        id="filter-status"
                        v-model="filterStatus"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    >
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="assigned">Assigned</option>
                        <option value="in_transit">In Transit</option>
                    </select>
                </div>

                <!-- Clear filters -->
                <button
                    v-if="filterBiker || filterTownship || filterStatus"
                    type="button"
                    @click="filterBiker = ''; filterTownship = ''; filterStatus = ''"
                    class="text-xs text-gray-400 hover:text-gray-600 underline underline-offset-2 transition-colors"
                >
                    Clear filters
                </button>

            </div>

            <!-- Table -->
            <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow
                            v-for="headerGroup in table.getHeaderGroups()"
                            :key="headerGroup.id"
                            class="bg-gray-50 hover:bg-gray-50"
                        >
                            <TableHead
                                v-for="header in headerGroup.headers"
                                :key="header.id"
                                scope="col"
                                :class="[
                                    'text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap',
                                    header.column.getCanSort() ? 'cursor-pointer select-none' : '',
                                ]"
                                @click="header.column.getToggleSortingHandler()?.($event)"
                            >
                                <div class="flex items-center gap-1">
                                    <FlexRender
                                        v-if="!header.isPlaceholder"
                                        :render="header.column.columnDef.header"
                                        :props="header.getContext()"
                                    />
                                    <span v-if="header.column.getCanSort()" class="text-gray-300">
                                        <ChevronUp
                                            v-if="header.column.getIsSorted() === 'asc'"
                                            class="w-3.5 h-3.5 text-purple-500"
                                        />
                                        <ChevronDown
                                            v-else-if="header.column.getIsSorted() === 'desc'"
                                            class="w-3.5 h-3.5 text-purple-500"
                                        />
                                        <ChevronsUpDown v-else class="w-3.5 h-3.5" />
                                    </span>
                                </div>
                            </TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <template v-if="table.getRowModel().rows.length">
                            <TableRow
                                v-for="row in table.getRowModel().rows"
                                :key="row.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <TableCell
                                    v-for="cell in row.getVisibleCells()"
                                    :key="cell.id"
                                    class="py-3 text-sm"
                                >
                                    <FlexRender
                                        :render="cell.column.columnDef.cell"
                                        :props="cell.getContext()"
                                    />
                                </TableCell>
                            </TableRow>
                        </template>

                        <TableEmpty v-else :colspan="columns.length">
                            <div class="py-12 text-center text-sm text-gray-400">
                                No active orders match the current filters.
                            </div>
                        </TableEmpty>
                    </TableBody>
                </Table>
            </div>

        </div>

        <!-- ── Assign Biker modal ─────────────────────────────── -->
        <Dialog :open="showAssignModal" @update:open="showAssignModal = $event">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <UserPlus class="w-4 h-4 text-blue-500" />
                        {{ selectedOrder?.biker_id ? 'Reassign Biker' : 'Assign Biker' }}
                    </DialogTitle>
                </DialogHeader>

                <p v-if="selectedOrder" class="text-xs text-gray-400 font-mono -mt-1">
                    Order #{{ selectedOrder.ulid }}
                </p>

                <form @submit.prevent="submitAssign" class="space-y-4 pt-1">
                    <div>
                        <Label for="modal-biker" class="mb-1.5 block">Biker</Label>
                        <select
                            id="modal-biker"
                            v-model="assignForm.biker_id"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select biker…</option>
                            <option
                                v-for="b in bikers"
                                :key="b.id"
                                :value="String(b.id)"
                            >
                                {{ b.name }}
                                <template v-if="b.phone"> — {{ b.phone }}</template>
                            </option>
                        </select>
                        <p v-if="assignForm.errors.biker_id" class="mt-1 text-xs text-red-500">
                            {{ assignForm.errors.biker_id }}
                        </p>
                    </div>

                    <div>
                        <Label for="modal-township" class="mb-1.5 block">Township</Label>
                        <select
                            id="modal-township"
                            v-model="assignForm.township_id"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select township…</option>
                            <option
                                v-for="t in townships"
                                :key="t.id"
                                :value="String(t.id)"
                            >
                                {{ t.name }}
                            </option>
                        </select>
                        <p v-if="assignForm.errors.township_id" class="mt-1 text-xs text-red-500">
                            {{ assignForm.errors.township_id }}
                        </p>
                    </div>

                    <DialogFooter class="pt-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="showAssignModal = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            :disabled="assignForm.processing"
                            class="bg-blue-600 hover:bg-blue-700 text-white"
                        >
                            {{ assignForm.processing ? 'Saving…' : 'Confirm' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>
