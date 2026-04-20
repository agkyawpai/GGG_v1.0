<script setup>
import { computed, h, ref } from 'vue';
import {
    useVueTable,
    createColumnHelper,
    getCoreRowModel,
    getFilteredRowModel,
    getSortedRowModel,
    getPaginationRowModel,
    FlexRender,
} from '@tanstack/vue-table';
import AppLayout from '@/Layouts/AppLayout.vue';
import TypeBadge from '@/components/TypeBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell, TableHead,
    TableHeader, TableRow, TableEmpty,
} from '@/components/ui/table';
import { Card, CardContent } from '@/components/ui/card';
import {
    Download, Search, ChevronsUpDown, ChevronUp,
    ChevronDown, SlidersHorizontal,
} from 'lucide-vue-next';

const props = defineProps({
    settlements: Array,
});

// ── filters ─────────────────────────────────────────────────────────────────

const globalFilter     = ref('');
const dateFrom         = ref('');
const dateTo           = ref('');
const columnVisibility = ref({});
const colPickerOpen    = ref(false);
const pagination       = ref({ pageIndex: 0, pageSize: 20 });

// Pre-filter by date range before handing data to TanStack so the
// summary totals always reflect the visible set.
const rangeFiltered = computed(() => {
    if (!dateFrom.value && !dateTo.value) return props.settlements;
    const from = dateFrom.value ? new Date(dateFrom.value) : null;
    const to   = dateTo.value   ? new Date(dateTo.value + 'T23:59:59') : null;
    return props.settlements.filter((s) => {
        const d = new Date(s.settled_at);
        if (from && d < from) return false;
        if (to   && d > to)   return false;
        return true;
    });
});

// ── columns ──────────────────────────────────────────────────────────────────

const col = createColumnHelper();

const columns = [
    col.accessor('settled_at', {
        id:     'date',
        header: 'Date',
        cell:   ({ getValue }) => {
            const v = getValue();
            if (!v) return '—';
            return new Date(v).toLocaleDateString('en-GB', {
                day: '2-digit', month: 'short', year: 'numeric',
            });
        },
        sortingFn: 'datetime',
    }),

    col.accessor((row) => row.order?.ulid ?? '', {
        id:     'ulid',
        header: 'Order',
        cell:   ({ getValue }) =>
            h('span', { class: 'font-mono text-xs text-gray-600' }, getValue()),
    }),

    col.accessor((row) => row.order?.type ?? '', {
        id:     'type',
        header: 'Type',
        cell:   ({ getValue }) => h(TypeBadge, { type: getValue() }),
        enableGlobalFilter: false,
    }),

    col.accessor((row) => row.order?.shop?.name ?? '', {
        id:     'shop',
        header: 'Shop',
        cell:   ({ getValue }) =>
            h('span', { class: 'text-sm text-gray-800' }, getValue()),
    }),

    col.accessor('cod_collected', {
        id:     'cod',
        header: 'COD Collected',
        cell:   ({ getValue }) => fmtMMK(getValue()),
        enableGlobalFilter: false,
    }),

    col.accessor('delivery_fee', {
        id:     'fee',
        header: 'Delivery Fee',
        cell:   ({ getValue }) => fmtMMK(getValue()),
        enableGlobalFilter: false,
    }),

    col.accessor('net_amount', {
        id:     'net',
        header: 'Net Amount',
        cell:   ({ getValue }) => {
            const v = Number(getValue());
            return h(
                'span',
                { class: v >= 0 ? 'font-semibold text-green-600' : 'font-semibold text-red-600' },
                fmtMMK(v),
            );
        },
        enableGlobalFilter: false,
    }),

    col.accessor((row) => row.settled_by?.name ?? '', {
        id:     'settled_by',
        header: 'Settled By',
        cell:   ({ getValue }) =>
            h('span', { class: 'text-sm text-gray-600' }, getValue() || '—'),
    }),
];

// ── table instance ───────────────────────────────────────────────────────────

const table = useVueTable({
    get data()    { return rangeFiltered.value; },
    columns,
    state: {
        get globalFilter()      { return globalFilter.value; },
        get columnVisibility()  { return columnVisibility.value; },
        get pagination()        { return pagination.value; },
    },
    onGlobalFilterChange:     (v) => { globalFilter.value = v; pagination.value.pageIndex = 0; },
    onColumnVisibilityChange: (updater) => {
        columnVisibility.value =
            typeof updater === 'function' ? updater(columnVisibility.value) : updater;
    },
    onPaginationChange: (updater) => {
        pagination.value =
            typeof updater === 'function' ? updater(pagination.value) : updater;
    },
    getCoreRowModel:       getCoreRowModel(),
    getFilteredRowModel:   getFilteredRowModel(),
    getSortedRowModel:     getSortedRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    globalFilterFn:        'includesString',
});

// ── summary (always tracks filtered rows) ────────────────────────────────────

const summary = computed(() => {
    const rows = table.getFilteredRowModel().rows;
    return rows.reduce(
        (acc, row) => {
            acc.cod  += Number(row.original.cod_collected);
            acc.fees += Number(row.original.delivery_fee);
            acc.net  += Number(row.original.net_amount);
            return acc;
        },
        { cod: 0, fees: 0, net: 0 },
    );
});

// ── helpers ──────────────────────────────────────────────────────────────────

function fmtMMK(val) {
    return `${Number(val).toLocaleString()} MMK`;
}

// ── CSV export ───────────────────────────────────────────────────────────────

function csvExport() {
    const headers = ['Date', 'Order ULID', 'Type', 'Shop',
                     'COD Collected', 'Delivery Fee', 'Net Amount', 'Settled By'];

    const rows = table.getFilteredRowModel().rows.map((row) => {
        const s = row.original;
        return [
            s.settled_at
                ? new Date(s.settled_at).toLocaleDateString('en-GB')
                : '',
            s.order?.ulid       ?? '',
            s.order?.type       ?? '',
            s.order?.shop?.name ?? '',
            s.cod_collected,
            s.delivery_fee,
            s.net_amount,
            s.settled_by?.name  ?? '',
        ];
    });

    const csv = [headers, ...rows]
        .map((r) => r.map((v) => `"${String(v).replaceAll('"', '""')}"`).join(','))
        .join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `settlements-${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}
</script>

<template>
    <AppLayout>
        <div class="p-6 space-y-6">

            <!-- Page heading -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Settlements</h1>
            </div>

            <!-- Summary bar -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                <!-- Total COD -->
                <Card>
                    <CardContent class="pt-5 pb-4 px-5">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                            Total COD Collected
                        </p>
                        <p class="text-2xl font-bold text-gray-900 tabular-nums">
                            {{ fmtMMK(summary.cod) }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            {{ table.getFilteredRowModel().rows.length }}
                            {{ table.getFilteredRowModel().rows.length === 1 ? 'settlement' : 'settlements' }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Total Fees -->
                <Card>
                    <CardContent class="pt-5 pb-4 px-5">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                            Total Delivery Fees
                        </p>
                        <p class="text-2xl font-bold text-gray-900 tabular-nums">
                            {{ fmtMMK(summary.fees) }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            across filtered rows
                        </p>
                    </CardContent>
                </Card>

                <!-- Net Amount -->
                <Card>
                    <CardContent class="pt-5 pb-4 px-5">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                            Net Amount
                        </p>
                        <p
                            class="text-2xl font-bold tabular-nums"
                            :class="summary.net >= 0 ? 'text-green-600' : 'text-red-600'"
                        >
                            {{ fmtMMK(summary.net) }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            COD minus all costs
                        </p>
                    </CardContent>
                </Card>

            </div>

            <!-- Toolbar -->
            <div class="flex flex-wrap items-center gap-3">

                <!-- Global search -->
                <div class="relative flex-1 min-w-48">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                    <input
                        :value="globalFilter"
                        @input="globalFilter = $event.target.value"
                        type="text"
                        placeholder="Search orders, shops…"
                        class="w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                </div>

                <!-- Date from -->
                <div class="flex items-center gap-1.5">
                    <label for="date-from" class="text-xs text-gray-500 shrink-0">From</label>
                    <input
                        id="date-from"
                        v-model="dateFrom"
                        type="date"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                </div>

                <!-- Date to -->
                <div class="flex items-center gap-1.5">
                    <label for="date-to" class="text-xs text-gray-500 shrink-0">To</label>
                    <input
                        id="date-to"
                        v-model="dateTo"
                        type="date"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                </div>

                <!-- Column visibility toggle -->
                <div class="relative">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="colPickerOpen = !colPickerOpen"
                        class="gap-1.5"
                    >
                        <SlidersHorizontal class="w-4 h-4" />
                        Columns
                    </Button>

                    <div
                        v-show="colPickerOpen"
                        class="absolute right-0 z-20 mt-1 w-44 rounded-xl border border-gray-200 bg-white shadow-lg py-1"
                    >
                        <label
                            v-for="col in table.getAllLeafColumns()"
                            :key="col.id"
                            class="flex items-center gap-2.5 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer select-none"
                        >
                            <input
                                type="checkbox"
                                :checked="col.getIsVisible()"
                                @change="col.toggleVisibility()"
                                class="rounded border-gray-300 accent-purple-600"
                            />
                            {{ col.columnDef.header }}
                        </label>
                    </div>

                    <!-- click-outside backdrop -->
                    <div
                        v-show="colPickerOpen"
                        class="fixed inset-0 z-10"
                        @click="colPickerOpen = false"
                    />
                </div>

                <!-- CSV export -->
                <Button
                    variant="outline"
                    size="sm"
                    @click="csvExport"
                    class="gap-1.5 shrink-0"
                >
                    <Download class="w-4 h-4" />
                    Export CSV
                </Button>

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

                        <TableEmpty v-else :colspan="table.getAllLeafColumns().length">
                            <div class="py-12 text-center text-gray-400 text-sm">
                                No settlements match the current filters.
                            </div>
                        </TableEmpty>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-gray-600">

                <!-- Row count -->
                <p class="text-gray-400">
                    Showing
                    {{ table.getState().pagination.pageIndex * table.getState().pagination.pageSize + 1 }}
                    –
                    {{ Math.min(
                        (table.getState().pagination.pageIndex + 1) * table.getState().pagination.pageSize,
                        table.getFilteredRowModel().rows.length
                    ) }}
                    of {{ table.getFilteredRowModel().rows.length }} settlements
                </p>

                <div class="flex items-center gap-3">
                    <!-- Page size -->
                    <div class="flex items-center gap-1.5">
                        <label for="page-size" class="text-xs text-gray-400 shrink-0">Rows</label>
                        <select
                            id="page-size"
                            :value="table.getState().pagination.pageSize"
                            @change="table.setPageSize(Number($event.target.value))"
                            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                        >
                            <option v-for="n in [10, 20, 50, 100]" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>

                    <!-- Prev / Next -->
                    <div class="flex items-center gap-1">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.previousPage()"
                        >
                            <ChevronDown class="w-4 h-4 rotate-90" />
                        </Button>

                        <span class="px-2 tabular-nums">
                            {{ table.getState().pagination.pageIndex + 1 }}
                            / {{ table.getPageCount() }}
                        </span>

                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!table.getCanNextPage()"
                            @click="table.nextPage()"
                        >
                            <ChevronDown class="w-4 h-4 -rotate-90" />
                        </Button>
                    </div>
                </div>

            </div>

        </div>
    </AppLayout>
</template>
