<template>
    <div class="island-field absolute inset-0">
        <div
            class="island-wrap lighthouse-wrap absolute top-1/2 left-1/2 w-[22%] -translate-x-1/2 -translate-y-1/2"
            role="button"
            tabindex="0"
            :aria-label="$t('islandGame.map.openLighthouse')"
            @mouseenter="$emit('lighthouse-hover', true)"
            @mouseleave="$emit('lighthouse-hover', false)"
            @focus="$emit('lighthouse-hover', true)"
            @blur="$emit('lighthouse-hover', false)"
            @click="$emit('open-lighthouse')"
            @keydown.enter.prevent="$emit('open-lighthouse')"
            @keydown.space.prevent="$emit('open-lighthouse')"
        >
            <svg class="ripple" viewBox="0 0 220 70" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                <use class="ring ring-outer" href="#wave-outer"/>
                <use class="ring ring-inner" href="#wave-inner"/>
            </svg>
            <div class="lighthouse-figure relative block w-full">
                <img :src="lighthouseImg" :alt="$t('islandGame.map.lighthouseAlt')" class="island-shadow lighthouse-bob block w-full">
            </div>

            <!-- mraky halící maják, rozestupují se s počtem získaných karet bezpečí -->
            <div
                v-for="(cloud, i) in clouds"
                :key="`cloud-${i}`"
                class="lighthouse-cloud"
                :style="cloudStyle(cloud)"
                aria-hidden="true"
            >
                <svg
                    :class="['lighthouse-cloud-img', cloud.floatClass]"
                    :style="{ animationDelay: cloud.delay }"
                    viewBox="0 0 220 124"
                >
                    <use href="#cloud-shape"/>
                </svg>
            </div>

            <!-- paprsky majáku – rozsvítí se k dokončenému ostrovu -->
            <div
                v-for="(island, i) in islands"
                :key="`beam-${i}`"
                class="lighthouse-beam"
                :style="{
                    transform: `translateX(-50%) rotate(${island.beamAngle}deg)`,
                    opacity: island.tasks.completed >= island.tasks.total ? 0.85 : 0,
                }"
                aria-hidden="true"
            ></div>

            <!-- záře lampy sílí s postupem hráče -->
            <div
                class="lighthouse-glow"
                :style="{ opacity: 0.1 + progress * 0.9 }"
                aria-hidden="true"
            ></div>

            <!-- počítadlo získaných karet bezpečí -->
            <span class="lighthouse-counter" :aria-label="$t('islandGame.map.collectedCardsAria')">
                <svg viewBox="0 0 24 24" class="lighthouse-counter-icon" aria-hidden="true">
                    <path d="M12 2l7 3v6c0 4.4-3 8.6-7 11-4-2.4-7-6.6-7-11V5l7-3z"/>
                </svg>
                <span>{{ collectedCards }}/{{ totalCards }}</span>
            </span>

            <!-- nápověda, že na maják lze kliknout (klik řeší obalující prvek) -->
            <span class="lighthouse-hint" :class="{ 'lighthouse-hint--hover': lighthouseHovered }" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="lighthouse-hint-icon">
                    <path d="M9 3a2 2 0 012 2v6h1V7a2 2 0 014 0v4h1V9a2 2 0 014 0v6a6 6 0 01-6 6h-2.5a4 4 0 01-3-1.35l-3.6-4.1a2 2 0 012.9-2.75L11 14V5a2 2 0 01-2-2z" fill="currentColor"/>
                </svg>
                <span>{{ $t('islandGame.map.enterLighthouse') }}</span>
            </span>
        </div>

        <button
            v-for="island in islands"
            :key="island.key"
            type="button"
            class="island-wrap absolute w-[23%] cursor-pointer border-0 bg-transparent p-0 transition-transform hover:scale-105 focus:scale-105 focus:outline-none"
            :style="island.position"
            :aria-label="$t('islandGame.map.openIsland', { name: island.name })"
            @click="$emit('open-island', island)"
        >
            <svg class="ripple" viewBox="0 0 220 70" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                <use class="ring ring-outer" href="#wave-outer"/>
                <use class="ring ring-inner" href="#wave-inner"/>
            </svg>
            <img :src="island.image" :alt="island.name" :class="['island-shadow island-bob relative block w-full', island.bobClass]">
            <span class="island-label">
                <span class="hidden sm:inline">{{ island.name }}</span>
                <span class="island-progress">{{ island.tasks.completed }}/{{ island.tasks.total }}</span>
            </span>
        </button>
    </div>
</template>

<script setup>
defineProps({
    islands: { type: Array, default: () => [] },
    clouds: { type: Array, default: () => [] },
    cloudStyle: { type: Function, required: true },
    progress: { type: Number, default: 0 },
    collectedCards: { type: Number, default: 0 },
    totalCards: { type: Number, default: 0 },
    lighthouseImg: { type: String, default: '' },
    lighthouseHovered: { type: Boolean, default: false },
});
defineEmits(['open-island', 'open-lighthouse', 'lighthouse-hover']);
</script>

<style scoped lang="scss">
@use '../styles/shared' as *;

.island-wrap {
    pointer-events: auto;
    /* ostrovy stojí nad rybkami – klik v místě ostrova patří ostrovu, ne rybce */
    z-index: 2;
}

.ripple {
    position: absolute;
    left: 50%;
    bottom: 4%;
    width: 115%;
    aspect-ratio: 220 / 70;
    transform: translateX(-50%);
    pointer-events: none;
    overflow: visible;
}

.ring {
    fill: none;
    stroke: rgba(255, 255, 255, 0.9);
    stroke-linecap: round;
    stroke-linejoin: round;
    vector-effect: non-scaling-stroke;
}

.ring-outer {
    stroke-width: 1.6;
    stroke-dasharray: 38 22;
}

.ring-inner {
    stroke-width: 1.4;
    stroke-dasharray: 28 26;
    stroke-dashoffset: 16;
    stroke-opacity: 0.65;
}

.island-shadow {
    filter:
        drop-shadow(0 6px 4px  rgba(8, 38, 70, 0.55))
        drop-shadow(0 14px 10px rgba(8, 38, 70, 0.25));
}

.island-label {
    position: absolute;
    bottom: -1.5rem;
    left: 50%;
    transform: translateX(-50%);
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.25rem 0.5rem 0.25rem 0.85rem;
    border-radius: 0.5rem;
    background: rgba(255, 255, 255, 0.96);
    color: #11365e;
    font-size: 0.875rem;
    font-weight: 600;
    line-height: 1.2;
    letter-spacing: 0.01em;
    white-space: nowrap;
    box-shadow:
        0 2px 6px rgba(0, 20, 50, 0.35),
        inset 0 0 0 1px rgba(20, 74, 120, 0.15);
    pointer-events: none;
    z-index: 2;
}

@media (max-width: 639px) {
    .island-label {
        padding: 0.2rem 0.35rem;
    }
}

/* telefon naležato: nízká scéna ořezávala titulky spodních ostrovů (scéna má overflow:hidden kvůli paprskům majáku).
   Pole ostrovů povytáhneme ode dna a titulek zmenšíme, ať se popisky vejdou nad spodní hranu scény. */
@media (orientation: landscape) and (max-height: 600px) {
    .island-field {
        bottom: 2.25rem;
    }

    .island-label {
        bottom: -1.1rem;
        font-size: 0.8rem;
        padding: 0.15rem 0.3rem;
    }
}

@keyframes island-bob {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50%      { transform: translateY(-5px) rotate(0.4deg); }
}

@keyframes lighthouse-bob {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-3px); }
}

.island-bob {
    animation: island-bob 5s ease-in-out infinite;
    transform-origin: 50% 80%;
    will-change: transform;
}

.lighthouse-bob {
    animation: lighthouse-bob 7s ease-in-out infinite;
    will-change: transform;
    backface-visibility: hidden;
}

.island-1 { animation-duration: 5.4s; animation-delay: 0s;    }
.island-2 { animation-duration: 6.0s; animation-delay: -1.2s; }
.island-3 { animation-duration: 5.8s; animation-delay: -2.0s; }
.island-4 { animation-duration: 6.4s; animation-delay: -0.8s; }

/* --- maják: mlha, paprsky, záře, počítadlo karet bezpečí --- */
.lighthouse-wrap {
    z-index: 3;
    cursor: pointer;
    outline: none;
}

/* maják se při najetí myší / fokusu rozzáří a mírně povyroste – signál, že je klikatelný (mraky se navíc rozestoupí) */
.lighthouse-figure {
    transform-origin: 50% 88%; /* roste od základny */
    transition: transform 0.3s ease;
}

.lighthouse-wrap:hover .lighthouse-figure,
.lighthouse-wrap:focus-visible .lighthouse-figure {
    transform: scale(1.07);
}

.lighthouse-wrap .lighthouse-bob {
    transition: filter 0.3s ease;
}

.lighthouse-wrap:hover .lighthouse-bob,
.lighthouse-wrap:focus-visible .lighthouse-bob {
    filter:
        drop-shadow(0 6px 4px rgba(8, 38, 70, 0.55))
        drop-shadow(0 0 22px rgba(255, 232, 150, 0.85));
}

.lighthouse-cloud {
    position: absolute;
    pointer-events: none;
    transition: opacity 0.7s ease, transform 0.7s ease;
    z-index: 2;
}

.lighthouse-cloud-img {
    display: block;
    width: 100%;
    height: auto;
    fill: #fff;
    filter: drop-shadow(0 6px 7px rgba(70, 95, 120, 0.28));
    will-change: transform;
}

.cloud-float-a { animation: cloud-float-a 13s ease-in-out infinite; }
.cloud-float-b { animation: cloud-float-b 16s ease-in-out infinite; }
.cloud-float-c { animation: cloud-float-c 14s ease-in-out infinite; }

@keyframes cloud-float-a {
    0%, 100% { transform: translate(0, 0);      }
    50%      { transform: translate(3.5%, -4%); }
}

@keyframes cloud-float-b {
    0%, 100% { transform: translate(0, 0);      }
    50%      { transform: translate(-4%, 3.5%); }
}

@keyframes cloud-float-c {
    0%, 100% { transform: translate(0, 0);     }
    50%      { transform: translate(4%, 2.5%); }
}

.lighthouse-beam {
    position: absolute;
    top: 23%;
    left: 46%;
    width: 82%;
    height: 205%;
    transform-origin: 50% 0;
    background: linear-gradient(
        to bottom,
        rgba(255, 242, 195, 0.62) 0%,
        rgba(255, 236, 170, 0.4) 48%,
        rgba(255, 232, 150, 0.18) 82%,
        rgba(255, 232, 150, 0) 100%
    );
    clip-path: polygon(50% 0%, 100% 100%, 0% 100%);
    mix-blend-mode: screen;
    pointer-events: none;
    transition: opacity 0.6s ease;
    z-index: 3;
}

.lighthouse-glow {
    position: absolute;
    top: 23%;
    left: 46%;
    width: 125%;
    aspect-ratio: 1;
    transform: translate(-50%, -50%);
    border-radius: 50%;
    background: radial-gradient(
        circle,
        rgba(255, 243, 200, 0.95) 0%,
        rgba(255, 224, 140, 0.5) 26%,
        rgba(255, 224, 140, 0) 66%
    );
    mix-blend-mode: screen;
    pointer-events: none;
    transition: opacity 0.7s ease;
    animation: lamp-pulse 3.6s ease-in-out infinite;
    z-index: 4;
}

@keyframes lamp-pulse {
    0%, 100% { transform: translate(-50%, -50%) scale(1);    }
    50%      { transform: translate(-50%, -50%) scale(1.09); }
}

.lighthouse-counter {
    position: absolute;
    bottom: -1.5rem;
    left: 50%;
    transform: translateX(-50%);
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.28rem 0.7rem 0.28rem 0.5rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.96);
    color: #11365e;
    font-size: 0.9rem;
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
    box-shadow:
        0 2px 8px rgba(0, 20, 50, 0.35),
        inset 0 0 0 1px rgba(20, 74, 120, 0.15);
    font-variant-numeric: tabular-nums;
    z-index: 5;
}

.lighthouse-counter-icon {
    width: 1.05rem;
    height: 1.05rem;
    fill: #f59e0b;
}

@media (max-width: 639px) {
    .lighthouse-counter {
        font-size: 0.78rem;
        padding: 0.2rem 0.5rem 0.2rem 0.35rem;
        bottom: -1.1rem;
    }
}

/* nápověda na středu majáku – ukáže se, až se po najetí myší rozestoupí mraky */
.lighthouse-hint {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.96);
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.9rem;
    border-radius: 9999px;
    background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%);
    color: #3a2a05;
    font-size: 0.85rem;
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
    box-shadow:
        0 4px 16px rgba(245, 158, 11, 0.5),
        inset 0 0 0 1px rgba(255, 255, 255, 0.5);
    pointer-events: none;
    z-index: 6;
    /* schovaná, dokud hráč nenajede na maják */
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.35s ease, transform 0.35s ease, visibility 0.35s ease;
}

.lighthouse-hint-icon {
    width: 1.05rem;
    height: 1.05rem;
    color: #6b4f10;
}

/* po rozestoupení mraků (hover na maják) nápověda naběhne na středu majáku – s mírným zpožděním, ať jdou mraky první */
.lighthouse-hint--hover {
    opacity: 1;
    visibility: visible;
    transform: translate(-50%, -50%) scale(1);
    transition-delay: 0.15s;
    animation: lighthouse-hint-pulse 1.8s ease-in-out 0.15s infinite;
}

@keyframes lighthouse-hint-pulse {
    0%, 100% { transform: translate(-50%, -50%) scale(1);    }
    50%      { transform: translate(-50%, -50%) scale(1.06); }
}

@media (max-width: 639px) {
    .lighthouse-hint {
        font-size: 0.72rem;
        padding: 0.3rem 0.7rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .island-bob,
    .lighthouse-bob,
    .lighthouse-cloud-img,
    .lighthouse-glow { animation: none; }
}
</style>
