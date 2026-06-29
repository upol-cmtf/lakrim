<template>
    <div class="absolute inset-0 z-10 flex flex-col p-3 sm:p-6">
        <div class="flex min-h-0 flex-1 items-center justify-center overflow-hidden">
            <div class="island-stage relative">
                <img
                    :src="island.detailImage || island.image"
                    :alt="island.name"
                    class="island-shadow-soft block h-full w-full"
                >
                <!-- lampa na trávě nalevo od kamene – rozzáří se po správné odpovědi -->
                <div
                    v-for="(pos, i) in island.pathButtonPositions"
                    :key="`lamp-${i}`"
                    :style="pos"
                    class="path-lamp absolute"
                    :class="{ 'path-lamp--lit': island.buttonStates[i] === 'green' }"
                    aria-hidden="true"
                >
                    <img
                        :src="island.buttonStates[i] === 'green' ? lampOnImg : lampOffImg"
                        alt=""
                        class="path-lamp-img block w-full"
                    >
                </div>
                <button
                    v-for="(pos, i) in island.pathButtonPositions"
                    :key="`btn-${i}`"
                    type="button"
                    :style="pos"
                    class="path-button absolute"
                    :class="{
                        'path-button--locked': island.buttonStates[i] === 'locked',
                        'path-button--done': island.buttonStates[i] === 'green' || island.buttonStates[i] === 'red',
                        'path-button--pulse': shouldPulse(i),
                    }"
                    :disabled="island.buttonStates[i] === 'locked'"
                    :aria-label="island.buttonStates[i] === 'green' || island.buttonStates[i] === 'red'
                        ? $t('islandGame.detail.taskReview', { n: i + 1 })
                        : $t('islandGame.detail.task', { n: i + 1 })"
                    @click="$emit('open-question', i)"
                >
                    <img
                        :src="buttonSrc(i, island.buttonStates[i])"
                        :alt="$t('islandGame.detail.task', { n: i + 1 })"
                        class="block w-full"
                    >
                    <svg
                        v-if="island.buttonStates[i] === 'locked'"
                        class="path-button-lock"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path d="M7 10V7a5 5 0 0110 0v3h-2V7a3 3 0 00-6 0v3z" fill="#fff"/>
                        <path fill-rule="evenodd" d="M5 11a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V11zm7 3a2 2 0 00-1 3.732V19a1 1 0 102 0v-1.268A2 2 0 0012 14z" fill="url(#lock-gradient)"/>
                    </svg>
                </button>
            </div>
        </div>

        <slot name="detail" :island="island"></slot>
    </div>
</template>

<script setup>
defineProps({
    island: { type: Object, required: true },
    lampOnImg: { type: String, default: '' },
    lampOffImg: { type: String, default: '' },
    buttonSrc: { type: Function, required: true },
    shouldPulse: { type: Function, required: true },
});
defineEmits(['open-question']);
</script>

<style scoped lang="scss">
.island-shadow-soft {
    filter:
        drop-shadow(0 0 6px  rgba(255, 255, 255, 0.85))
        drop-shadow(0 0 18px rgba(173, 216, 230, 0.6))
        drop-shadow(0 0 36px rgba(173, 216, 230, 0.35))
        drop-shadow(0 14px 12px rgba(8, 38, 70, 0.55));
}

.island-stage {
    aspect-ratio: 1970 / 1504;
    max-height: 100%;
    max-width: 100%;
}

.path-button {
    width: 9%;
    padding: 0;
    border: 0;
    background: transparent;
    transform: translate(-50%, -50%);
    filter: drop-shadow(0 3px 4px rgba(0, 0, 0, 0.45));
    transition: transform 0.18s ease, filter 0.18s ease;
    cursor: pointer;
    user-select: none;
}

.path-button:not(.path-button--locked):not(.path-button--done):hover,
.path-button:not(.path-button--locked):not(.path-button--done):focus {
    transform: translate(-50%, -50%) scale(1.12);
    filter: drop-shadow(0 6px 6px rgba(0, 0, 0, 0.55));
}

.path-button--locked {
    cursor: not-allowed;
}

/* první kámen pulzuje, dokud hráč na ostrově nezačal odpovídat – upoutá pozornost */
.path-button--pulse {
    animation: path-button-pulse 1.8s ease-in-out infinite;
}

.path-button--pulse:hover,
.path-button--pulse:focus {
    animation: none;
}

@keyframes path-button-pulse {
    0%, 100% {
        transform: translate(-50%, -50%) scale(1);
        filter: drop-shadow(0 3px 4px rgba(0, 0, 0, 0.45));
    }
    50% {
        transform: translate(-50%, -50%) scale(1.12);
        filter:
            drop-shadow(0 6px 6px rgba(0, 0, 0, 0.55))
            drop-shadow(0 0 10px rgba(255, 224, 140, 0.9));
    }
}

/* vyřešený kámen jde znovu otevřít a prohlédnout si situaci i vlastní odpověď */
.path-button--done {
    cursor: pointer;
}

.path-button--done:hover,
.path-button--done:focus {
    transform: translate(-50%, -50%) scale(1.06);
    filter: drop-shadow(0 5px 5px rgba(0, 0, 0, 0.5));
}

.path-button--locked > img {
    opacity: 0.55;
    filter: grayscale(0.85);
}

/* --- lampa u kamene: výchozí stav zhasnutá, po správné odpovědi se rozzáří --- */
.path-lamp {
    /* lampa stojí na trávě nalevo od kamene; transformem se doladí přesná pozice */
    width: 5%;
    transform: translate(-210%, -118%);
    pointer-events: none;
    user-select: none;
    z-index: 1;
}

.path-lamp-img {
    position: relative;
    z-index: 2;
    filter: drop-shadow(0 4px 4px rgba(8, 38, 70, 0.5));
    transition: filter 0.6s ease;
}

/* teplá záře kolem hlavy lampy – schovaná, dokud kámen není vyřešený */
.path-lamp::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 18%;
    width: 260%;
    aspect-ratio: 1;
    transform: translate(-50%, -50%);
    border-radius: 50%;
    background: radial-gradient(
        circle,
        rgba(255, 243, 200, 0.95) 0%,
        rgba(255, 224, 140, 0.55) 28%,
        rgba(255, 224, 140, 0) 68%
    );
    opacity: 0;
    mix-blend-mode: screen;
    transition: opacity 0.7s ease;
    pointer-events: none;
    z-index: 1;
}

.path-lamp--lit::before {
    opacity: 1;
}

.path-lamp--lit .path-lamp-img {
    filter:
        drop-shadow(0 4px 4px rgba(8, 38, 70, 0.5))
        drop-shadow(0 0 9px rgba(255, 224, 140, 0.95));
}

.path-button-lock {
    position: absolute;
    top: 38%;
    left: 50%;
    width: 55%;
    height: 55%;
    transform: translate(-50%, -50%);
    filter:
        drop-shadow(0 2px 2px rgba(0, 0, 0, 0.55))
        drop-shadow(0 5px 6px rgba(0, 0, 0, 0.35));
    pointer-events: none;
}

@media (prefers-reduced-motion: reduce) {
    .path-button--pulse { animation: none; }
}
</style>
