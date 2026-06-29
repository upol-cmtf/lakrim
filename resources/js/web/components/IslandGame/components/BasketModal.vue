<template>
    <!-- moje ulovené ryby – otevřou se z koše v majáku; klik na rybu úkol znovu otevře -->
    <transition name="modal-fade">
        <div
            v-if="open"
            class="question-modal-backdrop fixed inset-0 z-[60] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="$t('islandGame.basket.title')"
            @click.self="$emit('close')"
        >
            <div class="cards-modal relative flex max-h-[90vh] w-full max-w-3xl flex-col rounded-2xl bg-white p-6 shadow-2xl sm:p-8">
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

                <h3 class="mb-1 pr-8 text-xl font-bold text-blue-900">{{ $t('islandGame.basket.title') }}</h3>
                <p class="mb-4 text-sm text-slate-600">
                    {{ $t('islandGame.basket.caught', { count: caughtFish.length, total: easterEggsCount }) }}
                </p>

                <p
                    v-if="caughtFish.length === 0"
                    class="cards-empty"
                >
                    {{ $t('islandGame.basket.empty') }}
                </p>

                <div v-else class="caught-fish-grid">
                    <button
                        v-for="cf in caughtFish"
                        :key="cf.key"
                        type="button"
                        class="caught-fish-card"
                        @click="$emit('review', cf)"
                    >
                        <img :src="cf.img" alt="" class="caught-fish-card-img">
                        <span class="caught-fish-card-label">{{ eggTitle(cf.egg) }}</span>
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    open: { type: Boolean, default: false },
    caughtFish: { type: Array, default: () => [] },
    easterEggsCount: { type: Number, default: 0 },
    eggTitle: { type: Function, required: true },
});
defineEmits(['close', 'review']);
</script>

<style scoped lang="scss">
@use '../styles/shared' as *;

/* --- modal „Moje ulovené ryby" --- */
.caught-fish-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    /* větší mezery (hlavně mezi řádky), ať se zvětšení karty na hoveru vejde */
    gap: 1.75rem 1.1rem;
    overflow-y: auto;
    min-height: 0;
    /* prostor nahoře/po stranách, aby povylezlá karta nezajela pod hlavičku ani okraj */
    padding: 0.5rem 0.25rem 0.25rem;
}

/* ulovená ryba je tlačítko – klik znovu otevře daný úkol */
.caught-fish-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 0.9rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    background: linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 100%);
    cursor: pointer;
    transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
}

.caught-fish-card:hover,
.caught-fish-card:focus-visible {
    transform: translateY(-3px);
    border-color: #7dd3fc;
    box-shadow: 0 8px 18px rgba(8, 38, 70, 0.18);
    outline: none;
}

/* jednotná výška obrázku – vysoká rybka (mořský koník) se vejde celá,
   ostatní se vycentrují, takže nadpisy pak sedí v jedné linii */
.caught-fish-card-img {
    display: block;
    width: 100%;
    max-width: 130px;
    height: 120px;
    object-fit: contain;
    object-position: center;
    filter: drop-shadow(0 4px 5px rgba(8, 38, 70, 0.25));
}

.caught-fish-card-label {
    /* nadpis vždy dole na kartě, ať řádek nadpisů lícuje napříč mřížkou */
    margin-top: auto;
    color: #11365e;
    font-size: 0.82rem;
    font-weight: 700;
    line-height: 1.25;
    text-align: center;
}
</style>
