<template>
    <Dialog :open="open">
        <DialogContent :dismissable="false" class="sm:max-w-md" @pointer-down-outside.prevent @escape-key-down.prevent>
            <DialogHeader>
                <DialogTitle class="text-center">Choose Language / ဘာသာစကားရွေးပါ</DialogTitle>
            </DialogHeader>
            <div class="flex justify-center gap-4 px-6 py-6">
                <button
                    class="w-36 rounded-lg border-2 border-gray-200 px-4 py-4 text-center text-sm font-medium hover:border-gray-400 hover:bg-gray-50 transition-colors"
                    @click="pick('en')"
                >
                    <div class="text-2xl mb-1">🇬🇧</div>
                    <div>English</div>
                </button>
                <button
                    class="w-36 rounded-lg border-2 border-gray-200 px-4 py-4 text-center text-sm font-medium hover:border-gray-400 hover:bg-gray-50 transition-colors"
                    @click="pick('my')"
                >
                    <div class="text-2xl mb-1">🇲🇲</div>
                    <div>မြန်မာဘာသာ</div>
                </button>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { setLocale } from '@/Composables/useLocale';

const open = ref(false);

onMounted(() => {
    if (!localStorage.getItem('app_locale')) {
        open.value = true;
    }
});

function pick(lang) {
    setLocale(lang);
    open.value = false;
}
</script>
