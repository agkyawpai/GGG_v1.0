import { ref } from 'vue';
import axios from 'axios';
import { translations } from '@/i18n/translations';

const locale = ref(localStorage.getItem('app_locale') ?? 'en');

function setLocale(newLocale) {
    locale.value = newLocale;
    localStorage.setItem('app_locale', newLocale);
    axios.post('/locale', { locale: newLocale });
}

function t(key) {
    return translations[locale.value]?.[key] ?? key;
}

export { locale, setLocale, t };
