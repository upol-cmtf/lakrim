<template>
    <!-- zvětšený obrázek ze zadání situace – zavře se klikem kamkoliv nebo křížkem -->
    <transition name="modal-fade">
        <div
            v-if="src"
            class="image-zoom-backdrop fixed inset-0 z-[70] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="$t('islandGame.zoom.aria')"
            @click="$emit('close')"
        >
            <button
                type="button"
                class="image-zoom-close"
                :aria-label="$t('islandGame.common.close')"
                @click="$emit('close')"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
            </button>
            <img :src="src" :alt="alt" class="image-zoom-img">
        </div>
    </transition>
</template>

<script setup>
defineProps({
    src: { type: String, default: null },
    alt: { type: String, default: '' },
});
defineEmits(['close']);
</script>

<style scoped lang="scss">
@use '../styles/shared' as *;

/* --- lightbox: zvětšený obrázek ze zadání situace --- */
.image-zoom-backdrop {
    background: rgba(8, 20, 40, 0.85);
    cursor: zoom-out;
}

.image-zoom-img {
    max-width: 92vw;
    max-height: 92vh;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 0.5rem;
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.55);
    cursor: zoom-out;
}

.image-zoom-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.6rem;
    height: 2.6rem;
    border: 0;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.92);
    color: #11365e;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
    transition: background 0.15s ease, transform 0.15s ease;
}

.image-zoom-close:hover,
.image-zoom-close:focus-visible {
    background: #fff;
    transform: scale(1.06);
    outline: none;
}

.image-zoom-close svg {
    width: 1.3rem;
    height: 1.3rem;
}
</style>
