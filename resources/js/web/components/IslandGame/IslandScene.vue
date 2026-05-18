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

        <div class="container mx-auto flex min-h-screen items-center justify-center px-4 py-6 relative">

            <div class="island-scene relative mx-auto w-full overflow-hidden">
            <div class="sea-caustics" aria-hidden="true"></div>

            <svg width="0" height="0" class="absolute" aria-hidden="true" focusable="false">
                <defs>
                    <path id="wave-outer" :d="waveOuterPath"/>
                    <path id="wave-inner" :d="waveInnerPath"/>
                    <linearGradient id="lock-gradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#fbbf24"/>
                        <stop offset="100%" stop-color="#f59e0b"/>
                    </linearGradient>
                    <g id="cloud-shape">
                        <ellipse cx="112" cy="92" rx="98" ry="30" fill="#e9f0f6"/>
                        <ellipse cx="64"  cy="72" rx="48" ry="38"/>
                        <ellipse cx="168" cy="70" rx="46" ry="36"/>
                        <ellipse cx="116" cy="54" rx="58" ry="48"/>
                        <ellipse cx="92"  cy="40" rx="34" ry="30"/>
                    </g>
                </defs>
            </svg>

            <transition name="scene-fade">
                <div v-if="!selectedIsland" key="scene" class="absolute inset-0">
                    <div class="island-wrap lighthouse-wrap absolute top-1/2 left-1/2 w-[22%] -translate-x-1/2 -translate-y-1/2">
                        <svg class="ripple" viewBox="0 0 220 70" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                            <use class="ring ring-outer" href="#wave-outer"/>
                            <use class="ring ring-inner" href="#wave-inner"/>
                        </svg>
                        <img :src="lighthouseImg" alt="Maják" class="island-shadow lighthouse-bob relative block w-full">

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
                        <span class="lighthouse-counter" aria-label="Získané karty bezpečí">
                            <svg viewBox="0 0 24 24" class="lighthouse-counter-icon" aria-hidden="true">
                                <path d="M12 2l7 3v6c0 4.4-3 8.6-7 11-4-2.4-7-6.6-7-11V5l7-3z"/>
                            </svg>
                            <span>{{ collectedCards }}/{{ totalCards }}</span>
                        </span>
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

                <div v-else key="detail" class="absolute inset-0 z-10 flex flex-col p-3 sm:p-6">

                    <div class="flex min-h-0 flex-1 items-center justify-center overflow-hidden">
                        <div class="island-stage relative">
                            <img
                                :src="selectedIsland.detailImage || selectedIsland.image"
                                :alt="selectedIsland.name"
                                class="island-shadow-soft block h-full w-full"
                            >
                            <button
                                v-for="(pos, i) in selectedIsland.pathButtonPositions"
                                :key="`btn-${i}`"
                                type="button"
                                :style="pos"
                                class="path-button absolute"
                                :class="{
                                    'path-button--locked': selectedIsland.buttonStates[i] === 'locked',
                                    'path-button--done': selectedIsland.buttonStates[i] === 'green',
                                }"
                                :disabled="selectedIsland.buttonStates[i] === 'locked' || selectedIsland.buttonStates[i] === 'green'"
                                :aria-label="`Úkol ${i + 1}`"
                                @click="openQuestion(i)"
                            >
                                <img
                                    :src="buttonSrc(i, selectedIsland.buttonStates[i])"
                                    :alt="`Úkol ${i + 1}`"
                                    class="block w-full"
                                >
                                <svg
                                    v-if="selectedIsland.buttonStates[i] === 'locked'"
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
                                @click="answerQuestion(idx)"
                            >
                                {{ option }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </transition>
        </div>

        <div
            v-if="selectedIsland"
            class="island-guide absolute bottom-0 right-0"
        >
            <transition name="guide-fade">
                <div v-if="guideMessage" ref="bubbleEl" class="island-guide-bubble" role="status">
                    <button
                        type="button"
                        class="island-guide-close"
                        aria-label="Zavřít"
                        @click="guideMessage = null"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <div class="island-guide-text" v-html="guideMessage"></div>
                </div>
            </transition>
            <img :src="selectedIsland.guideImage" alt="Průvodce" class="island-guide-img">
        </div>
    </div>
</template>

<script setup>
import { computed, reactive, ref, watch, onBeforeUnmount } from 'vue';

const asset = (filename) => new URL(`../../../../images/islands/${filename}`, import.meta.url).href;
const buttonVariants = [1, 2, 3, 4, 5].map((n) => ({
    default: new URL(`../../../../images/button${n}_default.svg`, import.meta.url).href,
    green: new URL(`../../../../images/button${n}_green.svg`, import.meta.url).href,
    orange: new URL(`../../../../images/button${n}_orange.svg`, import.meta.url).href,
}));

// button state: 'locked' | 'default' | 'green' (správně) | 'orange' (špatně)
const buttonSrc = (index, state) => {
    const variant = buttonVariants[index];
    if (state === 'green') return variant.green;
    if (state === 'orange') return variant.orange;
    return variant.default;
};

const defaultPathButtonPositions = [
    { top: '63%', left: '38%' },
    { top: '56%', left: '46%' },
    { top: '49%', left: '54%' },
    { top: '42%', left: '61%' },
    { top: '35%', left: '69%' },
];

// první tlačítko je odemčené, zbytek se odemyká postupně
const initialButtonStates = () => ['default', 'locked', 'locked', 'locked', 'locked'];

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

// mraky kolem majáku – základní pozice (překrývají maják) a směr odplutí
const clouds = [
    { top: '-20%', left: '5%',   width: '92%', dx: '-12%',  dy: '-150%', floatClass: 'cloud-float-a', delay: '0s'  },
    { top: '8%',   left: '-32%', width: '84%', dx: '-150%', dy: '-25%',  floatClass: 'cloud-float-b', delay: '-4s' },
    { top: '2%',   left: '50%',  width: '88%', dx: '150%',  dy: '-35%',  floatClass: 'cloud-float-c', delay: '-7s' },
    { top: '20%',  left: '8%',   width: '88%', dx: '5%',    dy: '-165%', floatClass: 'cloud-float-b', delay: '-2s' },
    { top: '42%',  left: '-24%', width: '72%', dx: '-150%', dy: '75%',   floatClass: 'cloud-float-a', delay: '-9s' },
    { top: '42%',  left: '52%',  width: '76%', dx: '150%',  dy: '85%',   floatClass: 'cloud-float-c', delay: '-5s' },
];

// úvodní promluva průvodce při otevření detailu ostrova
const exploitedEmotionsIntro =
    '<p>Vítejte na Ostrově zneužitých citů, kapitáne. Tohle místo vypadá na první pohled vlídně, ale nenechte se zmást. Podvodníci zde neútočí jen na vaše zařízení, ale především na vaše srdce, vaši lásku k rodině a vaši ochotu pomáhat.</p>'
    + '<p>Aby vás podvodníci dostali tam, kam chtějí, používají tyto nekalé postupy:</p>'
    + '<ul>'
    + '<li><strong>Zneužití strachu a emocí:</strong> Budou vám tvrdit, že váš vnuk měl nehodu nebo že je váš telefon v ohrožení virem. Chtějí vás vyděsit, abyste je v panice poslechli.</li>'
    + '<li><strong>Hra na city a osamělost:</strong> Budou se vydávat za sympatické lidi v nouzi nebo osamělé hrdiny, kteří potřebují právě vaši pomoc. Budují si u vás důvěru jen proto, aby ji později zpeněžili.</li>'
    + '<li><strong>Falešná autorita a nátlak:</strong> Někdy vystupují jako policisté nebo bankéři. Budou na vás spěchat a nutit vás k tajnostem před rodinou, abyste se nemohli s nikým poradit.</li>'
    + '</ul>'
    + '<p>Pamatujte si jedno zlaté pravidlo: Skutečná policie, banka nebo váš blízký po vás nikdy nebudou chtít, abyste své peníze narychlo někam posílali nebo si do telefonu instalovali neznámé programy. Jakmile na vás někdo v telefonu tlačí, zakazuje vám o tom mluvit s rodinou nebo vás straší virem, je to téměř jistě podvodník.</p>'
    + '<p>Na cestě k majáku Ostrova zneužitých citů vás čeká pět zkoušek. Vaším úkolem je nenechat se ovládnout emocemi. Pokud ucítíte tlak, zastavte se. Ověřte si vše u svých blízkých nebo přímo v bance.</p>'
    + '<p>Jste připraveni prokouknout jejich pasti a rozsvítit tento ostrov naplno? Pojďme na to.</p>';

const props = defineProps({
    // ostrovy z DB (modul islands) – očekává pole { id, name, image, guide }
    islandsData: {
        type: Array,
        default: () => [],
    },
    // token respondenta – použije se pro volání endpointu na načtení další situace
    respondentToken: {
        type: String,
        default: '',
    },
});

// rozmístění ostrovů ve scéně – přiřazuje se podle pořadí, není v DB
// beamAngle = natočení paprsku majáku k danému ostrovu
// (0° = dolů, kladné = po směru hodin / doleva, záporné = doprava)
const islandLayouts = [
    { position: { top: '11%', left: '6%' }, bobClass: 'island-1', beamAngle: 106 },
    { position: { top: '11%', right: '6%' }, bobClass: 'island-2', beamAngle: -106 },
    { position: { bottom: '9%', left: '6%' }, bobClass: 'island-3', beamAngle: 56 },
    { position: { right: '6%', bottom: '9%' }, bobClass: 'island-4', beamAngle: -56 },
];

// úvodní promluvy průvodce podle názvu obrázku ostrova – nejsou v DB
const introMessages = {
    'exploited_emotions.webp': exploitedEmotionsIntro,
};

const islands = reactive(
    props.islandsData.map((island, index) => {
        const layout = islandLayouts[index % islandLayouts.length];

        return {
            key: island.id,
            name: island.name,
            image: asset(island.image),
            detailImage: asset(`detail/${island.image}`),
            guideImage: island.guide ? asset(island.guide) : null,
            introMessage: introMessages[island.image] ?? null,
            position: layout.position,
            bobClass: layout.bobClass,
            beamAngle: layout.beamAngle,
            tasks: { completed: 0, total: 5 },
            pathButtonPositions: defaultPathButtonPositions,
            buttonStates: initialButtonStates(),
        };
    }),
);

// celkový postup hráče – z něj se odvozuje mlha i záře majáku
const totalCards = computed(() => islands.reduce((sum, island) => sum + island.tasks.total, 0));
const collectedCards = computed(() => islands.reduce((sum, island) => sum + island.tasks.completed, 0));
const progress = computed(() => (totalCards.value > 0 ? collectedCards.value / totalCards.value : 0));
// mraky halí maják na začátku, s postupem se rozestoupí a odplují
const cloudStyle = (cloud) => ({
    top: cloud.top,
    left: cloud.left,
    width: cloud.width,
    opacity: Math.max(0, 1 - progress.value),
    transform: `translate(calc(${cloud.dx} * ${progress.value}), calc(${cloud.dy} * ${progress.value}))`,
});

const selectedIsland = ref(null);
const activeQuestion = ref(null);
const guideMessage = ref(null);
const bubbleEl = ref(null);

// kliknutí kamkoliv mimo bublinu ji zavře
let outsideClickTimer = null;

const handleOutsideClick = (event) => {
    if (bubbleEl.value && !bubbleEl.value.contains(event.target)) {
        guideMessage.value = null;
    }
};

watch(guideMessage, (value) => {
    clearTimeout(outsideClickTimer);
    document.removeEventListener('click', handleOutsideClick);
    if (value) {
        // listener přidáme až po dokončení kliknutí, které bublinu otevřelo
        outsideClickTimer = setTimeout(() => {
            document.addEventListener('click', handleOutsideClick);
        }, 0);
    }
});

onBeforeUnmount(() => {
    clearTimeout(outsideClickTimer);
    document.removeEventListener('click', handleOutsideClick);
});

const open = (island) => {
    selectedIsland.value = island;
    guideMessage.value = island.introMessage ?? null;
};

const close = () => {
    selectedIsland.value = null;
    guideMessage.value = null;
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
    correct: 2,
});

const openQuestion = (buttonIndex) => {
    const state = selectedIsland.value?.buttonStates[buttonIndex];
    if (state === 'locked' || state === 'green') return;
    guideMessage.value = null;
    activeQuestion.value = { ...fakeQuestion(buttonIndex), buttonIndex };
};

const closeQuestion = () => {
    activeQuestion.value = null;
};

const answerQuestion = (optionIndex) => {
    const question = activeQuestion.value;
    const island = selectedIsland.value;
    if (!question || !island) return;

    const i = question.buttonIndex;

    if (optionIndex === question.correct) {
        // správná odpověď – tlačítko zazelená a odemkne se další v pořadí
        if (island.buttonStates[i] !== 'green') {
            island.tasks.completed = Math.min(island.tasks.total, island.tasks.completed + 1);
        }
        island.buttonStates[i] = 'green';
        if (i + 1 < island.buttonStates.length && island.buttonStates[i + 1] === 'locked') {
            island.buttonStates[i + 1] = 'default';
        }
        guideMessage.value = '<p>Skvělá práce! Takhle se podvodům úspěšně bráníš.</p>';
    } else {
        // špatná odpověď – tlačítko zoranžoví, jde zkusit znovu
        island.buttonStates[i] = 'orange';
        guideMessage.value = '<p>Tentokrát to nevyšlo. Zkus si situaci znovu promyslet.</p>';
    }

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
    aspect-ratio: 3 / 2;
    /* co největší scéna, která se vejde do výšky i šířky obrazovky */
    max-width: min(96rem, calc((100vh - 3rem) * 3 / 2));
    max-height: calc(100vh - 3rem);
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

.path-button--done {
    cursor: default;
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

/* --- maják: mlha, paprsky, záře, počítadlo karet bezpečí --- */
.lighthouse-wrap {
    z-index: 3;
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

.scene-fade-enter-active,
.scene-fade-leave-active {
    transition: opacity 0.25s ease;
}

.scene-fade-enter-from,
.scene-fade-leave-to {
    opacity: 0;
}

.island-guide {
    z-index: 50;
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
    max-width: min(94vw, 54rem);
    padding: 0.75rem;
}

.island-guide-img {
    height: 14rem;
    width: auto;
    flex-shrink: 0;
    filter: drop-shadow(0 6px 8px rgba(8, 38, 70, 0.45));
    user-select: none;
    pointer-events: none;
}

.island-guide-bubble {
    position: relative;
    margin-bottom: 6rem;
    padding: 1.1rem 1.4rem;
    border-radius: 1.5rem;
    background: rgba(255, 255, 255, 0.98);
    color: #11365e;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.5;
    box-shadow:
        0 6px 18px rgba(0, 20, 50, 0.4),
        inset 0 0 0 1px rgba(20, 74, 120, 0.15);
}

.island-guide-close {
    position: absolute;
    top: -0.65rem;
    right: -0.65rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.9rem;
    height: 1.9rem;
    border: 0;
    border-radius: 9999px;
    background: #11365e;
    color: #fff;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 20, 50, 0.45);
    transition: background 0.15s ease, transform 0.15s ease;
}

.island-guide-close:hover,
.island-guide-close:focus-visible {
    background: #1d4f86;
    transform: scale(1.08);
    outline: none;
}

.island-guide-close svg {
    width: 1rem;
    height: 1rem;
}

.island-guide-text :deep(p) {
    margin: 0 0 0.85rem;
}

.island-guide-text :deep(p:last-child) {
    margin-bottom: 0;
}

.island-guide-text :deep(strong) {
    font-weight: 800;
}

.island-guide-text :deep(ul) {
    list-style: none;
    margin: 0.2rem 0 0.95rem;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.island-guide-text :deep(li) {
    position: relative;
    padding-left: 1.15rem;
}

.island-guide-text :deep(li)::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0.62em;
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 9999px;
    background: #f59e0b;
}

.island-guide-bubble::after {
    content: '';
    position: absolute;
    right: -7px;
    bottom: 14px;
    width: 0;
    height: 0;
    border-top: 8px solid transparent;
    border-bottom: 8px solid transparent;
    border-left: 9px solid rgba(255, 255, 255, 0.98);
}

@media (max-width: 639px) {
    .island-guide-img {
        height: 9rem;
        width: auto;
    }
    .island-guide-bubble {
        font-size: 0.9rem;
        padding: 0.85rem 1rem;
        margin-bottom: 3.5rem;
    }
}

.guide-fade-enter-active,
.guide-fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.guide-fade-enter-from,
.guide-fade-leave-to {
    opacity: 0;
    transform: translateY(12px);
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
    .lighthouse-bob,
    .lighthouse-cloud-img,
    .lighthouse-glow { animation: none; }
}
</style>
