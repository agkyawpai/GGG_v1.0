<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { t } from '@/Composables/useLocale';
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
        <div class="p-4 md:p-6 max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-900 mb-4 md:mb-6">{{ t('register_new_order') }}</h1>

            <form @submit.prevent="submit" class="space-y-5 bg-white border border-gray-200 rounded-xl p-4 md:p-6 shadow-sm mb-20 md:mb-0">
                <!-- Type toggle -->
                <div>
                    <Label class="mb-1.5 block">{{ t('order_type') }}</Label>
                    <div class="flex w-full gap-2">
                        <button
                            type="button"
                            @click="form.type = 'type_a'"
                            class="flex-1 py-2.5 px-4 rounded-lg border text-sm font-medium transition-colors"
                            :class="form.type === 'type_a'
                                ? 'bg-purple-600 text-white border-purple-600'
                                : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                        >
                            {{ t('type_a_yangon') }}
                        </button>
                        <button
                            type="button"
                            @click="form.type = 'type_b'"
                            class="flex-1 py-2 px-4 rounded-lg border text-sm font-medium transition-colors"
                            :class="form.type === 'type_b'
                                ? 'bg-teal-600 text-white border-teal-600'
                                : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                        >
                            {{ t('type_b_out_of_yangon') }}
                        </button>
                    </div>
                </div>

                <!-- Shop -->
                <div>
                    <Label for="shop_id" class="mb-1.5 block">{{ t('shop') }}</Label>
                    <select id="shop_id" v-model="form.shop_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">{{ t('select_shop') }}</option>
                        <option v-for="s in props.shops" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <p v-if="form.errors.shop_id" class="mt-1 text-xs text-red-500">{{ form.errors.shop_id }}</p>
                </div>

                <!-- Customer -->
                <div>
                    <Label for="customer_id" class="mb-1.5 block">{{ t('customer') }}</Label>
                    <select id="customer_id" v-model="form.customer_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">{{ t('select_customer') }}</option>
                        <option v-for="c in props.customers" :key="c.id" :value="c.id">{{ c.name }} — {{ c.phone }}</option>
                    </select>
                    <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-500">{{ form.errors.customer_id }}</p>
                </div>

                <!-- Township -->
                <div>
                    <Label for="township_id" class="mb-1.5 block">{{ t('township') }}</Label>
                    <select id="township_id" v-model="form.township_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">{{ t('select_township') }}</option>
                        <option v-for="t in props.townships" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                    <p v-if="form.errors.township_id" class="mt-1 text-xs text-red-500">{{ form.errors.township_id }}</p>
                </div>

                <!-- Item -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <Label for="item_name" class="mb-1.5 block">{{ t('item_name') }}</Label>
                        <Input id="item_name" v-model="form.item_name" :placeholder="t('item_name_placeholder')" />
                        <p v-if="form.errors.item_name" class="mt-1 text-xs text-red-500">{{ form.errors.item_name }}</p>
                    </div>
                    <div>
                        <Label for="item_cost" class="mb-1.5 block">{{ t('item_cost') }}</Label>
                        <Input id="item_cost" type="number" v-model="form.item_cost" placeholder="0" />
                        <p v-if="form.errors.item_cost" class="mt-1 text-xs text-red-500">{{ form.errors.item_cost }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <Label for="cod_amount" class="mb-1.5 block">{{ t('cod_amount') }}</Label>
                        <Input id="cod_amount" type="number" v-model="form.cod_amount" placeholder="0" />
                        <p v-if="form.errors.cod_amount" class="mt-1 text-xs text-red-500">{{ form.errors.cod_amount }}</p>
                    </div>
                    <div>
                        <Label for="delivery_fee" class="mb-1.5 block">{{ t('delivery_fee') }}</Label>
                        <Input id="delivery_fee" type="number" v-model="form.delivery_fee" placeholder="2500" />
                        <p v-if="form.errors.delivery_fee" class="mt-1 text-xs text-red-500">{{ form.errors.delivery_fee }}</p>
                    </div>
                </div>

                <!-- Type B extra fields -->
                <div v-if="form.type === 'type_b'">
                    <Label for="shipped_via" class="mb-1.5 block">{{ t('shipped_via') }}</Label>
                    <select id="shipped_via" v-model="form.shipped_via" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">{{ t('select_shipping_method') }}</option>
                        <option value="bus">{{ t('bus') }}</option>
                        <option value="express">{{ t('express_courier') }}</option>
                    </select>
                    <p v-if="form.errors.shipped_via" class="mt-1 text-xs text-red-500">{{ form.errors.shipped_via }}</p>
                </div>

                <!-- Notes -->
                <div>
                    <Label for="notes" class="mb-1.5 block">{{ t('notes') }} <span class="text-gray-400">{{ t('optional') }}</span></Label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="2"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"
                        :placeholder="t('special_instructions')"
                    ></textarea>
                </div>

                <Button type="submit" :disabled="form.processing" class="hidden md:flex w-full">
                    {{ form.processing ? t('registering') : t('register_order') }}
                </Button>
            </form>

            <!-- Mobile fixed submit -->
            <div class="md:hidden fixed bottom-16 inset-x-0 px-4 py-3 bg-white border-t border-gray-200 z-20">
                <Button type="button" :disabled="form.processing" class="w-full" @click="submit">
                    {{ form.processing ? t('registering') : t('register_order') }}
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
