<template>
    <!-- důležité kontakty – otevřou se ze záchranného kruhu v majáku -->
    <transition name="modal-fade">
        <div
            v-if="open"
            class="question-modal-backdrop fixed inset-0 z-[60] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="$t('islandGame.contacts.title')"
            @click.self="$emit('close')"
        >
            <div class="contacts-modal relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl sm:p-8">
                <button
                    type="button"
                    class="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-blue-900 transition hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                    :aria-label="$t('islandGame.common.close')"
                    @click="$emit('close')"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>

                <h3 class="mb-1 pr-8 text-xl font-bold text-blue-900">{{ $t('islandGame.contacts.title') }}</h3>
                <p class="mb-4 text-sm text-slate-600">{{ $t('islandGame.contacts.intro') }}</p>

                <ul class="space-y-2">
                    <li v-for="contact in contacts" :key="contact.label">
                        <a v-if="contact.phone" :href="`tel:${contact.phone.replace(/\s/g, '')}`" class="contact-row">
                            <span class="contact-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M6.6 10.8a15.5 15.5 0 006.6 6.6l2.2-2.2a1 1 0 011-.24 11.4 11.4 0 003.6.58 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.4 11.4 0 00.58 3.6 1 1 0 01-.24 1l-2.24 2.2z" fill="currentColor"/>
                                </svg>
                            </span>
                            <span class="contact-text">
                                <span class="contact-name">{{ contact.label }}</span>
                                <span class="contact-note">{{ contact.note }}</span>
                            </span>
                            <span class="contact-phone">{{ contact.phone }}</span>
                        </a>
                        <div v-else class="contact-row contact-row--static">
                            <span class="contact-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M6.6 10.8a15.5 15.5 0 006.6 6.6l2.2-2.2a1 1 0 011-.24 11.4 11.4 0 003.6.58 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.4 11.4 0 00.58 3.6 1 1 0 01-.24 1l-2.24 2.2z" fill="currentColor"/>
                                </svg>
                            </span>
                            <span class="contact-text">
                                <span class="contact-name">{{ contact.label }}</span>
                                <span class="contact-note">{{ contact.note }}</span>
                            </span>
                            <span v-if="contact.phoneNote" class="contact-phone contact-phone--note">{{ contact.phoneNote }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    open: { type: Boolean, default: false },
    contacts: { type: Array, default: () => [] },
});
defineEmits(['close']);
</script>

<style scoped lang="scss">
@use '../styles/shared' as *;

/* --- modal důležitých kontaktů --- */
.contact-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 0.75rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    text-decoration: none;
    transition: background 0.15s ease, border-color 0.15s ease;
}

.contact-row:hover,
.contact-row:focus-visible {
    background: #eff6ff;
    border-color: #bfdbfe;
    outline: none;
}

.contact-icon {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 9999px;
    background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%);
    color: #3a2a05;
}

.contact-icon svg {
    width: 1.2rem;
    height: 1.2rem;
}

.contact-text {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
}

.contact-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
}

.contact-note {
    font-size: 0.78rem;
    color: #64748b;
    line-height: 1.3;
}

.contact-phone {
    flex-shrink: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #11365e;
    font-variant-numeric: tabular-nums;
}

/* kontakt bez vlastního čísla (např. infolinka banky) – jen poznámka, kam pro číslo */
.contact-row--static {
    cursor: default;
}

.contact-row--static:hover,
.contact-row--static:focus-visible {
    background: #f8fafc;
    border-color: #e2e8f0;
}

.contact-phone--note {
    max-width: 7.5rem;
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1.3;
    text-align: right;
    color: #64748b;
}
</style>
