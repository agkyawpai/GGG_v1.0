<template>
    <Button variant="ghost" size="sm" @click="toggle">
        <Globe class="w-4 h-4 mr-1" />
        <span v-if="locale === 'en'">🇬🇧 EN</span>
        <span v-else>🇲🇲 မြန်မာ</span>
    </Button>
</template>

<script setup>
import { Globe } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { locale, setLocale } from '@/Composables/useLocale';

function toggle() {
    setLocale(locale.value === 'en' ? 'my' : 'en');
}
</script>
