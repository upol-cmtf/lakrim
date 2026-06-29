<template>
    <!-- pobídka chytat rybky – jen na přehledu moře, po delší nečinnosti hráče -->
    <transition name="fish-hint-fade">
        <div v-if="visible" class="fish-hint" role="status">
            <span class="fish-hint-icon" aria-hidden="true">🐟</span>
            <span class="fish-hint-text">{{ $t('islandGame.sea.hint') }}</span>
            <button
                type="button"
                class="fish-hint-close"
                :aria-label="$t('islandGame.common.close')"
                @click="$emit('close')"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    visible: { type: Boolean, default: false },
});
defineEmits(['close']);
</script>

<style scoped lang="scss">
/* pobídka „zkuste chytit rybku" – plovoucí lišta dole uprostřed přehledu moře.
   teplý sluneční gradient + pulzující záře, ať na modré vodě nezapadne */
.fish-hint {
    position: absolute;
    bottom: 1.5rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 30;
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    max-width: calc(100% - 2rem);
    padding: 0.7rem 0.75rem 0.7rem 1.15rem;
    border-radius: 9999px;
    background: linear-gradient(180deg, #ffc24a 0%, #ff9b21 100%);
    color: #5a2c00;
    font-weight: 800;
    font-size: clamp(0.9rem, 1.8vw, 1.1rem);
    line-height: 1.2;
    border: 2px solid rgba(255, 255, 255, 0.9);
    box-shadow:
        0 8px 22px rgba(0, 20, 50, 0.3),
        0 0 0 0 rgba(255, 193, 74, 0.7);
    animation: fish-hint-pulse 1.8s ease-in-out infinite;
}

.fish-hint-icon {
    font-size: 1.3em;
    line-height: 1;
    /* houpe jen rybka, ať to neruší slide-in/out celé lišty */
    animation: fish-hint-bob 2.6s ease-in-out infinite;
}

.fish-hint-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.6rem;
    height: 1.6rem;
    flex: 0 0 auto;
    border: 0;
    border-radius: 9999px;
    background: rgba(90, 44, 0, 0.18);
    color: #5a2c00;
    cursor: pointer;
    transition: background 0.15s ease;
}

.fish-hint-close:hover,
.fish-hint-close:focus-visible {
    background: rgba(90, 44, 0, 0.32);
    outline: none;
}

.fish-hint-close svg {
    width: 0.9rem;
    height: 0.9rem;
}

@keyframes fish-hint-bob {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-3px); }
}

/* pulzující záře okolo lišty – mění jen box-shadow, transform zůstává na slide-in */
@keyframes fish-hint-pulse {
    0%, 100% { box-shadow: 0 8px 22px rgba(0, 20, 50, 0.3), 0 0 0 0 rgba(255, 193, 74, 0.7); }
    50%      { box-shadow: 0 8px 22px rgba(0, 20, 50, 0.3), 0 0 0 12px rgba(255, 193, 74, 0); }
}

.fish-hint-fade-enter-active,
.fish-hint-fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fish-hint-fade-enter-from,
.fish-hint-fade-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(10px);
}
</style>
