<template>
    <transition name="modal-fade">
        <div
            v-if="open"
            class="question-modal-backdrop fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="$t('islandGame.contacts.title')"
            @click.self="$emit('close')"
        >
            <div class="contacts-modal relative my-auto max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-8">
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

                <contacts-list :contacts="contacts" />
            </div>
        </div>
    </transition>
</template>

<script setup>
import ContactsList from './ContactsList.vue';

defineProps({
    open: { type: Boolean, default: false },
    contacts: { type: Array, default: () => [] },
});
defineEmits(['close']);
</script>
