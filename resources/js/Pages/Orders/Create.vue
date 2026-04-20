<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
    shops:     Array,
    customers: Array,
    townships: Array,
    bikers:    Array,
});

const form = useForm({
    type:         'type_a',
    shop_id:      '',
    customer_id:  '',
    township_id:  '',
    item_name:    '',
    item_cost:    '',
    cod_amount:   '',
    delivery_fee: '2500',
    notes:        '',
    shipped_via:  '',
});

function submit() {
    form.post(route('orders.store'));
}
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Register New Order</h1>

            <form @submit.prevent="submit" class="space-y-5 bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <!-- Type toggle -->
                <div>
                    <Label class="mb-1.5 block">Order Type</Label>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="form.type = 'type_a'"
                            class="flex-1 py-2 px-4 rounded-lg border text-sm font-medium transition-colors"
                            :class="form.type === 'type_a'
                                ? 'bg-purple-600 text-white border-purple-600'
                                : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                        >
                            Type A — Yangon
                        </button>
                        <button
                            type="button"
                            @click="form.type = 'type_b'"
                            class="flex-1 py-2 px-4 rounded-lg border text-sm font-medium transition-colors"
                            :class="form.type === 'type_b'
                                ? 'bg-teal-600 text-white border-teal-600'
                                : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                        >
                            Type B — Out of Yangon
                        </button>
                    </div>
                </div>

                <!-- Shop -->
                <div>
                    <Label for="shop_id" class="mb-1.5 block">Shop</Label>
                    <select id="shop_id" v-model="form.shop_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select shop…</option>
                        <option v-for="s in props.shops" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <p v-if="form.errors.shop_id" class="mt-1 text-xs text-red-500">{{ form.errors.shop_id }}</p>
                </div>

                <!-- Customer -->
                <div>
                    <Label for="customer_id" class="mb-1.5 block">Customer</Label>
                    <select id="customer_id" v-model="form.customer_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select customer…</option>
                        <option v-for="c in props.customers" :key="c.id" :value="c.id">{{ c.name }} — {{ c.phone }}</option>
                    </select>
                    <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-500">{{ form.errors.customer_id }}</p>
                </div>

                <!-- Township -->
                <div>
                    <Label for="township_id" class="mb-1.5 block">Township</Label>
                    <select id="township_id" v-model="form.township_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select township…</option>
                        <option v-for="t in props.townships" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                    <p v-if="form.errors.township_id" class="mt-1 text-xs text-red-500">{{ form.errors.township_id }}</p>
                </div>

                <!-- Item -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label for="item_name" class="mb-1.5 block">Item Name</Label>
                        <Input id="item_name" v-model="form.item_name" placeholder="e.g. Nike Shoes" />
                        <p v-if="form.errors.item_name" class="mt-1 text-xs text-red-500">{{ form.errors.item_name }}</p>
                    </div>
                    <div>
                        <Label for="item_cost" class="mb-1.5 block">Item Cost (MMK)</Label>
                        <Input id="item_cost" type="number" v-model="form.item_cost" placeholder="0" />
                        <p v-if="form.errors.item_cost" class="mt-1 text-xs text-red-500">{{ form.errors.item_cost }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label for="cod_amount" class="mb-1.5 block">COD Amount (MMK)</Label>
                        <Input id="cod_amount" type="number" v-model="form.cod_amount" placeholder="0" />
                        <p v-if="form.errors.cod_amount" class="mt-1 text-xs text-red-500">{{ form.errors.cod_amount }}</p>
                    </div>
                    <div>
                        <Label for="delivery_fee" class="mb-1.5 block">Delivery Fee (MMK)</Label>
                        <Input id="delivery_fee" type="number" v-model="form.delivery_fee" placeholder="2500" />
                        <p v-if="form.errors.delivery_fee" class="mt-1 text-xs text-red-500">{{ form.errors.delivery_fee }}</p>
                    </div>
                </div>

                <!-- Type B extra fields -->
                <div v-if="form.type === 'type_b'">
                    <Label for="shipped_via" class="mb-1.5 block">Shipped Via</Label>
                    <select id="shipped_via" v-model="form.shipped_via" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">Select shipping method…</option>
                        <option value="bus">Bus</option>
                        <option value="express">Express Courier</option>
                    </select>
                    <p v-if="form.errors.shipped_via" class="mt-1 text-xs text-red-500">{{ form.errors.shipped_via }}</p>
                </div>

                <!-- Notes -->
                <div>
                    <Label for="notes" class="mb-1.5 block">Notes <span class="text-gray-400">(optional)</span></Label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="2"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"
                        placeholder="Any special instructions…"
                    ></textarea>
                </div>

                <Button type="submit" :disabled="form.processing" class="w-full">
                    {{ form.processing ? 'Registering…' : 'Register Order' }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>
