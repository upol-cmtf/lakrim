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
                    <section
                        v-for="group in groupedCards"
                        :key="group.island"
                        class="cards-group"
                    >
                        <h4 class="cards-group-title">{{ group.island }}</h4>
                        <div class="caught-fish-grid">
                            <div
                                v-for="(card, idx) in group.cards"
                                :key="idx"
                                class="collected-card"
                            >
                                <div class="collected-card-inner">
                                    <img :src="safetyCardImg" alt="" class="collected-card-frame">
                                    <div class="collected-card-body" v-html="card.content"></div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    cards: { type: Array, default: () => [] },
    totalCards: { type: Number, default: 0 },
    safetyCardImg: { type: String, default: '' },
});
defineEmits(['close']);

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

/* --- modal „Moje karty bezpečí" – dlaždicový styl jako ulovené ryby, sekce po ostrovech --- */
.cards-scroll {
    overflow-y: auto;
    min-height: 0;
    padding-right: 0.25rem;
}

.cards-group + .cards-group {
    margin-top: 1.75rem;
}

/* nadpis ostrova nad jeho kartami */
.cards-group-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 0 0.85rem;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.95rem;
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

/* stejná mřížka i dlaždice jako u ulovených ryb */
.caught-fish-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1.75rem 1.1rem;
    padding: 0.5rem 0.25rem 0.25rem;
}

.collected-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0.9rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    background: linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 100%);
    transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
}

.collected-card:hover,
.collected-card:focus-visible {
    transform: translateY(-3px);
    border-color: #7dd3fc;
    box-shadow: 0 8px 18px rgba(8, 38, 70, 0.18);
    outline: none;
}

/* obal jen kolem obrázku karty – text se polohuje vůči němu (ne vůči celé dlaždici) */
.collected-card-inner {
    position: relative;
    width: 100%;
}

.collected-card-frame {
    display: block;
    width: 100%;
    height: auto;
}

/* text karty leží v bílém poli rámu (stejné poměry jako u vyskakovací karty) */
.collected-card-body {
    position: absolute;
    top: 43.5%;
    left: 25%;
    right: 25%;
    bottom: 22%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    color: #11365e;
    font-size: 0.66rem;
    font-weight: 600;
    line-height: 1.35;
    text-align: center;
}

.collected-card-body :deep(p) {
    margin: 0 0 0.4rem;
}

.collected-card-body :deep(p:last-child) {
    margin-bottom: 0;
}

.collected-card-body :deep(strong) {
    font-weight: 800;
}
</style>
