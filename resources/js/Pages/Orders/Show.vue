<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/Layouts/AppLayout.vue';
import TypeBadge from '@/Components/TypeBadge.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog, DialogContent, DialogHeader,
    DialogTitle, DialogFooter,
} from '@/components/ui/dialog';
import {
    Card, CardContent, CardHeader, CardTitle,
} from '@/components/ui/card';
import {
    MapPin, Truck, Warehouse, User, Store,
    Package, Banknote, ClipboardList, CheckCircle2,
    XCircle, Clock,
} from 'lucide-vue-next';

const props = defineProps({
    order:               Object,
    allowedTransitions:  Array,
    bikers:              Array,
    townships:           Array,
});

// — modal visibility —
const showAssignModal  = ref(false);
const showSettleModal  = ref(false);
const showRejectModal  = ref(false);

// — forms —
const assignForm = useForm({
    biker_id:    '',
    township_id: props.order.township_id ?? '',
});

const settleForm = useForm({
    cod_collected: props.order.cod_amount ?? '',
    total_cost:    props.order.item_cost  ?? '',
});

const rejectForm = useForm({
    rejection_reason: '',
    return_fee:       '',
});

// — transition helpers —
const can = computed(() => ({
    assign:      props.allowedTransitions.includes('assigned'),
    inTransit:   props.allowedTransitions.includes('in_transit'),
    deliver:     props.allowedTransitions.includes('delivered'),
    settle:      props.allowedTransitions.includes('settled'),
    reject:      props.allowedTransitions.includes('returned'),
}));

// — actions —
function submitAssign() {
    assignForm.patch(route('orders.assign', props.order.ulid), {
        onSuccess: () => {
            showAssignModal.value = false;
            toast.success('Biker assigned');
        },
    });
}

function markStatus(status) {
    useForm({ status }).patch(route('orders.status', props.order.ulid), {
        onSuccess: () => toast.success('Status updated'),
    });
}

function submitSettle() {
    settleForm.post(route('settlements.store', props.order.ulid), {
        onSuccess: () => {
            showSettleModal.value = false;
            toast.success(`Settlement recorded — Net ${fmtMMK(netAmount.value)}`);
        },
    });
}

function submitReject() {
    rejectForm.patch(route('orders.reject', props.order.ulid), {
        onSuccess: () => {
            showRejectModal.value = false;
            toast.error('Order rejected', { description: rejectForm.rejection_reason });
        },
    });
}

// — derived display values —
const netAmount = computed(() =>
    Number(settleForm.cod_collected) - Number(settleForm.total_cost)
);

function fmtMMK(val) {
    return `${Number(val).toLocaleString()} MMK`;
}

function fmtDate(val) {
    if (!val) return null;
    return new Date(val).toLocaleString('en-GB', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

const biker = computed(() => props.order.biker?.name ?? null);
const settler = computed(() => props.order.settlement?.settled_by?.name ?? null);

const timeline = computed(() => {
    const passed = (statuses) => statuses.includes(props.order.status);
    const entries = [
        {
            label:  'Order Registered',
            at:     props.order.ordered_at,
            status: 'pending',
            icon:   ClipboardList,
            actor:  null,
        },
        {
            label:     'Biker Assigned',
            at:        props.order.ordered_at,
            status:    'assigned',
            icon:      User,
            actor:     biker.value,
            condition: passed(['assigned','in_transit','delivered','returned','settled']),
        },
        {
            label:     'In Transit',
            at:        null,
            status:    'in_transit',
            icon:      Truck,
            actor:     biker.value,
            condition: passed(['in_transit','delivered','returned','settled']),
        },
        {
            label:     'Delivered',
            at:        props.order.delivered_at,
            status:    'delivered',
            icon:      CheckCircle2,
            actor:     biker.value,
            condition: passed(['delivered','returned','settled']),
        },
        {
            label:     'Rejected / Returned',
            at:        props.order.delivered_at,
            status:    'returned',
            icon:      XCircle,
            actor:     biker.value,
            condition: props.order.status === 'returned',
        },
        {
            label:     'Settled',
            at:        props.order.settled_at,
            status:    'settled',
            icon:      Banknote,
            actor:     settler.value,
            condition: props.order.status === 'settled',
        },
    ];
    return entries.filter(e => e.condition !== false);
});

const warehouseStatusLabel = {
    in_transit: 'In Transit',
    received:   'Received',
    stored:     'Stored',
    dispatched: 'Dispatched',
};
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-4xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Board
                    </a>
                    <span class="text-gray-300">/</span>
                    <span class="font-mono text-sm text-gray-500">{{ order.ulid }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <TypeBadge :type="order.type" />
                    <StatusBadge :status="order.status" />
                </div>
            </div>

            <!-- Order info card -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <component
                            :is="order.type === 'type_a' ? MapPin : Truck"
                            class="w-4 h-4"
                            :class="order.type === 'type_a' ? 'text-purple-500' : 'text-teal-500'"
                        />
                        Order Details
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="grid grid-cols-2 gap-x-8 gap-y-4 text-sm sm:grid-cols-3">

                        <div>
                            <dt class="flex items-center gap-1.5 text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                                <Store class="w-3.5 h-3.5" /> Shop
                            </dt>
                            <dd class="font-medium text-gray-900">{{ order.shop?.name }}</dd>
                            <dd class="text-gray-500 text-xs">{{ order.shop?.phone }}</dd>
                        </div>

                        <div>
                            <dt class="flex items-center gap-1.5 text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                                <User class="w-3.5 h-3.5" /> Customer
                            </dt>
                            <dd class="font-medium text-gray-900">{{ order.customer?.name }}</dd>
                            <dd class="text-gray-500 text-xs">{{ order.customer?.phone }}</dd>
                        </div>

                        <div>
                            <dt class="flex items-center gap-1.5 text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                                <MapPin class="w-3.5 h-3.5" /> Township
                            </dt>
                            <dd class="font-medium text-gray-900">{{ order.township?.name }}</dd>
                            <dd v-if="order.biker" class="text-gray-500 text-xs">
                                Biker: {{ order.biker.name }}
                            </dd>
                        </div>

                        <div>
                            <dt class="flex items-center gap-1.5 text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                                <Package class="w-3.5 h-3.5" /> Item
                            </dt>
                            <dd class="font-medium text-gray-900">{{ order.item_name }}</dd>
                            <dd class="text-gray-500 text-xs">Cost: {{ fmtMMK(order.item_cost) }}</dd>
                        </div>

                        <div>
                            <dt class="flex items-center gap-1.5 text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                                <Banknote class="w-3.5 h-3.5" /> COD Amount
                            </dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ fmtMMK(order.cod_amount) }}</dd>
                        </div>

                        <div>
                            <dt class="flex items-center gap-1.5 text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                                <Clock class="w-3.5 h-3.5" /> Delivery Fee
                            </dt>
                            <dd class="font-medium text-gray-900">{{ fmtMMK(order.delivery_fee) }}</dd>
                            <dd v-if="order.return_fee" class="text-xs text-red-500">
                                Return fee: {{ fmtMMK(order.return_fee) }}
                            </dd>
                        </div>

                        <div v-if="order.notes" class="col-span-2 sm:col-span-3">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Notes</dt>
                            <dd class="text-gray-700 text-sm">{{ order.notes }}</dd>
                        </div>

                        <div v-if="order.rejection_reason" class="col-span-2 sm:col-span-3">
                            <dt class="text-xs font-medium text-red-400 uppercase tracking-wide mb-1">Rejection Reason</dt>
                            <dd class="text-red-700 text-sm">{{ order.rejection_reason }}</dd>
                        </div>

                    </dl>
                </CardContent>
            </Card>

            <!-- Type B — Warehouse section -->
            <Card v-if="order.type === 'type_b' && order.warehousing">
                <CardHeader class="pb-3">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Warehouse class="w-4 h-4 text-teal-500" />
                        Warehouse
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="grid grid-cols-2 gap-x-8 gap-y-4 text-sm sm:grid-cols-4">

                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                                Status
                            </dt>
                            <dd>
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="{
                                        'bg-sky-100    text-sky-700':    order.warehousing.status === 'in_transit',
                                        'bg-yellow-100 text-yellow-700': order.warehousing.status === 'received',
                                        'bg-teal-100   text-teal-700':   order.warehousing.status === 'stored',
                                        'bg-gray-100   text-gray-600':   order.warehousing.status === 'dispatched',
                                    }"
                                >
                                    {{ warehouseStatusLabel[order.warehousing.status] }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                                Shipped Via
                            </dt>
                            <dd>
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="order.warehousing.shipped_via === 'bus'
                                        ? 'bg-blue-100 text-blue-700'
                                        : 'bg-amber-100 text-amber-700'"
                                >
                                    {{ order.warehousing.shipped_via === 'bus' ? 'Bus' : 'Express' }}
                                </span>
                            </dd>
                        </div>

                        <div v-if="order.warehousing.received_at">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                                Received
                            </dt>
                            <dd class="text-gray-700">{{ fmtDate(order.warehousing.received_at) }}</dd>
                        </div>

                        <div v-if="order.warehousing.received_by">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                                Received By
                            </dt>
                            <dd class="text-gray-700">{{ order.warehousing.received_by?.name }}</dd>
                        </div>

                        <div v-if="order.warehousing.dispatched_at">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                                Dispatched
                            </dt>
                            <dd class="text-gray-700">{{ fmtDate(order.warehousing.dispatched_at) }}</dd>
                        </div>

                    </dl>
                </CardContent>
            </Card>

            <!-- Timeline -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <ClipboardList class="w-4 h-4 text-gray-400" />
                        Order Timeline
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <ol class="relative space-y-0">
                        <li
                            v-for="(event, i) in timeline"
                            :key="event.status"
                            class="flex gap-4"
                        >
                            <!-- Spine + dot -->
                            <div class="flex flex-col items-center">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full ring-2 ring-white"
                                    :class="{
                                        'bg-yellow-100 text-yellow-700':  event.status === 'pending',
                                        'bg-blue-100   text-blue-700':    event.status === 'assigned',
                                        'bg-orange-100 text-orange-700':  event.status === 'in_transit',
                                        'bg-green-100  text-green-700':   event.status === 'delivered',
                                        'bg-red-100    text-red-700':     event.status === 'returned',
                                        'bg-gray-100   text-gray-600':    event.status === 'settled',
                                    }"
                                >
                                    <component :is="event.icon" class="w-4 h-4" />
                                </span>
                                <!-- Connector line — hidden on last item -->
                                <span
                                    v-if="i < timeline.length - 1"
                                    class="mt-1 mb-1 w-px flex-1 bg-gray-200"
                                />
                            </div>

                            <!-- Content -->
                            <div class="pb-6 pt-1 min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 leading-none mb-1">
                                    {{ event.label }}
                                </p>
                                <p v-if="event.actor" class="text-xs text-gray-500">
                                    {{ event.actor }}
                                </p>
                                <p v-if="event.at" class="text-xs text-gray-400 mt-0.5">
                                    {{ fmtDate(event.at) }}
                                </p>
                                <p v-else class="text-xs text-gray-300 mt-0.5 italic">
                                    timestamp unavailable
                                </p>
                            </div>
                        </li>
                    </ol>
                </CardContent>
            </Card>

            <!-- Action buttons -->
            <div v-if="can.assign || can.inTransit || can.deliver || can.settle || can.reject"
                 class="flex flex-wrap gap-3">

                <Button v-if="can.assign"
                        @click="showAssignModal = true"
                        class="bg-blue-600 hover:bg-blue-700 text-white">
                    <User class="w-4 h-4 mr-2" /> Assign Biker
                </Button>

                <Button v-if="can.inTransit"
                        @click="markStatus('in_transit')"
                        class="bg-orange-500 hover:bg-orange-600 text-white">
                    <Truck class="w-4 h-4 mr-2" /> Mark In Transit
                </Button>

                <Button v-if="can.deliver"
                        @click="markStatus('delivered')"
                        class="bg-green-600 hover:bg-green-700 text-white">
                    <CheckCircle2 class="w-4 h-4 mr-2" /> Mark Delivered
                </Button>

                <Button v-if="can.settle"
                        @click="showSettleModal = true"
                        class="bg-purple-600 hover:bg-purple-700 text-white">
                    <Banknote class="w-4 h-4 mr-2" /> Record Settlement
                </Button>

                <Button v-if="can.reject"
                        variant="outline"
                        @click="showRejectModal = true"
                        class="border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300">
                    <XCircle class="w-4 h-4 mr-2" /> Record Rejection
                </Button>

            </div>

        </div>

        <!-- ── Assign Biker modal ─────────────────────────────── -->
        <Dialog :open="showAssignModal" @update:open="showAssignModal = $event">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <User class="w-4 h-4 text-blue-500" /> Assign Biker
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitAssign" class="space-y-4 pt-1">
                    <div>
                        <Label for="assign-biker" class="mb-1.5 block">Biker</Label>
                        <select
                            id="assign-biker"
                            v-model="assignForm.biker_id"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select biker…</option>
                            <option v-for="b in bikers" :key="b.id" :value="b.id">
                                {{ b.name }} — {{ b.phone }}
                            </option>
                        </select>
                        <p v-if="assignForm.errors.biker_id" class="mt-1 text-xs text-red-500">
                            {{ assignForm.errors.biker_id }}
                        </p>
                    </div>

                    <div>
                        <Label for="assign-township" class="mb-1.5 block">Township</Label>
                        <select
                            id="assign-township"
                            v-model="assignForm.township_id"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select township…</option>
                            <option v-for="t in townships" :key="t.id" :value="t.id">
                                {{ t.name }}
                            </option>
                        </select>
                        <p v-if="assignForm.errors.township_id" class="mt-1 text-xs text-red-500">
                            {{ assignForm.errors.township_id }}
                        </p>
                    </div>

                    <DialogFooter class="pt-2">
                        <Button type="button" variant="outline" @click="showAssignModal = false">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="assignForm.processing"
                                class="bg-blue-600 hover:bg-blue-700 text-white">
                            {{ assignForm.processing ? 'Assigning…' : 'Assign Biker' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- ── Record Settlement modal ────────────────────────── -->
        <Dialog :open="showSettleModal" @update:open="showSettleModal = $event">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Banknote class="w-4 h-4 text-purple-500" /> Record Settlement
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitSettle" class="space-y-4 pt-1">
                    <div>
                        <Label for="cod-collected" class="mb-1.5 block">COD Collected (MMK)</Label>
                        <Input
                            id="cod-collected"
                            type="number"
                            v-model="settleForm.cod_collected"
                            min="0"
                            step="1"
                        />
                        <p v-if="settleForm.errors.cod_collected" class="mt-1 text-xs text-red-500">
                            {{ settleForm.errors.cod_collected }}
                        </p>
                    </div>

                    <div>
                        <Label for="total-cost" class="mb-1.5 block">Total Cost (MMK)</Label>
                        <Input
                            id="total-cost"
                            type="number"
                            v-model="settleForm.total_cost"
                            min="0"
                            step="1"
                        />
                        <p v-if="settleForm.errors.total_cost" class="mt-1 text-xs text-red-500">
                            {{ settleForm.errors.total_cost }}
                        </p>
                    </div>

                    <!-- Live net preview -->
                    <div class="rounded-lg border border-gray-100 bg-gray-50 px-4 py-3 flex justify-between items-center text-sm">
                        <span class="text-gray-500">Net Amount</span>
                        <span class="font-semibold"
                              :class="netAmount >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ fmtMMK(netAmount) }}
                        </span>
                    </div>

                    <DialogFooter class="pt-2">
                        <Button type="button" variant="outline" @click="showSettleModal = false">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="settleForm.processing"
                                class="bg-purple-600 hover:bg-purple-700 text-white">
                            {{ settleForm.processing ? 'Saving…' : 'Confirm Settlement' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- ── Record Rejection modal ─────────────────────────── -->
        <Dialog :open="showRejectModal" @update:open="showRejectModal = $event">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <XCircle class="w-4 h-4 text-red-500" /> Record Rejection
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submitReject" class="space-y-4 pt-1">
                    <div>
                        <Label for="rejection-reason" class="mb-1.5 block">Rejection Reason</Label>
                        <textarea
                            id="rejection-reason"
                            v-model="rejectForm.rejection_reason"
                            rows="3"
                            placeholder="Describe why the customer rejected the order…"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-red-400"
                        />
                        <p v-if="rejectForm.errors.rejection_reason" class="mt-1 text-xs text-red-500">
                            {{ rejectForm.errors.rejection_reason }}
                        </p>
                    </div>

                    <div>
                        <Label for="return-fee" class="mb-1.5 block">
                            Return Fee (MMK) <span class="text-gray-400 font-normal">(optional)</span>
                        </Label>
                        <Input
                            id="return-fee"
                            type="number"
                            v-model="rejectForm.return_fee"
                            min="0"
                            step="1"
                            placeholder="0"
                        />
                        <p v-if="rejectForm.errors.return_fee" class="mt-1 text-xs text-red-500">
                            {{ rejectForm.errors.return_fee }}
                        </p>
                    </div>

                    <DialogFooter class="pt-2">
                        <Button type="button" variant="outline" @click="showRejectModal = false">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="rejectForm.processing"
                                class="bg-red-600 hover:bg-red-700 text-white">
                            {{ rejectForm.processing ? 'Saving…' : 'Confirm Rejection' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>
