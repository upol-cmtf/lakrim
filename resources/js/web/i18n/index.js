import { createI18n } from 'vue-i18n';
import cs from './locales/cs';

// Composition API režim (legacy: false) – v šablonách $t, v setupu useI18n().
// warnHtmlMessage vypnuté, protože zprávy průvodce vědomě obsahují HTML
// (vlastní, důvěryhodné řetězce renderované přes v-html).
export default createI18n({
    legacy: false,
    globalInjection: true,
    locale: 'cs',
    fallbackLocale: 'cs',
    warnHtmlMessage: false,
    messages: { cs },
});
