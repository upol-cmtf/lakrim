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
                            <!-- lampa na trávě nalevo od kamene – rozzáří se po správné odpovědi -->
                            <div
                                v-for="(pos, i) in selectedIsland.pathButtonPositions"
                                :key="`lamp-${i}`"
                                :style="pos"
                                class="path-lamp absolute"
                                :class="{ 'path-lamp--lit': selectedIsland.buttonStates[i] === 'green' }"
                                aria-hidden="true"
                            >
                                <img
                                    :src="selectedIsland.buttonStates[i] === 'green' ? lampOnImg : lampOffImg"
                                    alt=""
                                    class="path-lamp-img block w-full"
                                >
                            </div>
                            <button
                                v-for="(pos, i) in selectedIsland.pathButtonPositions"
                                :key="`btn-${i}`"
                                type="button"
                                :style="pos"
                                class="path-button absolute"
                                :class="{
                                    'path-button--locked': selectedIsland.buttonStates[i] === 'locked',
                                    'path-button--done': selectedIsland.buttonStates[i] === 'green' || selectedIsland.buttonStates[i] === 'red',
                                }"
                                :disabled="selectedIsland.buttonStates[i] === 'locked' || selectedIsland.buttonStates[i] === 'green' || selectedIsland.buttonStates[i] === 'red'"
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
                <div
                    class="question-modal relative flex h-full w-full max-w-[1600px] flex-col rounded-2xl bg-white p-6 sm:p-8 shadow-2xl"
                    :class="{ 'question-modal--dimmed': guideMessage }"
                >
                    <button
                        type="button"
                        class="absolute right-3 top-3 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-blue-900 transition hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                        aria-label="Zavřít"
                        @click="closeQuestion"
                    >
                        <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
                            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>

                    <!-- odpočet času – jen u situací s limitem (settings.time_limit) -->
                    <div
                        v-if="questionTimeLimit !== null"
                        class="question-timer mb-4 pr-10 sm:pr-12"
                        :class="{ 'question-timer--low': timeIsLow }"
                        role="timer"
                    >
                        <div class="question-timer-head">
                            <span class="question-timer-badge">
                                <svg viewBox="0 0 24 24" class="question-timer-icon" aria-hidden="true">
                                    <circle cx="12" cy="13" r="8" fill="none" stroke="currentColor" stroke-width="2"/>
                                    <path d="M12 9v4l2.5 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 2h6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <span>{{ formattedRemainingTime }}</span>
                            </span>
                            <span class="question-timer-label">{{ timeIsLow ? 'Pospěš si!' : 'Čas na odpověď' }}</span>
                        </div>
                        <div class="question-timer-track" aria-hidden="true">
                            <div class="question-timer-fill" :style="{ width: `${timerPercent}%` }"></div>
                        </div>
                    </div>

                    <div class="flex min-h-0 flex-1 flex-col gap-6 overflow-y-auto sm:flex-row sm:gap-8">
                        <!-- levá část: popis situace -->
                        <div class="sm:w-3/5">
                            <h3 class="mb-4 pr-8 text-xl font-bold leading-tight text-blue-900 sm:pr-0">
                                {{ activeQuestion.title }}
                            </h3>

                            <p
                                v-if="activeQuestion.perex"
                                class="mb-3 text-sm font-semibold leading-relaxed text-slate-800"
                            >
                                {{ activeQuestion.perex }}
                            </p>

                            <div
                                v-if="activeQuestion.description"
                                :class="[
                                    'question-modal-text text-sm leading-relaxed text-slate-700',
                                    { 'question-modal-text--cover': descriptionImageOnly },
                                ]"
                                v-html="activeQuestion.description"
                            ></div>
                        </div>

                        <!-- pravá část: možnosti na výběr -->
                        <div class="sm:w-2/5 sm:border-l sm:border-slate-200 sm:pl-8">
                            <h3 class="mb-3 text-base font-semibold text-slate-900">
                                Jak byste se zachovali?
                            </h3>
                            <p class="mb-4 text-sm text-slate-600">
                                Vyberte odpověď, která nejlépe odpovídá tomu, co byste v této situaci udělali.
                            </p>
                            <ul class="space-y-2">
                            <li
                                v-for="option in activeQuestion.options"
                                :key="option.id"
                            >
                                <button
                                    type="button"
                                    :class="optionClass(option)"
                                    :disabled="isOptionDisabled(option)"
                                    @click="answerQuestion(option)"
                                >
                                    <span>{{ option.name }}</span>
                                    <svg
                                        v-if="optionVariant(option) === 'correct'"
                                        class="h-5 w-5 flex-shrink-0 text-emerald-600"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="M5 12l5 5L20 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <svg
                                        v-else-if="optionVariant(option) === 'wrong'"
                                        class="h-5 w-5 flex-shrink-0 text-red-500"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="M7 7l10 10M17 7L7 17" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
        </div>

        <!-- karta bezpečí – po správné odpovědi vyskočí vpravo nahoře a po chvilce sama zmizí -->
        <transition name="safety-card-fade">
            <div v-if="safetyCard" class="safety-card" role="status" aria-label="Karta bezpečí">
                <div class="safety-card-corner safety-card-corner--tl" aria-hidden="true"></div>
                <div class="safety-card-corner safety-card-corner--tr" aria-hidden="true"></div>
                <div class="safety-card-corner safety-card-corner--bl" aria-hidden="true"></div>
                <div class="safety-card-corner safety-card-corner--br" aria-hidden="true"></div>

                <div class="safety-card-header">KARTA BEZPEČÍ</div>

                <div class="safety-card-illustration" aria-hidden="true">
                    <svg viewBox="0 0 120 130">
                        <defs>
                            <linearGradient id="shieldFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#fde68a"/>
                                <stop offset="100%" stop-color="#d4a437"/>
                            </linearGradient>
                            <linearGradient id="shieldStroke" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#8b6818"/>
                                <stop offset="100%" stop-color="#6b4f10"/>
                            </linearGradient>
                            <radialGradient id="shieldGlow" cx="50%" cy="40%" r="70%">
                                <stop offset="0%" stop-color="#fff" stop-opacity="0.6"/>
                                <stop offset="100%" stop-color="#fff" stop-opacity="0"/>
                            </radialGradient>
                        </defs>
                        <g transform="translate(60 65)">
                            <path
                                d="M0 -50 L45 -35 L45 5 C45 30 25 50 0 60 C-25 50 -45 30 -45 5 L-45 -35 Z"
                                fill="url(#shieldFill)"
                                stroke="url(#shieldStroke)"
                                stroke-width="3"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M0 -50 L45 -35 L45 5 C45 30 25 50 0 60 C-25 50 -45 30 -45 5 L-45 -35 Z"
                                fill="url(#shieldGlow)"
                            />
                            <path
                                d="M-18 5 L-5 18 L20 -10"
                                fill="none"
                                stroke="#fff"
                                stroke-width="6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </g>
                        <g fill="#d4a437" opacity="0.8">
                            <circle cx="15" cy="20" r="2"/>
                            <circle cx="105" cy="20" r="2"/>
                            <circle cx="15" cy="110" r="2"/>
                            <circle cx="105" cy="110" r="2"/>
                        </g>
                    </svg>
                </div>

                <div class="safety-card-body" v-html="safetyCard"></div>
            </div>
        </transition>

        <div
            v-if="selectedIsland"
            class="island-guide absolute bottom-0 right-0"
            :class="{ 'island-guide--above-modal': activeQuestion }"
        >
            <transition name="guide-fade">
                <div
                    v-if="guideMessage"
                    ref="bubbleEl"
                    class="island-guide-bubble"
                    role="status"
                >
                    <span
                        v-if="guideTone !== 'default'"
                        :class="['island-guide-shield', `island-guide-shield--${guideTone}`]"
                        aria-hidden="true"
                    >
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2l8 3v6c0 5-3.5 9.5-8 11-4.5-1.5-8-6-8-11V5l8-3z" fill="currentColor"/>
                            <path
                                v-if="guideTone === 'success'"
                                d="M8.5 12l2.5 2.5L16 9.5"
                                fill="none"
                                stroke="#fff"
                                stroke-width="2.2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                v-else-if="guideTone === 'failure'"
                                d="M9 9l6 6M15 9l-6 6"
                                fill="none"
                                stroke="#fff"
                                stroke-width="2.2"
                                stroke-linecap="round"
                            />
                            <g v-else>
                                <path d="M12 8v4" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/>
                                <circle cx="12" cy="15" r="1.1" fill="#fff"/>
                            </g>
                        </svg>
                    </span>
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
                    <button
                        v-if="guideAction"
                        type="button"
                        class="island-guide-action"
                        @click="runGuideAction"
                    >
                        {{ guideAction.label }}
                    </button>
                </div>
            </transition>
            <img :src="selectedIsland.guideImage" alt="Průvodce" class="island-guide-img">
        </div>
    </div>
</template>

<script setup>
import { computed, reactive, ref, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';

const asset = (filename) => new URL(`../../../../images/islands/${filename}`, import.meta.url).href;
const buttonVariants = [1, 2, 3, 4, 5].map((n) => ({
    default: new URL(`../../../../images/button${n}_default.svg`, import.meta.url).href,
    green: new URL(`../../../../images/button${n}_green.svg`, import.meta.url).href,
    orange: new URL(`../../../../images/button${n}_orange.svg`, import.meta.url).href,
    red: new URL(`../../../../images/button${n}_red.svg`, import.meta.url).href,
}));

// button state: 'locked' | 'default' | 'green' (správně) | 'orange' (1× špatně – lze zkusit znovu) | 'red' (2× špatně – další úkol odemčen)
const buttonSrc = (index, state) => {
    const variant = buttonVariants[index];
    if (state === 'green') return variant.green;
    if (state === 'orange') return variant.orange;
    if (state === 'red') return variant.red;
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
const lampOffImg = new URL('../../../../images/turned_off_lamp.webp', import.meta.url).href;
const lampOnImg = new URL('../../../../images/turned_on_lamp.webp', import.meta.url).href;

// mraky kolem majáku – základní pozice (překrývají maják) a směr odplutí
const clouds = [
    { top: '-20%', left: '5%',   width: '92%', dx: '-12%',  dy: '-150%', floatClass: 'cloud-float-a', delay: '0s'  },
    { top: '8%',   left: '-32%', width: '84%', dx: '-150%', dy: '-25%',  floatClass: 'cloud-float-b', delay: '-4s' },
    { top: '2%',   left: '50%',  width: '88%', dx: '150%',  dy: '-35%',  floatClass: 'cloud-float-c', delay: '-7s' },
    { top: '20%',  left: '8%',   width: '88%', dx: '5%',    dy: '-165%', floatClass: 'cloud-float-b', delay: '-2s' },
    { top: '42%',  left: '-24%', width: '72%', dx: '-150%', dy: '75%',   floatClass: 'cloud-float-a', delay: '-9s' },
    { top: '42%',  left: '52%',  width: '76%', dx: '150%',  dy: '85%',   floatClass: 'cloud-float-c', delay: '-5s' },
];

const props = defineProps({
    // ostrovy z DB (modul islands) – očekává pole { id, name, image, guide, intro }
    islandsData: {
        type: Array,
        default: () => [],
    },
    // token respondenta – použije se pro volání endpointu na načtení další situace
    respondentToken: {
        type: String,
        default: '',
    },
    // URL endpointu pro získání situace pro dané tlačítko
    situationUrl: {
        type: String,
        default: '',
    },
    // URL endpointu pro uložení odpovědi na situaci
    answerUrl: {
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

const islands = reactive(
    props.islandsData.map((island, index) => {
        const layout = islandLayouts[index % islandLayouts.length];

        return {
            key: island.id,
            name: island.name,
            image: asset(island.image),
            detailImage: asset(`detail/${island.image}`),
            guideImage: island.guide ? asset(island.guide) : null,
            introMessage: island.intro ?? null,
            position: layout.position,
            bobClass: layout.bobClass,
            beamAngle: layout.beamAngle,
            tasks: { completed: 0, total: 5 },
            pathButtonPositions: defaultPathButtonPositions,
            buttonStates: initialButtonStates(),
            // počet dosavadních neúspěšných pokusů a uložená rozehraná situace pro každé tlačítko
            attempts: [0, 0, 0, 0, 0],
            cachedQuestions: [null, null, null, null, null],
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
// stav rozehrané situace v modálu: 'live' = čeká na odpověď, 'retry' = po 1. špatném pokusu, 'finished' = vyhodnoceno (správně nebo 2. špatně)
const questionStatus = ref('live');
const loadingQuestion = ref(false);
const answerSubmitting = ref(false);
const guideMessage = ref(null);
// barevné ladění bubliny průvodce: 'default' | 'success' | 'retry' | 'failure'
const guideTone = ref('default');
// historie zvolených možností v právě otevřené situaci – zvýrazňuje se v modalu
const wrongOptionIds = ref([]);
const correctOptionId = ref(null);

const optionVariant = (option) => {
    if (correctOptionId.value === option.id) return 'correct';
    if (wrongOptionIds.value.includes(option.id)) return 'wrong';
    return 'default';
};

const optionClass = (option) => {
    const base = 'flex w-full items-center justify-between gap-3 rounded-lg border px-4 py-3 text-left text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-80';
    const variant = optionVariant(option);
    if (variant === 'correct') return `${base} border-emerald-400 bg-emerald-50 text-emerald-900`;
    if (variant === 'wrong') return `${base} border-red-300 bg-red-50 text-red-900`;
    return `${base} border-slate-200 bg-slate-50 text-slate-800 hover:border-blue-400 hover:bg-blue-50`;
};

const isOptionDisabled = (option) => (
    answerSubmitting.value
    || questionStatus.value === 'finished'
    || wrongOptionIds.value.includes(option.id)
);
// volitelné tlačítko v bublině průvodce (po odpovědi) – { label, handler }
const guideAction = ref(null);
// obsah karty bezpečí, která se po správné odpovědi objeví fade-inem nad modálem
const safetyCard = ref(null);

const runGuideAction = () => {
    const action = guideAction.value;
    guideAction.value = null;
    action?.handler();
};

// popis otázky tvořený jen obrázkem (žádný text) – obrázek pak vyplní modal
const descriptionImageOnly = computed(() => {
    const description = activeQuestion.value?.description ?? '';
    return /^\s*<img\b[^>]*>\s*$/i.test(description);
});
const bubbleEl = ref(null);

// --- časový limit na odpověď (volitelný, settings.time_limit = počet sekund) ---
const remainingSeconds = ref(0);
let questionTimer = null;

// počet sekund z nastavení otázky; null = situace bez limitu
const parseTimeLimit = (settings) => {
    const seconds = Number(settings?.time_limit);
    return Number.isFinite(seconds) && seconds > 0 ? Math.floor(seconds) : null;
};

const questionTimeLimit = computed(() => activeQuestion.value?.timeLimit ?? null);
const timeIsLow = computed(() => questionTimeLimit.value !== null && remainingSeconds.value <= 10);

const formattedRemainingTime = computed(() => {
    const total = Math.max(0, remainingSeconds.value);
    return `${Math.floor(total / 60)}:${String(total % 60).padStart(2, '0')}`;
});

const timerPercent = computed(() => (
    questionTimeLimit.value ? Math.max(0, remainingSeconds.value) / questionTimeLimit.value * 100 : 0
));

const stopQuestionTimer = () => {
    if (questionTimer !== null) {
        clearInterval(questionTimer);
        questionTimer = null;
    }
};

// vypršení času se počítá jako špatný pokus – po prvním zoranžoví, po druhém zčervená a odemkne další úkol
const handleTimeUp = () => {
    stopQuestionTimer();

    const question = activeQuestion.value;
    const island = selectedIsland.value;
    if (!question || !island) return;

    const i = question.buttonIndex;
    const attemptNumber = island.attempts[i] + 1;
    island.attempts[i] = attemptNumber;

    if (attemptNumber >= 2) {
        island.buttonStates[i] = 'red';
        island.cachedQuestions[i] = null;
        if (i + 1 < island.buttonStates.length && island.buttonStates[i + 1] === 'locked') {
            island.buttonStates[i + 1] = 'default';
        }
        questionStatus.value = 'finished';
        guideTone.value = 'failure';
        guideMessage.value = '<p>Čas vypršel a druhý pokus už není možný. Pojďme dál.</p>';
        guideAction.value = buildContinueAction(island, i);
    } else {
        island.buttonStates[i] = 'orange';
        island.cachedQuestions[i] = { ...question };
        // modal zůstává otevřený – hráč si po dovysvětlení může zkusit odpovědět znovu
        questionStatus.value = 'retry';
        guideTone.value = 'retry';
        guideMessage.value = '<p>Čas vypršel – odpověď ses nestihl/a poslat. Zkus situaci znovu.</p>';
        guideAction.value = retryAction;
    }
};

const startQuestionTimer = () => {
    stopQuestionTimer();

    if (questionTimeLimit.value === null) return;

    remainingSeconds.value = questionTimeLimit.value;
    questionTimer = setInterval(() => {
        remainingSeconds.value -= 1;

        if (remainingSeconds.value <= 0) {
            handleTimeUp();
        }
    }, 1000);
};

// kliknutí kamkoliv mimo bublinu ji zavře
let outsideClickTimer = null;
// prodleva, než se po otevření detailu ostrova ukáže úvodní zpráva průvodce
let introTimer = null;
// karta bezpečí sama zmizí po nastavené době
let safetyCardTimer = null;
const safetyCardDismissDelay = 6000;

const showSafetyCard = (content) => {
    clearTimeout(safetyCardTimer);
    safetyCard.value = content;
    safetyCardTimer = setTimeout(() => {
        safetyCard.value = null;
    }, safetyCardDismissDelay);
};

const hideSafetyCard = () => {
    clearTimeout(safetyCardTimer);
    safetyCard.value = null;
};

// klíč v sessionStorage – intro se ukáže jednou za session, příští spuštění aplikace ho zobrazí znovu
const seenIntrosStorageKey = 'islandGame.seenIntros';

const readSeenIntros = () => {
    try {
        const raw = window.sessionStorage.getItem(seenIntrosStorageKey);
        const parsed = raw ? JSON.parse(raw) : [];
        return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
        return [];
    }
};

const hasSeenIntro = (islandId) => readSeenIntros().includes(islandId);

const markIntroSeen = (islandId) => {
    try {
        const seen = readSeenIntros();
        if (!seen.includes(islandId)) {
            seen.push(islandId);
            window.sessionStorage.setItem(seenIntrosStorageKey, JSON.stringify(seen));
        }
    } catch (error) {
        // sessionStorage nemusí být dostupné (privátní režim) – v tom případě se intro ukáže příště znovu
    }
};

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
    } else {
        // s bublinou mizí i případné akční tlačítko a barevné ladění
        guideAction.value = null;
        guideTone.value = 'default';
    }
});

onBeforeUnmount(() => {
    clearTimeout(outsideClickTimer);
    clearTimeout(introTimer);
    clearTimeout(safetyCardTimer);
    stopQuestionTimer();
    document.removeEventListener('click', handleOutsideClick);
});

const open = (island) => {
    clearTimeout(introTimer);
    selectedIsland.value = island;
    guideMessage.value = null;

    // detail se otevře hned, úvodní zpráva průvodce naběhne až po krátké prodlevě
    // intro se ukáže jen při prvním otevření – další otevření už ho přeskočí
    if (island.introMessage && !hasSeenIntro(island.key)) {
        introTimer = setTimeout(() => {
            // pojistka: hráč mezitím nemusel detail zavřít / přepnout
            if (selectedIsland.value === island) {
                guideMessage.value = island.introMessage;
                markIntroSeen(island.key);
            }
        }, 700);
    }
};

const close = () => {
    clearTimeout(introTimer);
    selectedIsland.value = null;
    guideMessage.value = null;
    hideSafetyCard();
};

// po vyhodnocení situace (správně / 2× špatně) nabídneme pokračování nebo dokončení ostrova
const buildContinueAction = (island, i) => {
    const isLastStone = i === island.buttonStates.length - 1;
    return isLastStone
        ? { label: 'Zpět na mapu ostrovů', handler: () => { closeQuestion(); close(); } }
        : { label: 'Pokračovat na další kámen', handler: closeQuestion };
};

const retryAction = {
    label: 'Zkusit odpovědět znovu',
    handler: () => {
        guideMessage.value = null;
    },
};

// načte situaci pro dané tlačítko z DB (endpoint vybere otázku adaptivní obtížnosti)
const openQuestion = async (buttonIndex) => {
    const island = selectedIsland.value;
    const state = island?.buttonStates[buttonIndex];
    if (!island || state === 'locked' || state === 'green' || state === 'red') return;
    if (loadingQuestion.value) return;

    guideMessage.value = null;
    questionStatus.value = 'live';
    wrongOptionIds.value = [];
    correctOptionId.value = null;

    // po prvním špatném pokusu nabízíme stejnou situaci znovu – bez dalšího volání endpointu
    const cached = island.cachedQuestions[buttonIndex];
    if (cached) {
        activeQuestion.value = { ...cached, openedAt: Date.now() };
        startQuestionTimer();
        return;
    }

    loadingQuestion.value = true;

    try {
        const { data } = await axios.post(
            props.situationUrl,
            {
                respondent_token: props.respondentToken,
                island_id: island.key,
                button: buttonIndex + 1,
            },
            { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } },
        );

        const situation = data.data;
        activeQuestion.value = {
            buttonIndex,
            situationId: situation.id,
            questionId: situation.question.id,
            title: situation.title || situation.question.perex,
            // perex ukážeme jen tehdy, když má situace vlastní titulek (jinak by se opakoval)
            perex: situation.title ? situation.question.perex : null,
            description: situation.question.description,
            options: situation.question.options,
            timeLimit: parseTimeLimit(situation.question.settings),
            openedAt: Date.now(),
        };
        startQuestionTimer();
    } catch (error) {
        guideMessage.value = error.response?.status === 404
            ? '<p>Pro tento úkol už nejsou žádné další situace.</p>'
            : '<p>Situaci se nepodařilo načíst. Zkuste to prosím za chvíli znovu.</p>';
    } finally {
        loadingQuestion.value = false;
    }
};

const closeQuestion = () => {
    stopQuestionTimer();
    activeQuestion.value = null;
    questionStatus.value = 'live';
    wrongOptionIds.value = [];
    correctOptionId.value = null;
    hideSafetyCard();
};

// zpětná vazba po špatné odpovědi – hodnocení zvolené možnosti, případně otázky
const wrongAnswerHint = (result, optionId) => {
    const chosen = (result.evaluations ?? []).find((evaluation) => evaluation.optionId === optionId);
    return chosen?.evaluation || result.correctAnswerEvaluation || null;
};

// odešle zvolenou odpověď na endpoint, ten vyhodnotí správnost a případnou kartu bezpečí
const answerQuestion = async (option) => {
    const question = activeQuestion.value;
    const island = selectedIsland.value;
    if (!question || !island || answerSubmitting.value) return;

    // hráč odpověděl včas – odpočet zastavíme
    stopQuestionTimer();
    answerSubmitting.value = true;
    const i = question.buttonIndex;
    const attemptNumber = island.attempts[i] + 1;

    try {
        const { data } = await axios.post(
            props.answerUrl,
            {
                respondent_token: props.respondentToken,
                question_id: question.questionId,
                option_ids: [option.id],
                seconds: Math.max(0, Math.round((Date.now() - question.openedAt) / 1000)),
                attempt: attemptNumber,
                island_id: island.key,
                button: i + 1,
            },
            { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } },
        );

        const result = data.data;

        if (result.correct) {
            // správně – tlačítko zazelená, odemkne se další a hráč získá kartu bezpečí
            correctOptionId.value = option.id;
            if (island.buttonStates[i] !== 'green') {
                island.tasks.completed = Math.min(island.tasks.total, island.tasks.completed + 1);
            }
            island.buttonStates[i] = 'green';
            island.cachedQuestions[i] = null;
            if (i + 1 < island.buttonStates.length && island.buttonStates[i + 1] === 'locked') {
                island.buttonStates[i + 1] = 'default';
            }
            questionStatus.value = 'finished';
            guideTone.value = 'success';
            if (result.safetyCard) {
                // karta vpravo nahoře jen oslavně oznámí zisk; popis vysvětlí průvodce
                showSafetyCard('<p><strong>Získal jsi kartu bezpečí!</strong></p>');
                guideMessage.value = result.safetyCard;
            } else {
                guideMessage.value = '<p>Skvělá práce! Situaci jsi zvládl správně.</p>';
            }
            guideAction.value = buildContinueAction(island, i);
        } else {
            island.attempts[i] = attemptNumber;
            if (!wrongOptionIds.value.includes(option.id)) {
                wrongOptionIds.value = [...wrongOptionIds.value, option.id];
            }
            const hint = wrongAnswerHint(result, option.id);

            if (attemptNumber >= 2) {
                // druhá špatná odpověď – tlačítko zčervená, další úkol se odemkne
                island.buttonStates[i] = 'red';
                island.cachedQuestions[i] = null;
                if (i + 1 < island.buttonStates.length && island.buttonStates[i + 1] === 'locked') {
                    island.buttonStates[i + 1] = 'default';
                }
                questionStatus.value = 'finished';
                guideTone.value = 'failure';
                guideMessage.value = hint
                    ? `<p>${hint}</p><p>Druhý pokus už nevyšel – pojďme dál.</p>`
                    : '<p>Bohužel ani druhý pokus nevyšel. Pojďme dál.</p>';
                guideAction.value = buildContinueAction(island, i);
            } else {
                // první špatná odpověď – tlačítko zoranžoví a stejná situace se nabídne k druhému pokusu
                island.buttonStates[i] = 'orange';
                island.cachedQuestions[i] = { ...question };
                questionStatus.value = 'retry';
                guideTone.value = 'retry';
                guideMessage.value = hint
                    ? `<p>${hint}</p><p>Zkus tuhle situaci ještě jednou.</p>`
                    : '<p>Tentokrát to nevyšlo. Zkus tuhle situaci ještě jednou.</p>';
                guideAction.value = retryAction;
            }
        }
    } catch (error) {
        guideMessage.value = '<p>Odpověď se nepodařilo odeslat. Zkuste to prosím za chvíli znovu.</p>';
    } finally {
        answerSubmitting.value = false;
    }
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
    /* nižší než modal (z-50), aby průvodce zůstal za detailem situace */
    z-index: 40;
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
    max-width: min(94vw, 54rem);
    padding: 0.75rem;
}

/* nad otevřeným modalem – průvodce stojí v rohu obrazovky, bublina je vždy vidět */
.island-guide--above-modal {
    position: fixed;
    z-index: 60;
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

.island-guide-shield {
    position: absolute;
    top: -1.1rem;
    left: -1.1rem;
    width: 2.6rem;
    height: 2.6rem;
    border-radius: 9999px;
    background: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(8, 38, 70, 0.35);
}

.island-guide-shield svg {
    width: 1.7rem;
    height: 1.7rem;
}

.island-guide-shield--success {
    color: #16a34a;
}

.island-guide-shield--retry {
    color: #f59e0b;
}

.island-guide-shield--failure {
    color: #dc2626;
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

.island-guide-action {
    display: inline-flex;
    align-items: center;
    margin-top: 1.4rem;
    padding: 0.55rem 1.25rem;
    border: 0;
    border-radius: 0.55rem;
    background: #11365e;
    color: #fff;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s ease, transform 0.15s ease;
}

.island-guide-action:hover,
.island-guide-action:focus-visible {
    background: #1d4f86;
    transform: translateY(-1px);
    outline: none;
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

.question-modal-text :deep(img) {
    display: block;
    max-width: 100%;
    max-height: 30rem;
    width: auto;
    height: auto;
    margin: 0 auto 0.75rem;
    border-radius: 0.5rem;
}

/* popis je jen obrázek – nech ho vyplnit výšku modalu */
.question-modal-text--cover :deep(img) {
    max-height: 82vh;
    margin-bottom: 0;
}

.question-modal-text :deep(p) {
    margin: 0 0 0.6rem;
}

.question-modal-text :deep(p:last-child) {
    margin-bottom: 0;
}

/* --- karta bezpečí – vyskočí vpravo nahoře jako sběratelská karta --- */
.safety-card {
    position: fixed;
    top: 1.25rem;
    right: 1.25rem;
    z-index: 70;
    width: min(17rem, calc(100vw - 2.5rem));
    max-height: calc(100vh - 2.5rem);
    overflow-y: auto;
    padding: 1.05rem 1.05rem 1.2rem;
    text-align: center;
    background:
        radial-gradient(ellipse at top, rgba(255, 250, 235, 0.95) 0%, #fff7e6 60%, #f6e4b8 100%);
    border-radius: 0.85rem;
    border: 2px solid #b88a2c;
    box-shadow:
        0 20px 40px rgba(8, 38, 70, 0.4),
        inset 0 0 0 3px #fff,
        inset 0 0 0 4px #d4a437;
}

/* dekorativní rohy karty */
.safety-card-corner {
    position: absolute;
    width: 0.85rem;
    height: 0.85rem;
    border: 2px solid #8b6818;
    pointer-events: none;
}

.safety-card-corner--tl { top: 0.45rem; left: 0.45rem; border-right: 0; border-bottom: 0; border-top-left-radius: 0.3rem; }
.safety-card-corner--tr { top: 0.45rem; right: 0.45rem; border-left: 0; border-bottom: 0; border-top-right-radius: 0.3rem; }
.safety-card-corner--bl { bottom: 0.45rem; left: 0.45rem; border-right: 0; border-top: 0; border-bottom-left-radius: 0.3rem; }
.safety-card-corner--br { bottom: 0.45rem; right: 0.45rem; border-left: 0; border-top: 0; border-bottom-right-radius: 0.3rem; }

.safety-card-header {
    display: block;
    margin: 0 auto 0.85rem;
    padding: 0.35rem 0;
    font-family: 'Georgia', 'Times New Roman', serif;
    font-size: 0.85rem;
    font-weight: 800;
    color: #6b4f10;
    letter-spacing: 0.18em;
    border-top: 1px solid rgba(139, 104, 24, 0.4);
    border-bottom: 1px solid rgba(139, 104, 24, 0.4);
}

.safety-card-illustration {
    margin: 0.25rem auto 0.85rem;
    width: 6.25rem;
    height: 6.75rem;
    filter: drop-shadow(0 4px 6px rgba(139, 104, 24, 0.35));
}

.safety-card-illustration svg {
    width: 100%;
    height: 100%;
}

.safety-card-body {
    color: #11365e;
    font-size: 0.92rem;
    line-height: 1.5;
    text-align: left;
    padding: 0.5rem 0.25rem 0;
    border-top: 1px dashed rgba(139, 104, 24, 0.4);
}

.safety-card-body :deep(p) {
    margin: 0 0 0.6rem;
}

.safety-card-body :deep(p:last-child) {
    margin-bottom: 0;
}

.safety-card-body :deep(strong) {
    font-weight: 800;
}

.safety-card-fade-enter-active {
    transition: opacity 1.1s ease, transform 1.1s cubic-bezier(0.22, 1, 0.36, 1);
}

.safety-card-fade-leave-active {
    transition: opacity 0.7s ease, transform 0.7s ease;
}

.safety-card-fade-enter-from {
    opacity: 0;
    transform: translateY(-14px) scale(0.94);
}

.safety-card-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.97);
}

/* když průvodce zobrazí bublinu nad modal, ztlumíme obsah modalu, aby s ní nesoupeřil */
.question-modal--dimmed {
    transition: filter 0.2s ease, opacity 0.2s ease;
    filter: blur(2px) grayscale(0.7) brightness(0.92);
    opacity: 0.9;
    pointer-events: none;
}

/* --- odpočet času na odpověď --- */
.question-timer-head {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.45rem;
}

.question-timer-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.28rem 0.7rem;
    border-radius: 9999px;
    background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%);
    color: #3a2a05;
    font-size: 1rem;
    font-weight: 700;
    line-height: 1;
    font-variant-numeric: tabular-nums;
    box-shadow:
        inset 0 0 0 1px rgba(255, 255, 255, 0.55),
        0 1px 2px rgba(0, 0, 0, 0.2);
}

.question-timer-icon {
    width: 1.05rem;
    height: 1.05rem;
}

.question-timer-label {
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #64748b;
}

.question-timer-track {
    height: 0.4rem;
    border-radius: 9999px;
    background: #e2e8f0;
    overflow: hidden;
}

.question-timer-fill {
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, #fbbf24 0%, #f59e0b 100%);
    transition: width 1s linear, background 0.3s ease;
}

/* posledních pár sekund – varovná červená a pulzování */
.question-timer--low .question-timer-badge {
    background: linear-gradient(180deg, #f87171 0%, #dc2626 100%);
    color: #fff;
    animation: timer-pulse 1s ease-in-out infinite;
}

.question-timer--low .question-timer-label {
    color: #dc2626;
}

.question-timer--low .question-timer-fill {
    background: linear-gradient(90deg, #f87171 0%, #dc2626 100%);
}

@keyframes timer-pulse {
    0%, 100% { transform: scale(1);    }
    50%      { transform: scale(1.07); }
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
    .lighthouse-glow,
    .question-timer--low .question-timer-badge { animation: none; }
}
</style>
