<template>
    <!-- modal majáku – interiér majáku (ukázka); v pozadí zůstává moře s ostrovy -->
    <transition name="modal-fade">
        <div
            v-if="open"
            class="lighthouse-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-3"
            role="dialog"
            aria-modal="true"
            :aria-label="$t('islandGame.lighthouse.aria')"
            @click.self="$emit('close')"
        >
            <button
                type="button"
                class="absolute top-3 left-3 z-10 inline-flex items-center gap-2 rounded-lg bg-white/95 px-2.5 py-[0.3rem] text-[0.95rem] font-semibold leading-tight text-blue-900 shadow-md transition hover:bg-white sm:px-4 sm:py-2 sm:text-base"
                :aria-label="$t('islandGame.common.backToMap')"
                @click="$emit('close')"
            >
                <span aria-hidden="true">←</span>
                <span class="hidden sm:inline">{{ $t('islandGame.common.backToMap') }}</span>
            </button>

            <div class="lighthouse-modal relative">
                <div class="lighthouse-modal-stage">
                    <img :src="interiorImg" :alt="$t('islandGame.lighthouse.interiorAlt')" class="lighthouse-modal-img">

                    <!-- záchranný kruh visící na háčku na stěně – houpe se a otevírá důležité kontakty -->
                    <button
                        type="button"
                        class="lighthouse-hotspot lighthouse-hotspot--ring"
                        :aria-label="$t('islandGame.lighthouse.contacts')"
                        @click="$emit('open-contacts')"
                    >
                        <span class="lighthouse-ring-swing">
                            <img :src="lifeRingImg" alt="" class="lighthouse-hotspot-img">
                        </span>
                        <span class="lighthouse-hotspot-label lighthouse-basket-label">
                            <span class="hidden sm:inline">{{ $t('islandGame.lighthouse.contacts') }}</span>
                            <span class="island-progress">{{ importantContactsCount }}</span>
                        </span>
                    </button>

                    <!-- nástěnka na stěně – otevírá sbírku karet bezpečí -->
                    <button
                        type="button"
                        class="lighthouse-hotspot lighthouse-hotspot--board"
                        :aria-label="$t('islandGame.lighthouse.cards')"
                        @click="$emit('open-cards')"
                    >
                        <img :src="corkboardImg" alt="" class="lighthouse-hotspot-img">
                        <!-- na korku ukazujeme max 3 karty, zbytek je v modalu po kliknutí -->
                        <span
                            v-for="(card, idx) in boardSafetyCards"
                            :key="`board-card-${idx}`"
                            class="board-card"
                            :style="boardCardStyle(idx)"
                        >
                            <img :src="safetyCardImg" alt="" class="board-card-img">
                        </span>
                        <span class="lighthouse-hotspot-label">
                            <span class="hidden sm:inline">{{ $t('islandGame.lighthouse.cards') }}</span>
                            <span class="island-progress">{{ collectedSafetyCardsCount }}</span>
                        </span>
                    </button>

                    <!-- koš na ryby na stole – ulovené ryby v něm leží; klik otevře jejich přehled -->
                    <button
                        type="button"
                        class="lighthouse-basket lighthouse-hotspot--basket"
                        :aria-label="$t('islandGame.lighthouse.basket')"
                        @click="$emit('open-basket')"
                    >
                        <img :src="fishingBasketImg" alt="" class="lighthouse-basket-img">
                        <!-- ulovené ryby ležící v koši -->
                        <span
                            v-for="cf in caughtFish"
                            :key="cf.key"
                            class="basket-fish"
                            :style="{
                                left: cf.x,
                                top: cf.y,
                                transform: `translate(-50%, -50%) rotate(${cf.rot}deg) scale(${cf.scale})`,
                                filter: `hue-rotate(${cf.hue}deg)`,
                            }"
                        >
                            <svg class="basket-fish-svg" viewBox="0 0 64 40">
                                <path d="M20 20C20 11 30 6 42 6s18 5 18 14-8 14-18 14-22-5-22-14z" fill="#ff9f43"/>
                                <path d="M20 20 6 9l5 11-5 11z" fill="#f97316"/>
                                <path d="M40 11q7 0 11 4-6 1-11-1z" fill="#ffbe76"/>
                                <circle cx="51" cy="16" r="2.4" fill="#1f2d3d"/>
                            </svg>
                        </span>
                        <span class="lighthouse-hotspot-label lighthouse-basket-label">
                            <span class="hidden sm:inline">{{ $t('islandGame.lighthouse.basket') }}</span>
                            <span class="island-progress">{{ caughtFish.length }}</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    open: { type: Boolean, default: false },
    interiorImg: { type: String, default: '' },
    lifeRingImg: { type: String, default: '' },
    corkboardImg: { type: String, default: '' },
    fishingBasketImg: { type: String, default: '' },
    safetyCardImg: { type: String, default: '' },
    boardSafetyCards: { type: Array, default: () => [] },
    boardCardStyle: { type: Function, required: true },
    collectedSafetyCardsCount: { type: Number, default: 0 },
    importantContactsCount: { type: Number, default: 0 },
    caughtFish: { type: Array, default: () => [] },
});
defineEmits(['close', 'open-contacts', 'open-cards', 'open-basket']);
</script>

<style scoped lang="scss">
@use '../styles/shared' as *;

/* vnitřní transform modalu (základní fade řeší sdílený _shared.scss) */
.modal-fade-enter-active .lighthouse-modal,
.modal-fade-leave-active .lighthouse-modal {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-fade-enter-from .lighthouse-modal,
.modal-fade-leave-to .lighthouse-modal {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
}

/* modal majáku – bez tmavého překryvu, jen jemně rozmazané moře s ostrovy v pozadí */
.lighthouse-modal-backdrop {
    background: rgba(8, 38, 70, 0.12);
    backdrop-filter: blur(7px);
    -webkit-backdrop-filter: blur(7px);
}

/* modal majáku – obrázek interiéru v dřevěno-mosazném rámu, ať to působí jako uvnitř majáku */
.lighthouse-modal {
    position: relative;
    display: flex;
    padding: clamp(0.7rem, 1.8vw, 1.5rem);
    border-radius: 1.1rem;
    background:
        linear-gradient(145deg, #caa15a 0%, #9c6b2f 42%, #6f4a1e 100%);
    box-shadow:
        0 24px 48px rgba(8, 38, 70, 0.55),
        inset 0 2px 2px rgba(255, 255, 255, 0.4),
        inset 0 -3px 6px rgba(0, 0, 0, 0.35);
}

/* nýtky v rozích rámu – nautický detail */
.lighthouse-modal::after {
    content: '';
    position: absolute;
    inset: clamp(0.32rem, 0.8vw, 0.6rem);
    border-radius: 0.7rem;
    pointer-events: none;
    background:
        radial-gradient(circle, #f4e3b0 0%, #b88a2c 55%, #6b4f10 100%) no-repeat left top,
        radial-gradient(circle, #f4e3b0 0%, #b88a2c 55%, #6b4f10 100%) no-repeat right top,
        radial-gradient(circle, #f4e3b0 0%, #b88a2c 55%, #6b4f10 100%) no-repeat left bottom,
        radial-gradient(circle, #f4e3b0 0%, #b88a2c 55%, #6b4f10 100%) no-repeat right bottom;
    background-size: 0.5rem 0.5rem;
    box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.2);
}

.lighthouse-modal-stage {
    position: relative;
    display: block;
    line-height: 0;
}

.lighthouse-modal-img {
    display: block;
    width: auto;
    height: auto;
    max-width: 92vw;
    max-height: 86vh;
    border-radius: 0.5rem;
    box-shadow:
        0 0 0 2px rgba(60, 38, 12, 0.7),
        0 0 0 4px rgba(244, 227, 176, 0.35),
        0 8px 18px rgba(0, 0, 0, 0.4);
}

/* koš ulovených ryb na stole (dekorace) – ryby v něm leží */
.lighthouse-basket {
    position: absolute;
    top: 52.5%;
    left: 25%;
    width: 17%;
    display: flex;
    flex-direction: column;
    align-items: center;
    /* je to tlačítko – vyresetujeme nativní vzhled */
    padding: 0;
    border: 0;
    background: transparent;
    cursor: pointer;
    line-height: 1;
    user-select: none;
    z-index: 2;
}

.lighthouse-basket-img {
    display: block;
    width: 100%;
    height: auto;
    filter: drop-shadow(0 7px 6px rgba(8, 38, 70, 0.35));
    transform-origin: 50% 100%;
    transition: transform 0.25s ease, filter 0.25s ease;
}

/* koš se na hoveru zlehka zvětší a rozzáří – stejně jako nástěnka a kruh */
.lighthouse-basket:hover .lighthouse-basket-img,
.lighthouse-basket:focus-visible .lighthouse-basket-img {
    transform: scale(1.05);
    filter:
        drop-shadow(0 7px 6px rgba(8, 38, 70, 0.4))
        drop-shadow(0 0 10px rgba(255, 238, 170, 0.95));
}

.lighthouse-basket:hover .lighthouse-basket-label,
.lighthouse-basket:focus-visible .lighthouse-basket-label {
    transform: scale(1.06);
    background: #fff;
}

/* jednotlivá ulovená ryba ležící v ústí koše (pozici/rotaci dává inline styl) */
.basket-fish {
    position: absolute;
    width: 26%;
    line-height: 0;
}

.basket-fish-svg {
    display: block;
    width: 100%;
    height: auto;
    filter: drop-shadow(0 2px 2px rgba(8, 38, 70, 0.4));
}

/* získané karty bezpečí napíchané na korku (pozici/natočení dává inline styl) */
.board-card {
    position: absolute;
    width: 27%;
    line-height: 0;
    /* klik patří nástěnce (otevře modal), ne kartě */
    pointer-events: none;
}

.board-card-img {
    display: block;
    width: 100%;
    height: auto;
    border-radius: 2px;
    filter: drop-shadow(0 2px 3px rgba(8, 38, 70, 0.45));
}

/* napínáček uprostřed horního okraje karty */
.board-card::after {
    content: '';
    position: absolute;
    top: 4%;
    left: 50%;
    width: 14%;
    aspect-ratio: 1;
    transform: translate(-50%, -50%);
    border-radius: 50%;
    background: radial-gradient(circle at 35% 30%, #ff7a7a 0%, #d83a3a 60%, #a31f1f 100%);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
}

/* interaktivní prvky ve scéně majáku (záchranný kruh apod.) */
.lighthouse-hotspot {
    position: absolute;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0;
    border: 0;
    background: transparent;
    cursor: pointer;
    line-height: 1;
    z-index: 2;
}

/* poloha záchranného kruhu na háčku na stěně vpravo od dveří */
.lighthouse-hotspot--ring {
    top: 6%;
    left: 61%;
    width: 13%;
}

/* kruh se na hoveru zlehka zvětší, stejně jako nástěnka */
.lighthouse-hotspot--ring .lighthouse-hotspot-img {
    transition: transform 0.25s ease, filter 0.25s ease;
}

.lighthouse-hotspot--ring:hover .lighthouse-hotspot-img,
.lighthouse-hotspot--ring:focus-visible .lighthouse-hotspot-img {
    transform: scale(1.05);
}

/* poloha nástěnky na stěně vlevo od dveří (frontálně, v jedné linii se dveřmi) */
.lighthouse-hotspot--board {
    top: 10%;
    left: 19.5%;
    width: 21%;
}

.lighthouse-hotspot--board .lighthouse-hotspot-img {
    transition: transform 0.25s ease, filter 0.25s ease;
}

.lighthouse-hotspot--board:hover .lighthouse-hotspot-img,
.lighthouse-hotspot--board:focus-visible .lighthouse-hotspot-img {
    transform: scale(1.04);
}

/* popisek blíž k nástěnce (deska je natočená, spodní střed je výš než roh) */
.lighthouse-hotspot--board .lighthouse-hotspot-label {
    position: absolute;
    left: 50%;
    top: 82%;
    transform: translateX(-50%);
    margin-top: 0;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.lighthouse-hotspot--board:hover .lighthouse-hotspot-label,
.lighthouse-hotspot--board:focus-visible .lighthouse-hotspot-label {
    transform: translateX(-50%) scale(1.06);
}

.lighthouse-hotspot-img {
    display: block;
    width: 100%;
    height: auto;
    transform-origin: 50% 5%;
    filter: drop-shadow(0 6px 8px rgba(8, 38, 70, 0.45));
    transition: filter 0.25s ease;
}

/* obal kruhu se zlehka houpe jako na háčku (pivot nahoře u háčku) */
.lighthouse-ring-swing {
    display: block;
    width: 100%;
    /* pivot v místě, kde lano leží na háčku, ať se kruh houpe okolo háčku */
    transform-origin: 48% 8%;
    animation: life-ring-swing 4.5s ease-in-out infinite;
}

@keyframes life-ring-swing {
    0%, 100% { transform: rotate(-5deg); }
    50%      { transform: rotate(5deg);  }
}

.lighthouse-hotspot:hover .lighthouse-hotspot-img,
.lighthouse-hotspot:focus-visible .lighthouse-hotspot-img {
    filter:
        drop-shadow(0 6px 8px rgba(8, 38, 70, 0.5))
        drop-shadow(0 0 10px rgba(255, 238, 170, 0.95));
}

.lighthouse-hotspot-label {
    margin-top: 0.4rem;
    padding: 0.22rem 0.6rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.96);
    color: #11365e;
    font-size: clamp(0.62rem, 1.1vw, 0.85rem);
    font-weight: 700;
    /* explicitně, ať popisek nepřebírá line-height: 0 ze stage (jinak text v <div> košíku zkolaboval) */
    line-height: 1.15;
    white-space: nowrap;
    box-shadow:
        0 2px 6px rgba(0, 20, 50, 0.35),
        inset 0 0 0 1px rgba(20, 74, 120, 0.15);
    transition: transform 0.2s ease, background 0.2s ease;
}

.lighthouse-hotspot:hover .lighthouse-hotspot-label,
.lighthouse-hotspot:focus-visible .lighthouse-hotspot-label {
    transform: scale(1.06);
    background: #fff;
}

/* popisek koše – text + žlutý chip s počtem vedle sebe */
.lighthouse-basket-label {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
</style>
