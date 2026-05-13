<template>
    <div class="island-page relative overflow-hidden">
        <svg
            v-for="(w, i) in seaWavelets"
            :key="`page-wavelet-${i}`"
            :style="{ top: w.top, left: w.left, width: w.width, opacity: w.opacity }"
            class="sea-wavelet absolute"
            viewBox="0 0 40 8"
            aria-hidden="true"
        >
            <path
                :d="seaWaveletPath"
                fill="none"
                stroke="currentColor"
                stroke-width="1.4"
                stroke-linecap="round"
                vector-effect="non-scaling-stroke"
            />
        </svg>

        <button
            v-if="selectedIsland"
            type="button"
            class="absolute top-3 left-3 z-20 inline-flex items-center gap-2 rounded-lg bg-white/95 px-2.5 py-[0.3rem] text-[0.95rem] font-semibold leading-tight text-blue-900 shadow-md transition hover:bg-white sm:px-4 sm:py-2 sm:text-base"
            @click="close"
            aria-label="Zpět"
        >
            <span aria-hidden="true">←</span>
            <span class="hidden sm:inline">Zpět</span>
        </button>

        <h2 v-if="selectedIsland" class="island-detail-label absolute top-3 left-1/2 z-20 -translate-x-1/2">
            {{ selectedIsland.name }}
            <span class="island-progress island-progress--lg">{{ selectedIsland.tasks.completed }}/{{ selectedIsland.tasks.total }}</span>
        </h2>

        <div class="container mx-auto px-4 py-6 relative">

            <div class="island-scene relative mx-auto aspect-[3/2] w-full max-w-5xl overflow-hidden">
            <div class="sea-caustics" aria-hidden="true"></div>

            <svg width="0" height="0" class="absolute" aria-hidden="true" focusable="false">
                <defs>
                    <path id="wave-outer" :d="waveOuterPath"/>
                    <path id="wave-inner" :d="waveInnerPath"/>
                    <linearGradient id="lock-gradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#fbbf24"/>
                        <stop offset="100%" stop-color="#f59e0b"/>
                    </linearGradient>
                </defs>
            </svg>

            <transition name="scene-fade">
                <div v-if="!selectedIsland" key="scene" class="absolute inset-0">
                    <div class="island-wrap absolute top-1/2 left-1/2 w-[22%] -translate-x-1/2 -translate-y-1/2">
                        <svg class="ripple" viewBox="0 0 220 70" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                            <use class="ring ring-outer" href="#wave-outer"/>
                            <use class="ring ring-inner" href="#wave-inner"/>
                        </svg>
                        <img :src="lighthouseImg" alt="Maják" class="island-shadow lighthouse-bob relative block w-full">
                    </div>

                    <button
                        v-for="island in islands"
                        :key="island.key"
                        type="button"
                        class="island-wrap absolute w-[23%] cursor-pointer border-0 bg-transparent p-0 transition-transform hover:scale-105 focus:scale-105 focus:outline-none"
                        :style="island.position"
                        :aria-label="`Otevřít ostrov: ${island.name}`"
                        @click="open(island)"
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

                <div v-else key="detail" class="absolute inset-0 flex flex-col p-3 sm:p-6">

                    <div class="flex min-h-0 flex-1 items-center justify-center overflow-hidden">
                        <div class="island-stage relative">
                            <img
                                :src="selectedIsland.detailImage || selectedIsland.image"
                                :alt="selectedIsland.name"
                                class="island-shadow-soft block h-full w-full"
                            >
                            <img
                                v-for="(pos, i) in selectedIsland.lampPositions"
                                :key="`lamp-${i}`"
                                :src="lampImg"
                                :style="pos"
                                class="island-lamp absolute"
                                alt=""
                            >
                            <button
                                v-for="(pos, i) in selectedIsland.pathButtonPositions"
                                :key="`btn-${i}`"
                                type="button"
                                :style="pos"
                                class="path-button absolute"
                                :class="{ 'path-button--locked': i > 0 }"
                                :disabled="i > 0"
                                :aria-label="`Úkol ${i + 1}`"
                                @click="openQuestion(i)"
                            >
                                <img
                                    :src="buttonImgs[i]"
                                    :alt="`Úkol ${i + 1}`"
                                    class="block w-full"
                                >
                                <svg
                                    v-if="i > 0"
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

                    <slot name="detail" :island="selectedIsland"></slot>
                </div>
            </transition>
        </div>

        <transition name="modal-fade">
            <div
                v-if="activeQuestion"
                class="question-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                :aria-label="activeQuestion.title"
                @click.self="closeQuestion"
            >
                <div class="question-modal relative w-full max-w-lg rounded-2xl bg-white p-6 sm:p-8 shadow-2xl">
                    <button
                        type="button"
                        class="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-full text-blue-900 transition hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                        aria-label="Zavřít"
                        @click="closeQuestion"
                    >
                        <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
                            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>

                    <h3 class="mb-4 pr-8 text-xl font-bold leading-tight text-blue-900">
                        {{ activeQuestion.title }}
                    </h3>

                    <p class="mb-5 text-sm leading-relaxed text-slate-700">
                        {{ activeQuestion.description }}
                    </p>

                    <ul class="space-y-2">
                        <li
                            v-for="(option, idx) in activeQuestion.options"
                            :key="`opt-${idx}`"
                        >
                            <button
                                type="button"
                                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-medium text-slate-800 transition hover:border-blue-400 hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                                @click="closeQuestion"
                            >
                                {{ option }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </transition>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';

const asset = (filename) => new URL(`../../../../images/islands/${filename}`, import.meta.url).href;
const lampImg = new URL('../../../../images/turned_off_lamp.png', import.meta.url).href;
const buttonImgs = [
    new URL('../../../../images/button1.png', import.meta.url).href,
    new URL('../../../../images/button2.png', import.meta.url).href,
    new URL('../../../../images/button3.png', import.meta.url).href,
    new URL('../../../../images/button4.png', import.meta.url).href,
    new URL('../../../../images/button5.png', import.meta.url).href,
];

const defaultPathButtonPositions = [
    { top: '63%', left: '38%' },
    { top: '56%', left: '46%' },
    { top: '49%', left: '54%' },
    { top: '42%', left: '61%' },
    { top: '35%', left: '69%' },
];

const defaultLampPositions = [
    // { top: '62%', left: '47%' },
    // { top: '56%', left: '52%' },
    // { top: '50%', left: '57%' },
    // { top: '44%', left: '62%' },
    { top: '38%', left: '58%' },
];

const seaWaveletPath = 'M2,4 Q7,1 12,4 T22,4 T32,4 T38,4';

const waveletHeightFactor = 0.3;

const boxesOverlap = (a, b, pad = 0) => !(
    a.left + a.width + pad < b.left ||
    b.left + b.width + pad < a.left ||
    a.top + a.height + pad < b.top ||
    b.top + b.height + pad < a.top
);

const generateWavelets = (count) => {
    const placed = [];
    let safety = 0;
    while (placed.length < count && safety < count * 80) {
        safety++;
        const width = 3 + Math.random() * 4;
        const height = width * waveletHeightFactor;
        const left = 1 + Math.random() * (98 - width);
        const top = 1 + Math.random() * (98 - height);
        const candidate = { top, left, width, height };

        if (placed.some(p => boxesOverlap(candidate, p, 1.5))) continue;

        placed.push(candidate);
    }
    return placed.map(w => ({
        top: `${w.top.toFixed(1)}%`,
        left: `${w.left.toFixed(1)}%`,
        width: `${w.width.toFixed(1)}%`,
        opacity: (0.15 + Math.random() * 0.25).toFixed(2),
    }));
};

const seaWavelets = generateWavelets(70);

const waveOuterPath = 'M210.0,38.0L211.3,39.3L211.6,40.8L210.8,41.9L209.1,42.5L207.1,42.5L205.4,42.5L204.1,43.2L203.2,44.7L202.4,46.8L201.3,49.0L199.6,50.6L197.3,51.3L194.6,51.1L191.7,50.5L188.8,50.2L186.0,50.7L183.4,52.2L180.7,54.3L177.9,56.3L174.7,57.7L171.2,58.1L167.5,57.6L163.6,56.7L159.7,56.0L155.9,56.3L152.1,57.4L148.3,59.2L144.3,61.0L140.2,62.1L136.0,62.1L131.7,61.2L127.3,59.9L123.0,58.9L118.7,58.7L114.3,59.6L110.0,61.0L105.6,62.4L101.2,63.1L96.9,62.7L92.6,61.4L88.4,59.7L84.2,58.3L80.1,57.8L75.9,58.2L71.7,59.2L67.6,60.3L63.6,60.6L59.7,59.8L56.2,58.1L52.8,56.1L49.5,54.4L46.1,53.5L42.7,53.6L39.3,54.3L35.9,54.9L32.8,54.9L30.1,53.8L27.8,51.9L25.9,49.7L24.1,47.7L22.2,46.6L20.0,46.5L17.6,46.8L15.3,47.1L13.4,46.7L12.2,45.4L11.8,43.5L12.1,41.5L12.5,40.1L12.4,39.2L11.5,38.7L10.0,38.0L8.7,36.7L8.4,35.2L9.2,34.1L10.9,33.5L12.9,33.5L14.6,33.5L15.9,32.8L16.8,31.3L17.6,29.2L18.7,27.0L20.4,25.4L22.7,24.7L25.4,24.9L28.3,25.5L31.2,25.8L34.0,25.3L36.6,23.8L39.3,21.7L42.1,19.7L45.3,18.3L48.8,17.9L52.5,18.4L56.4,19.3L60.3,20.0L64.1,19.7L67.9,18.6L71.7,16.8L75.7,15.0L79.8,13.9L84.0,13.9L88.3,14.8L92.7,16.1L97.0,17.1L101.3,17.3L105.7,16.4L110.0,15.0L114.4,13.6L118.8,12.9L123.1,13.3L127.4,14.6L131.6,16.3L135.8,17.7L139.9,18.2L144.1,17.8L148.3,16.8L152.4,15.7L156.4,15.4L160.3,16.2L163.8,17.9L167.2,19.9L170.5,21.6L173.9,22.5L177.3,22.4L180.7,21.7L184.1,21.1L187.2,21.1L189.9,22.2L192.2,24.1L194.1,26.3L195.9,28.3L197.8,29.4L200.0,29.5L202.4,29.2L204.7,28.9L206.6,29.3L207.8,30.6L208.2,32.5L207.9,34.5L207.5,35.9L207.6,36.8L208.5,37.3Z';
const waveInnerPath = 'M188.0,38.0L188.9,38.9L189.3,40.1L188.8,41.0L187.7,41.5L186.3,41.6L184.8,41.4L183.5,41.4L182.4,41.9L181.5,43.0L180.6,44.5L179.5,46.1L178.1,47.5L176.3,48.3L174.3,48.5L172.0,48.2L169.6,47.7L167.2,47.4L164.8,47.6L162.4,48.3L160.0,49.6L157.6,51.2L154.9,52.5L152.1,53.3L149.2,53.4L146.1,52.9L142.9,52.2L139.7,51.5L136.5,51.3L133.3,51.6L130.1,52.6L126.9,53.8L123.6,54.9L120.2,55.6L116.8,55.6L113.4,55.0L110.0,54.0L106.6,53.0L103.2,52.2L99.9,52.1L96.5,52.6L93.1,53.5L89.8,54.4L86.4,54.9L83.2,54.8L80.0,54.1L77.0,52.8L74.1,51.4L71.2,50.3L68.3,49.7L65.5,49.7L62.6,50.2L59.8,50.9L57.0,51.2L54.5,51.1L52.2,50.2L50.1,48.9L48.2,47.3L46.5,45.9L44.8,44.9L43.0,44.5L41.1,44.7L39.2,45.0L37.4,45.3L35.8,45.0L34.7,44.2L34.1,42.9L34.0,41.3L34.1,40.0L34.1,39.1L33.9,38.7L33.1,38.5L32.0,38.0L31.1,37.1L30.7,35.9L31.2,35.0L32.3,34.5L33.7,34.4L35.2,34.6L36.5,34.6L37.6,34.1L38.5,33.0L39.4,31.5L40.5,29.9L41.9,28.5L43.7,27.7L45.7,27.5L48.0,27.8L50.4,28.3L52.8,28.6L55.2,28.4L57.6,27.7L60.0,26.4L62.4,24.8L65.1,23.5L67.9,22.7L70.8,22.6L73.9,23.1L77.1,23.8L80.3,24.5L83.5,24.7L86.7,24.4L89.9,23.4L93.1,22.2L96.4,21.1L99.8,20.4L103.2,20.4L106.6,21.0L110.0,22.0L113.4,23.0L116.8,23.8L120.1,23.9L123.5,23.4L126.9,22.5L130.2,21.6L133.6,21.1L136.8,21.2L140.0,21.9L143.0,23.2L145.9,24.6L148.8,25.7L151.7,26.3L154.5,26.3L157.4,25.8L160.2,25.1L163.0,24.8L165.5,24.9L167.8,25.8L169.9,27.1L171.8,28.7L173.5,30.1L175.2,31.1L177.0,31.5L178.9,31.3L180.8,31.0L182.6,30.7L184.2,31.0L185.3,31.8L185.9,33.1L186.0,34.7L185.9,36.0L185.9,36.9L186.1,37.3L186.9,37.5Z';

const lighthouseImg = asset('lighthouse.webp');

const islands = reactive([
    {
        key: 'digitalni-pasti',
        name: 'Ostrov digitálních pastí',
        image: asset('digital_traps.webp'),
        detailImage: asset('detail/digital_traps.webp'),
        position: { top: '18%', left: '14%' },
        bobClass: 'island-1',
        tasks: { completed: 0, total: 5 },
        lampPositions: defaultLampPositions,
        pathButtonPositions: defaultPathButtonPositions,
    },
    {
        key: 'klamave-zpravy',
        name: 'Ostrov klamavých zpráv',
        image: asset('deceptive_news.webp'),
        detailImage: asset('detail/deceptive_news.webp'),
        position: { top: '18%', right: '14%' },
        bobClass: 'island-2',
        tasks: { completed: 0, total: 5 },
        lampPositions: defaultLampPositions,
        pathButtonPositions: defaultPathButtonPositions,
    },
    {
        key: 'lasky',
        name: 'Ostrov zneužitých citů',
        image: asset('exploited_emotions.webp'),
        detailImage: asset('detail/exploited_emotions.webp'),
        position: { bottom: '14%', left: '14%' },
        bobClass: 'island-3',
        tasks: { completed: 0, total: 5 },
        lampPositions: defaultLampPositions,
        pathButtonPositions: defaultPathButtonPositions,
    },
    {
        key: 'penize',
        name: 'Ostrov falešného bohatství',
        image: asset('fake_wealth.webp'),
        detailImage: asset('detail/fake_wealth.webp'),
        position: { right: '14%', bottom: '14%' },
        bobClass: 'island-4',
        tasks: { completed: 0, total: 5 },
        lampPositions: defaultLampPositions,
        pathButtonPositions: defaultPathButtonPositions,
    },
]);

const selectedIsland = ref(null);
const activeQuestion = ref(null);

const open = (island) => {
    selectedIsland.value = island;
};

const close = () => {
    selectedIsland.value = null;
};

const fakeQuestion = (buttonIndex) => ({
    title: `Úkol ${buttonIndex + 1}: Rozpoznáš podvod?`,
    description:
        'Dostali jste e-mail z banky s odkazem na "ověření účtu". ' +
        'Zpráva obsahuje překlepy a adresa odesílatele vypadá podezřele. ' +
        'Co uděláte jako první?',
    options: [
        'Kliknu na odkaz a zadám přihlašovací údaje.',
        'Zavolám na číslo uvedené v e-mailu.',
        'Ignoruji e-mail a kontaktuji banku přes oficiální web/aplikaci.',
        'Přepošlu e-mail kamarádovi pro radu.',
    ],
});

const openQuestion = (buttonIndex) => {
    if (buttonIndex > 0) return;
    activeQuestion.value = fakeQuestion(buttonIndex);
};

const closeQuestion = () => {
    activeQuestion.value = null;
};
</script>

<style scoped>
.island-page {
    min-height: 100vh;
    background: radial-gradient(ellipse at 50% 30%, #4ea6d8 0%, #2c79b0 55%, #144a78 100%);
}

.island-scene {
    background: transparent;
}

.sea-wavelet {
    color: rgba(255, 255, 255, 0.85);
    pointer-events: none;
    user-select: none;
}

.sea-caustics {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
        radial-gradient(ellipse 30% 6% at 22% 78%, rgba(255, 255, 255, 0.18), transparent 70%),
        radial-gradient(ellipse 22% 5% at 78% 22%, rgba(255, 255, 255, 0.14), transparent 70%),
        radial-gradient(ellipse 18% 4% at 60% 60%, rgba(255, 255, 255, 0.12), transparent 70%),
        radial-gradient(ellipse 14% 4% at 38% 40%, rgba(255, 255, 255, 0.10), transparent 70%),
        radial-gradient(ellipse 60% 50% at 50% 110%, rgba(0, 20, 50, 0.35), transparent 70%);
    mix-blend-mode: screen;
}

.island-wrap {
    pointer-events: auto;
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

.island-lamp {
    width: 5%;
    transform: translate(-50%, -100%);
    filter: drop-shadow(0 4px 4px rgba(0, 0, 0, 0.45));
    pointer-events: none;
    user-select: none;
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

.path-button:not(.path-button--locked):hover,
.path-button:not(.path-button--locked):focus {
    transform: translate(-50%, -50%) scale(1.12);
    filter: drop-shadow(0 6px 6px rgba(0, 0, 0, 0.55));
}

.path-button--locked {
    cursor: not-allowed;
}

.path-button--locked > img {
    opacity: 0.55;
    filter: grayscale(0.85);
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

.island-progress {
    display: inline-flex;
    align-items: center;
    padding: 0.1rem 0.45rem;
    border-radius: 9999px;
    background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%);
    color: #3a2a05;
    font-size: 0.72rem;
    font-weight: 700;
    line-height: 1;
    letter-spacing: 0.02em;
    box-shadow:
        inset 0 0 0 1px rgba(255, 255, 255, 0.55),
        0 1px 2px rgba(0, 0, 0, 0.2);
    font-variant-numeric: tabular-nums;
}

.island-progress--lg {
    padding: 0.3rem 0.85rem;
    font-size: 1rem;
    border-radius: 9999px;
}

.island-detail-label {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem 0.5rem 1.25rem;
    border-radius: 0.5rem;
    background: rgba(255, 255, 255, 0.96);
    color: #11365e;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
    letter-spacing: 0.01em;
    white-space: nowrap;
    box-shadow:
        0 4px 14px rgba(0, 20, 50, 0.4),
        inset 0 0 0 1px rgba(20, 74, 120, 0.18);
}

@media (max-width: 639px) {
    .island-detail-label {
        gap: 0.45rem;
        padding: 0.3rem 0.5rem 0.3rem 0.75rem;
        font-size: 0.95rem;
    }
    .island-progress--lg {
        padding: 0.2rem 0.6rem;
        font-size: 0.8rem;
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

.scene-fade-enter-active,
.scene-fade-leave-active {
    transition: opacity 0.25s ease;
}

.scene-fade-enter-from,
.scene-fade-leave-to {
    opacity: 0;
}

.question-modal-backdrop {
    background: rgba(8, 38, 70, 0.6);
    backdrop-filter: blur(4px);
}

.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.2s ease;
}

.modal-fade-enter-active .question-modal,
.modal-fade-leave-active .question-modal {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-fade-enter-from .question-modal,
.modal-fade-leave-to .question-modal {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
}

@media (prefers-reduced-motion: reduce) {
    .island-bob,
    .lighthouse-bob { animation: none; }
}
</style>
