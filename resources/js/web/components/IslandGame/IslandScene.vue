<template>
    <div class="island-page relative overflow-hidden">
        <SeaWavelets :wavelets="seaWavelets" :path="seaWaveletPath" />

        <!-- přehled moře: ikonka domů + „Jak hrát?" vlevo nahoře -->
        <div v-if="!selectedIsland" class="absolute top-3 left-3 z-20 flex items-center gap-2">
            <a
                :href="homeUrl"
                class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white/95 text-blue-900 shadow-md transition hover:bg-white sm:h-8 sm:w-8"
                :aria-label="$t('islandGame.common.home')"
            >
                <svg viewBox="0 0 24 24" class="h-4 w-4 sm:h-[1.1rem] sm:w-[1.1rem]" aria-hidden="true">
                    <path d="M3 11.5 12 4l9 7.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 10v9a1 1 0 0 0 1 1h4v-5h4v5h4a1 1 0 0 0 1-1v-9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <button
                type="button"
                class="inline-flex items-center rounded-lg bg-white/95 px-2.5 py-[0.3rem] text-[0.8rem] font-semibold leading-tight text-blue-900 shadow-md transition hover:bg-white sm:text-sm"
                @click="tourActive = true"
            >
                {{ $t('islandGame.common.howToPlay') }}
            </button>
        </div>

        <button
            v-if="selectedIsland"
            type="button"
            class="back-to-map-btn absolute top-3 left-3 z-20 inline-flex items-center gap-2 rounded-lg bg-white/95 px-2.5 py-[0.3rem] text-[0.95rem] font-semibold leading-tight text-blue-900 shadow-md transition hover:bg-white sm:px-4 sm:py-2 sm:text-base"
            @click="close"
            :aria-label="$t('islandGame.common.backToMap')"
        >
            <span aria-hidden="true">←</span>
            <span class="back-to-map-label hidden sm:inline">{{ $t('islandGame.common.backToMap') }}</span>
        </button>

        <h2 v-if="selectedIsland" class="island-detail-label absolute top-3 left-1/2 z-20 -translate-x-1/2">
            {{ selectedIsland.name }}
            <span class="island-progress island-progress--lg">{{ selectedIsland.tasks.completed }}/{{ selectedIsland.tasks.total }}</span>
        </h2>

        <SeaFishLayer
            v-if="!selectedIsland"
            :fish="fish"
            :fish-style="fishStyle"
            :fish-inner-style="fishInnerStyle"
            :paused="tourActive"
            @catch="loadEasterEgg"
        />

        <FishHint :visible="!selectedIsland && fishHintVisible && !tourActive" @close="hideFishHint" />

        <!-- na mapě necháme prázdnou vodu propustnou pro klik (klik projde na rybku pod kontejnerem) -->
        <div
            class="island-stage container relative mx-auto flex items-center justify-center px-4 py-6"
            :class="{ 'pointer-events-none': !selectedIsland }"
        >
            <div class="island-scene relative mx-auto w-full overflow-hidden">
                <div class="sea-caustics" aria-hidden="true"></div>

                <!-- sdílené SVG definice (ref přes <use href="#..."> v subkomponentách) -->
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
                    <IslandMap
                        v-if="!selectedIsland"
                        key="scene"
                        :islands="islands"
                        :clouds="clouds"
                        :cloud-style="cloudStyle"
                        :progress="progress"
                        :collected-cards="collectedCards"
                        :total-cards="totalCards"
                        :lighthouse-img="lighthouseImg"
                        :lighthouse-hovered="lighthouseHovered"
                        @open-island="open"
                        @open-lighthouse="openLighthouse"
                        @lighthouse-hover="lighthouseHovered = $event"
                    />
                    <IslandDetail
                        v-else
                        key="detail"
                        :island="selectedIsland"
                        :lamp-on-img="lampOnImg"
                        :lamp-off-img="lampOffImg"
                        :button-src="buttonSrc"
                        :should-pulse="shouldPulse"
                        @open-question="openQuestion"
                    >
                        <template #detail="{ island }">
                            <slot name="detail" :island="island"></slot>
                        </template>
                    </IslandDetail>
                </transition>

                <QuestionModal
                    :question="activeQuestion"
                    :review-mode="reviewMode"
                    :dimmed="!!guideMessage"
                    :question-time-limit="questionTimeLimit"
                    :time-is-low="timeIsLow"
                    :formatted-remaining-time="formattedRemainingTime"
                    :timer-percent="timerPercent"
                    :description-image-only="descriptionImageOnly"
                    :option-class="optionClass"
                    :option-variant="optionVariant"
                    :is-option-disabled="isOptionDisabled"
                    @close="closeQuestion"
                    @answer="answerQuestion"
                    @zoom="openImageZoom"
                />
            </div>
        </div>

        <SafetyCardToast :content="safetyCard" :frame-src="safetyCardImg" />

        <ImageZoom :src="zoomImage" :alt="zoomAlt" @close="closeImageZoom" />

        <LighthouseModal
            :open="lighthouseModalOpen"
            :interior-img="lighthouseInteriorImg"
            :life-ring-img="lifeRingImg"
            :corkboard-img="corkboardImg"
            :fishing-basket-img="fishingBasketImg"
            :safety-card-img="safetyCardImg"
            :board-safety-cards="boardSafetyCards"
            :board-card-style="boardCardStyle"
            :collected-safety-cards-count="collectedSafetyCards.length"
            :important-contacts-count="importantContacts.length"
            :caught-fish="caughtFish"
            @close="closeLighthouse"
            @open-contacts="openContacts"
            @open-cards="openCards"
            @open-basket="openBasket"
        />

        <ContactsModal :open="contactsOpen" :contacts="importantContacts" @close="closeContacts" />

        <SafetyCardsModal
            :open="cardsOpen"
            :cards="collectedSafetyCards"
            :total-cards="totalCards"
            :safety-card-img="safetyCardImg"
            @close="closeCards"
        />

        <BasketModal
            :open="basketOpen"
            :caught-fish="caughtFish"
            :easter-eggs-count="easterEggsCount"
            :egg-title="eggTitle"
            @close="closeBasket"
            @review="reviewEasterEgg"
        />

        <EasterEggModal
            :open="easterEggModalOpen"
            :loading="easterEggLoading"
            :empty="easterEggEmpty"
            :content="easterEggContent"
            :egg="easterEgg"
            v-model:view="easterEggView"
            @close="closeEasterEgg"
        />

        <GameCompleteModal
            :open="gameCompleteOpen"
            :collected-cards="collectedCards"
            :total-cards="totalCards"
            :caught-fish-count="caughtFish.length"
            :easter-eggs-count="easterEggsCount"
            :home-url="homeUrl"
            :video-url="completionVideoUrl"
            @close="closeGameComplete"
        />

        <IntroTour :active="tourActive" @finish="finishTour" />

        <GuideBubble
            v-if="selectedIsland"
            :guide-image="selectedIsland.guideImage"
            :message="guideMessage"
            :tone="guideTone"
            :action="guideAction"
            :above-modal="!!activeQuestion"
            @close="guideMessage = null"
            @run-action="runGuideAction"
        />
    </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';

import {
    seaWavelets,
    seaWaveletPath,
    waveOuterPath,
    waveInnerPath,
    lighthouseImg,
    lampOnImg,
    lampOffImg,
    safetyCardImg,
    lighthouseInteriorImg,
    lifeRingImg,
    fishingBasketImg,
    corkboardImg,
    clouds,
    buttonSrc,
} from './composables/assets';
import { useGuide } from './composables/useGuide';
import { useSafetyCard } from './composables/useSafetyCard';
import { useIslands } from './composables/useIslands';
import { useSeaFish } from './composables/useSeaFish';
import { useLighthouseModals } from './composables/useLighthouseModals';
import { useEasterEggs } from './composables/useEasterEggs';
import { useQuestionFlow } from './composables/useQuestionFlow';

import SeaWavelets from './components/SeaWavelets.vue';
import SeaFishLayer from './components/SeaFishLayer.vue';
import FishHint from './components/FishHint.vue';
import IslandMap from './components/IslandMap.vue';
import IslandDetail from './components/IslandDetail.vue';
import QuestionModal from './components/QuestionModal.vue';
import SafetyCardToast from './components/SafetyCardToast.vue';
import ImageZoom from './components/ImageZoom.vue';
import LighthouseModal from './components/LighthouseModal.vue';
import ContactsModal from './components/ContactsModal.vue';
import SafetyCardsModal from './components/SafetyCardsModal.vue';
import BasketModal from './components/BasketModal.vue';
import EasterEggModal from './components/EasterEggModal.vue';
import GameCompleteModal from './components/GameCompleteModal.vue';
import IntroTour from './components/IntroTour.vue';
import GuideBubble from './components/GuideBubble.vue';

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
    // počet easter eggů – kolik rybek se ve scéně vygeneruje
    easterEggsCount: {
        type: Number,
        default: 0,
    },
    // URL endpointu pro načtení znění easter egg úkolu
    easterEggUrl: {
        type: String,
        default: '',
    },
    // URL endpointu pro uložení splněného easter eggu (ulovené rybky)
    respondentEasterEggUrl: {
        type: String,
        default: '',
    },
    // URL úvodní stránky – cíl ikonky domů vlevo nahoře na přehledu moře
    homeUrl: {
        type: String,
        default: '/',
    },
    // URL videa, které se přehraje na konci hry (překvapení po dohrání)
    completionVideoUrl: {
        type: String,
        default: '',
    },
});

// bublina průvodce a karta bezpečí – sdílené napříč intro/odpovědí
const { guideMessage, guideTone, guideAction, runGuideAction } = useGuide();
const { safetyCard, showSafetyCard, hideSafetyCard } = useSafetyCard();

// ostrovy + výběr ostrova + postup hráče
const {
    islands,
    selectedIsland,
    totalCards,
    collectedCards,
    progress,
    allStonesResolved,
    collectedSafetyCards,
    lighthouseHovered,
    cloudStyle,
    shouldPulse,
    open,
    close,
} = useIslands(props, { guideMessage, guideAction, hideSafetyCard });

// rybky v moři + koš ulovených ryb + pobídka
const {
    fish,
    caughtFish,
    catchFish,
    fishStyle,
    fishInnerStyle,
    fishHintVisible,
    hideFishHint,
    scheduleFishHint,
} = useSeaFish(props, selectedIsland);

// modal majáku a jeho hotspoty
const {
    lighthouseModalOpen,
    openLighthouse,
    closeLighthouse,
    contactsOpen,
    importantContacts,
    openContacts,
    closeContacts,
    cardsOpen,
    boardSafetyCards,
    boardCardStyle,
    openCards,
    closeCards,
    basketOpen,
    openBasket,
    closeBasket,
} = useLighthouseModals({ collectedSafetyCards, lighthouseHovered });

// bonusové úkoly (easter eggy) na rybkách
const {
    easterEggModalOpen,
    easterEgg,
    easterEggView,
    easterEggLoading,
    easterEggEmpty,
    easterEggContent,
    loadEasterEgg,
    closeEasterEgg,
    reviewEasterEgg,
    eggTitle,
} = useEasterEggs(props, { catchFish, scheduleFishHint, basketOpen });

// rozehraná situace v modálu (otázka, odpočet, vyhodnocení, lightbox)
const {
    activeQuestion,
    reviewMode,
    optionVariant,
    optionClass,
    isOptionDisabled,
    descriptionImageOnly,
    questionTimeLimit,
    timeIsLow,
    formattedRemainingTime,
    timerPercent,
    openQuestion,
    closeQuestion,
    answerQuestion,
    zoomImage,
    zoomAlt,
    openImageZoom,
    closeImageZoom,
} = useQuestionFlow(props, {
    selectedIsland,
    guideMessage,
    guideTone,
    guideAction,
    showSafetyCard,
    hideSafetyCard,
    collectedSafetyCards,
    close,
});

// po projití všech kamenů na všech ostrovech ukážeme oslavný panel (jen při dohrání,
// ne při každém načtení – proto reagujeme jen na přechod z nedohráno na dohráno)
const gameCompleteOpen = ref(false);
watch(allStonesResolved, (done, wasDone) => {
    if (done && !wasDone) {
        gameCompleteOpen.value = true;
    }
});
const closeGameComplete = () => {
    gameCompleteOpen.value = false;
    // poslední kámen mohl nechat otevřené okno otázky – zavřeme ho a vrátíme se na mapu
    closeQuestion();
    close();
};

// úvodní prohlídka mapy – ukáže se jednou (uloženo u respondenta), znovu přes „Jak hrát?"
const introStorageKey = `lakrim.introSeen.${props.respondentToken}`;
const introSeen = () => {
    try {
        return window.localStorage.getItem(introStorageKey) === '1';
    } catch (error) {
        return false;
    }
};
const tourActive = ref(!introSeen());
const finishTour = () => {
    tourActive.value = false;
    try {
        window.localStorage.setItem(introStorageKey, '1');
    } catch (error) {
        // úložiště nemusí být dostupné – tiše ignorujeme
    }
};

// při otevřeném modálu (maják / easter egg / situace) zamkneme rolování stránky pod ním
watch([lighthouseModalOpen, easterEggModalOpen, activeQuestion], ([lighthouse, easterEgg, question]) => {
    document.body.style.overflow = (lighthouse || easterEgg || question) ? 'hidden' : '';
});

onBeforeUnmount(() => {
    // pojistka, ať po odpojení komponenty nezůstane stránka zamčená
    document.body.style.overflow = '';
});
</script>

<style scoped lang="scss">
@use './styles/shared' as *;

.island-page {
    min-height: 100vh;
    min-height: 100dvh;
    background: radial-gradient(ellipse at 50% 30%, #4ea6d8 0%, #2c79b0 55%, #144a78 100%);
}

/* scéna se centruje podle reálně viditelné výšky (dvh) – jinak na mobilu naležato
   100vh ignoruje lištu prohlížeče a spodní řada ostrovů i s popisky spadne pod okraj obrazovky */
.island-stage {
    min-height: 100vh;
    min-height: 100dvh;
}

.island-scene {
    background: transparent;
    aspect-ratio: 3 / 2;
    /* co největší scéna, která se vejde do výšky i šířky obrazovky.
       dvh (dynamická výška viewportu) – na mobilu naležato počítáme s reálně viditelnou plochou,
       ne se 100vh, které ignoruje lištu prohlížeče a ořezávalo spodní popisky ostrovů. */
    max-width: min(96rem, calc((100vh - 3rem) * 3 / 2));
    max-height: calc(100vh - 3rem);
    max-width: min(96rem, calc((100dvh - 3rem) * 3 / 2));
    max-height: calc(100dvh - 3rem);
}

/* telefon naležato: scéna je nízká, poměr 3:2 ji srazil do malého rámečku a popisky spodních
   ostrovů se ořezávaly. Na mobilu naležato proto necháme moře s ostrovy roztáhnout přes celou
   obrazovku – zrušíme pevný poměr stran i omezení šířky kontejneru a scéna vyplní viewport. */
@media (orientation: landscape) and (max-height: 600px) {
    .island-stage {
        max-width: none;
        padding: 0.5rem 0.75rem;
    }

    .island-scene {
        aspect-ratio: auto;
        max-width: none;
        max-height: none;
        height: calc(100dvh - 1rem);
    }

    /* na šířku je text „Zpět na mapu ostrovů" moc dlouhý – necháme jen šipku */
    .back-to-map-label {
        display: none;
    }
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
}

/* telefon naležato: tlačítko zpět (jen šipka) i štítek s názvem ostrova zmenšíme, ať nezabírají moc místa */
@media (orientation: landscape) and (max-height: 600px) {
    .back-to-map-btn {
        padding: 0.2rem 0.4rem;
        font-size: 0.8rem;
    }

    .island-detail-label {
        gap: 0.35rem;
        padding: 0.2rem 0.45rem 0.2rem 0.6rem;
        font-size: 0.82rem;
        border-radius: 0.4rem;
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
</style>
