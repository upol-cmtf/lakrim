<template>
    <!-- moje karty bezpečí – otevřou se z nástěnky v majáku; seskupené podle ostrova -->
    <transition name="modal-fade">
        <div
            v-if="open"
            class="question-modal-backdrop fixed inset-0 z-[60] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="$t('islandGame.cards.title')"
            @click.self="$emit('close')"
        >
            <div class="cards-modal relative flex h-full max-h-[94vh] w-full max-w-[95vw] flex-col rounded-2xl bg-white p-6 shadow-2xl sm:p-8">
                <button
                    type="button"
                    class="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-blue-900 transition hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                    :aria-label="$t('islandGame.common.close')"
                    @click="$emit('close')"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>

                <h3 class="mb-1 pr-8 text-xl font-bold text-blue-900">{{ $t('islandGame.cards.title') }}</h3>
                <p class="mb-4 text-sm text-slate-600">
                    {{ $t('islandGame.cards.collected', { count: cards.length, total: totalCards }) }}
                </p>

                <p
                    v-if="cards.length === 0"
                    class="cards-empty"
                >
                    {{ $t('islandGame.cards.empty') }}
                </p>

                <div v-else class="cards-scroll">
                    <div class="cards-groups">
                    <section
                        v-for="group in groupedCards"
                        :key="group.island"
                        class="cards-group"
                    >
                        <h4 class="cards-group-title">{{ group.island }}</h4>
                        <div class="cards-grid">
                            <button
                                v-for="(card, idx) in group.cards"
                                :key="idx"
                                type="button"
                                class="collected-card"
                                :aria-label="$t('islandGame.cards.zoom')"
                                @click="activeCard = card"
                            >
                                <div class="collected-card-inner">
                                    <img :src="safetyCardImg" alt="" class="collected-card-frame">
                                    <div class="collected-card-body" v-html="card.content"></div>
                                </div>
                            </button>
                        </div>
                    </section>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!-- zvětšená karta – po kliknutí na dlaždici; čitelný text, zavře se klikem/Esc -->
    <transition name="modal-fade">
        <div
            v-if="activeCard"
            class="card-zoom-backdrop fixed inset-0 z-[70] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="$t('islandGame.cards.title')"
            @click="activeCard = null"
        >
            <div class="card-zoom" @click.stop>
                <img :src="safetyCardImg" alt="" class="card-zoom-frame">
                <div class="card-zoom-body" v-html="activeCard.content"></div>
                <button
                    type="button"
                    class="card-zoom-close"
                    :aria-label="$t('islandGame.common.close')"
                    @click="activeCard = null"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    cards: { type: Array, default: () => [] },
    totalCards: { type: Number, default: 0 },
    safetyCardImg: { type: String, default: '' },
});
defineEmits(['close']);

// karta otevřená v detailu (zvětšená)
const activeCard = ref(null);

// zavřením celého modalu zavřeme i případný zvětšený detail
watch(() => props.open, (open) => {
    if (!open) {
        activeCard.value = null;
    }
});

const onKeydown = (event) => {
    if (event.key === 'Escape' && activeCard.value) {
        activeCard.value = null;
    }
};
onMounted(() => document.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));

// karty seskupíme podle ostrova (pořadí dle prvního výskytu)
const groupedCards = computed(() => {
    const groups = [];
    const byIsland = new Map();
    for (const card of props.cards) {
        const name = card.island ?? '';
        if (!byIsland.has(name)) {
            const group = { island: name, cards: [] };
            byIsland.set(name, group);
            groups.push(group);
        }
        byIsland.get(name).cards.push(card);
    }
    return groups;
});
</script>

<style scoped lang="scss">
@use '../styles/shared' as *;

/* --- modal „Moje karty bezpečí" – malé dlaždice (náhled), detail po kliknutí --- */
.cards-scroll {
    overflow-y: auto;
    min-height: 0;
    padding-right: 0.25rem;
}

/* ostrovy v mřížce 2×2 (na úzkém displeji pod sebou) */
.cards-groups {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.25rem 1.75rem;
}

@media (max-width: 639px) {
    .cards-groups {
        grid-template-columns: 1fr;
    }
}

/* nadpis ostrova nad jeho kartami */
.cards-group-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0 0 0.5rem;
    padding-bottom: 0.3rem;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.85rem;
    font-weight: 700;
    color: #11365e;
}

.cards-group-title::before {
    content: '';
    width: 0.55rem;
    height: 0.55rem;
    border-radius: 9999px;
    background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%);
    flex: 0 0 auto;
}

/* malé dlaždice – jen náhled, čte se až ve zvětšení; cíl je vejít se bez scrollu */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(92px, 1fr));
    gap: 0.7rem 0.6rem;
    padding: 0.35rem 0.25rem 0.25rem;
}

.collected-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0.35rem 0.3rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.7rem;
    background: linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 100%);
    cursor: pointer;
    transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
}

/* na hover/fokus dlaždice povyleze a zvětší se – signál, že je klikací */
.collected-card:hover,
.collected-card:focus-visible {
    transform: translateY(-3px) scale(1.04);
    border-color: #7dd3fc;
    box-shadow: 0 8px 18px rgba(8, 38, 70, 0.18);
    outline: none;
}

/* obal jen kolem obrázku karty – text se polohuje vůči němu */
.collected-card-inner {
    position: relative;
    width: 100%;
}

.collected-card-frame {
    display: block;
    width: 100%;
    height: auto;
}

/* náhled textu v dlaždici – malý, na čtení je detail po kliknutí */
.collected-card-body {
    position: absolute;
    top: 43%;
    left: 14%;
    right: 14%;
    bottom: 13%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    color: #11365e;
    font-size: 0.55rem;
    font-weight: 600;
    line-height: 1.3;
    text-align: center;
}

.collected-card-body :deep(p) {
    margin: 0 0 0.3rem;
}

.collected-card-body :deep(p:last-child) {
    margin-bottom: 0;
}

.collected-card-body :deep(strong) {
    font-weight: 800;
}

/* --- zvětšená karta (detail) --- */
.card-zoom-backdrop {
    background: rgba(8, 38, 70, 0.6);
    backdrop-filter: blur(4px);
}

.card-zoom {
    position: relative;
    width: min(32rem, 92vw);
    filter: drop-shadow(0 20px 40px rgba(8, 38, 70, 0.45));
}

.card-zoom-frame {
    display: block;
    width: 100%;
    height: auto;
}

/* text karty v bílém poli rámu – tady už pořádně čitelný */
.card-zoom-body {
    position: absolute;
    top: 43%;
    left: 14%;
    right: 14%;
    bottom: 13%;
    display: flex;
    flex-direction: column;
    align-items: center;
    /* „safe center" = krátký text vycentruje, ale u dlouhého (přetékajícího)
       nezarovná na střed (což by ořízlo a znemožnilo doscrollovat začátek) */
    justify-content: safe center;
    overflow-y: auto;
    padding: 0 0.3rem;
    color: #11365e;
    font-size: clamp(0.85rem, 2.8vw, 1.05rem);
    font-weight: 600;
    line-height: 1.4;
    text-align: center;
}

.card-zoom-body :deep(p) {
    margin: 0 0 0.4rem;
}

.card-zoom-body :deep(p:last-child) {
    margin-bottom: 0;
}

.card-zoom-body :deep(strong) {
    font-weight: 800;
}

.card-zoom-close {
    position: absolute;
    top: -0.85rem;
    right: -0.85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.4rem;
    height: 2.4rem;
    border: 0;
    border-radius: 9999px;
    background: #fff;
    color: #11365e;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
    transition: background 0.15s ease, transform 0.15s ease;
}

.card-zoom-close:hover,
.card-zoom-close:focus-visible {
    background: #eff6ff;
    transform: scale(1.06);
    outline: none;
}

.card-zoom-close svg {
    width: 1.2rem;
    height: 1.2rem;
}
</style>
