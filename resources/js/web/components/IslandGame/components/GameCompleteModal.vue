<template>
    <!-- oslavný panel po projití všech kamenů na všech ostrovech -->
    <transition name="modal-fade">
        <div
            v-if="open"
            class="game-complete-backdrop fixed inset-0 z-[80] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="$t('islandGame.complete.title')"
        >
            <div class="game-complete-modal">
                <button
                    type="button"
                    class="game-complete-close"
                    :aria-label="$t('islandGame.common.close')"
                    @click="$emit('close')"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    </svg>
                </button>

                <div class="game-complete-glow" aria-hidden="true"></div>

                <div class="game-complete-scroll">
                <div class="game-complete-badge" aria-hidden="true">🗼</div>

                <h2 class="game-complete-title">{{ $t('islandGame.complete.title') }}</h2>
                <p class="game-complete-subtitle">{{ $t('islandGame.complete.subtitle') }}</p>

                <div class="game-complete-text">
                    <p>{{ $t('islandGame.complete.p1') }}</p>
                    <p>{{ $t('islandGame.complete.p2') }}</p>
                    <p>{{ $t('islandGame.complete.p3') }}</p>
                    <p>{{ $t('islandGame.complete.p4') }}</p>
                </div>

                <!-- překvapení na závěr: video ze skutečného světa -->
                <video
                    v-if="videoUrl"
                    class="game-complete-video"
                    :src="videoUrl"
                    controls
                    playsinline
                    preload="metadata"
                ></video>

                <div class="game-complete-stats">
                    <span class="game-complete-stat">
                        <strong>{{ collectedCards }}/{{ totalCards }}</strong> {{ $t('islandGame.complete.cards') }}
                    </span>
                    <span v-if="easterEggsCount > 0" class="game-complete-stat">
                        <strong>{{ caughtFishCount }}/{{ easterEggsCount }}</strong> {{ $t('islandGame.complete.fish') }}
                    </span>
                </div>

                <div class="game-complete-actions">
                    <button type="button" class="game-complete-btn" @click="$emit('close')">
                        {{ $t('islandGame.common.backToMap') }}
                    </button>
                    <a :href="homeUrl" class="game-complete-btn game-complete-btn--ghost">
                        {{ $t('islandGame.complete.home') }}
                    </a>
                </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    open: { type: Boolean, default: false },
    collectedCards: { type: Number, default: 0 },
    totalCards: { type: Number, default: 0 },
    caughtFishCount: { type: Number, default: 0 },
    easterEggsCount: { type: Number, default: 0 },
    homeUrl: { type: String, default: '/' },
    videoUrl: { type: String, default: '' },
});
defineEmits(['close']);
</script>

<style scoped lang="scss">
@use '../styles/shared' as *;

.game-complete-backdrop {
    background: rgba(8, 38, 70, 0.72);
    backdrop-filter: blur(6px);
}

.game-complete-modal {
    position: relative;
    width: 100%;
    max-width: 62rem;
    border-radius: 1.5rem;
    /* ořez kvůli zaobleným rohům – scrollbar vnitřního obalu pak nepřečuhuje přes roh */
    overflow: hidden;
    background: radial-gradient(ellipse at 50% -10%, #ffffff 0%, #f3f9ff 60%, #e6f1fb 100%);
    box-shadow: 0 24px 60px rgba(8, 38, 70, 0.5);
    text-align: center;
    /* zlatý proužek nahoře jako „světlo majáku" */
    border-top: 4px solid #f59e0b;
}

/* vlastní scroll je tady, uvnitř zaobleného rámu */
.game-complete-scroll {
    max-height: 90vh;
    overflow-y: auto;
    padding: 2.75rem 2.25rem 2rem;
}

/* teplá záře za špičkou majáku */
.game-complete-glow {
    position: absolute;
    top: -3.5rem;
    left: 50%;
    width: 16rem;
    height: 16rem;
    transform: translateX(-50%);
    pointer-events: none;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 224, 140, 0.7) 0%, rgba(255, 224, 140, 0) 70%);
}

.game-complete-badge {
    position: relative;
    font-size: 3.25rem;
    line-height: 1;
    filter: drop-shadow(0 6px 10px rgba(8, 38, 70, 0.3));
    animation: game-complete-bob 3s ease-in-out infinite;
}

@keyframes game-complete-bob {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-6px); }
}

.game-complete-title {
    margin: 0.85rem 0 0.35rem;
    font-size: clamp(1.4rem, 4vw, 1.85rem);
    font-weight: 800;
    color: #11365e;
    line-height: 1.2;
}

.game-complete-subtitle {
    margin: 0 0 1.1rem;
    font-size: 1rem;
    font-weight: 600;
    color: #2c79b0;
}

.game-complete-text {
    max-width: 48rem;
    margin: 0 auto;
    text-align: left;
    color: #334155;
    font-size: 0.95rem;
    line-height: 1.55;
}

.game-complete-text p {
    margin: 0 0 0.7rem;
}

.game-complete-text p:last-child {
    margin-bottom: 0;
}

.game-complete-video {
    display: block;
    width: 100%;
    margin: 1.25rem 0 0;
    border-radius: 0.85rem;
    background: #000;
    box-shadow: 0 8px 22px rgba(8, 38, 70, 0.35);
}

.game-complete-stats {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.6rem;
    margin: 1.25rem 0 1.4rem;
}

.game-complete-stat {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.85rem;
    border-radius: 9999px;
    background: rgba(20, 74, 120, 0.08);
    color: #11365e;
    font-size: 0.85rem;
    font-weight: 600;
}

.game-complete-stat strong {
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}

.game-complete-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.7rem;
}

.game-complete-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.7rem 1.4rem;
    border: 0;
    border-radius: 0.7rem;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%);
    color: #3a2a05;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    transition: filter 0.15s ease, transform 0.15s ease;
}

.game-complete-btn:hover,
.game-complete-btn:focus-visible {
    filter: brightness(1.05);
    transform: translateY(-1px);
    outline: none;
}

.game-complete-btn--ghost {
    background: #fff;
    color: #11365e;
    box-shadow: inset 0 0 0 1px rgba(20, 74, 120, 0.35);
}

.game-complete-close {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border: 0;
    border-radius: 9999px;
    background: rgba(20, 74, 120, 0.08);
    color: #11365e;
    cursor: pointer;
    transition: background 0.15s ease;
}

.game-complete-close:hover,
.game-complete-close:focus-visible {
    background: rgba(20, 74, 120, 0.16);
    outline: none;
}

.game-complete-close svg {
    width: 1.1rem;
    height: 1.1rem;
}

@media (prefers-reduced-motion: reduce) {
    .game-complete-badge { animation: none; }
}
</style>
