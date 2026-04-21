<script setup>
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const form = useForm({
    email:    '',
    password: '',
    remember: false,
});

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
        <div class="w-full max-w-sm">

            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <img src="/images/ggg-logo-login.svg" alt="GGG — Go Grab Get" class="h-24 w-auto" />
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
                <h1 class="text-lg font-semibold text-gray-900 mb-6">Sign in</h1>

                <form @submit.prevent="submit" class="space-y-5">

                    <div>
                        <Label for="email" class="mb-1.5 block">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            placeholder="admin@ggg.test"
                            :class="form.errors.email ? 'border-red-400' : ''"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <Label for="password" class="mb-1.5 block">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="rounded border-gray-300 accent-purple-600"
                        />
                        <label for="remember" class="text-sm text-gray-600 cursor-pointer">
                            Remember me
                        </label>
                    </div>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white"
                    >
                        {{ form.processing ? 'Signing in…' : 'Sign in' }}
                    </Button>

                </form>
            </div>
        </div>
    </div>
</template>
