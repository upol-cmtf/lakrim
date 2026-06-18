<template>
    <!-- bonusový úkol (easter egg) – otevře se kliknutím na rybku ve scéně -->
    <transition name="modal-fade">
        <div
            v-if="open"
            class="question-modal-backdrop fixed inset-0 z-[60] flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="$t('islandGame.easterEgg.aria')"
            @click.self="$emit('close')"
        >
            <div class="easter-egg-modal relative flex max-h-[92vh] w-full max-w-[1500px] flex-col rounded-2xl bg-white p-6 shadow-2xl sm:p-8">
                <button
                    type="button"
                    class="absolute right-3 top-3 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-blue-900 transition hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                    :aria-label="$t('islandGame.common.close')"
                    @click="$emit('close')"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>

                <p v-if="loading" class="py-8 text-center text-slate-500">{{ $t('islandGame.easterEgg.loading') }}</p>
                <p v-else-if="empty" class="py-8 text-center text-slate-500">
                    {{ $t('islandGame.easterEgg.empty') }}
                </p>
                <template v-else>
                    <div
                        class="easter-egg-content min-h-0 flex-1 overflow-y-auto text-sm leading-relaxed text-slate-700"
                        v-html="content"
                    ></div>

                    <!-- tlačítko na vyhodnocení – jen když má easter egg evaluation -->
                    <div
                        v-if="egg && egg.evaluation"
                        class="mt-5 flex justify-end gap-3 border-t border-slate-200 pt-4"
                    >
                        <button
                            v-if="view === 'description'"
                            type="button"
                            class="easter-egg-btn"
                            @click="$emit('update:view', 'evaluation')"
                        >
                            {{ $t('islandGame.easterEgg.showEvaluation') }}
                        </button>
                        <button
                            v-else
                            type="button"
                            class="easter-egg-btn easter-egg-btn--ghost"
                            @click="$emit('update:view', 'description')"
                        >
                            {{ $t('islandGame.easterEgg.backToTask') }}
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    open: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    empty: { type: Boolean, default: false },
    content: { type: String, default: null },
    egg: { type: Object, default: null },
    view: { type: String, default: 'description' },
});
defineEmits(['close', 'update:view']);
</script>

<style scoped lang="scss">
@use '../styles/shared' as *;

/* tlačítko ve spodní liště modalu easter eggu (zobrazit vyhodnocení / zpět) */
.easter-egg-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.55rem 1.15rem;
    border-radius: 0.6rem;
    background: linear-gradient(180deg, #2c79b0 0%, #144a78 100%);
    color: #fff;
    font-size: 0.9rem;
    font-weight: 600;
    line-height: 1.2;
    box-shadow: 0 2px 6px rgba(8, 38, 70, 0.35);
    transition: transform 0.15s ease, filter 0.15s ease;
}

.easter-egg-btn:hover {
    filter: brightness(1.08);
}

.easter-egg-btn:active {
    transform: translateY(1px);
}

.easter-egg-btn--ghost {
    background: #fff;
    color: #144a78;
    box-shadow: inset 0 0 0 1px rgba(20, 74, 120, 0.4);
}

/* layout obsahu easter eggu řídíme tady (scoped přes :deep), */
/* protože <style> vložený přes v-html se ve scoped komponentě nemusí uplatnit */

/* titulek easter eggu – přichází z HTML (description/evaluation), proto ho stylujeme tady */
/* padding-right dělá místo pro křížek (zavřít) vpravo nahoře */
.easter-egg-content :deep(.ee-title) {
    margin: 0 0 1rem;
    padding-right: 2.5rem;
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.3;
    color: #1e3a8a;
}

/* popisek nad obrázky – odsazení, ať obrázky nejsou nalepené na text */
.easter-egg-content :deep(.ee-diff__hint) {
    margin: 0 0 16px;
}

.easter-egg-content :deep(.ee-diff__images) {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    justify-content: center;
    align-items: flex-start;
}

/* dva obrázky vedle sebe – sdílejí řádek rovným dílem, na řádku se nezalomí */
.easter-egg-content :deep(.ee-diff__images .easter-egg-image) {
    flex: 1 1 0;
    min-width: 0;
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

/* na úzkém displeji se obrázky zalomí pod sebe */
@media (max-width: 639px) {
    .easter-egg-content :deep(.ee-diff__images .easter-egg-image) {
        flex-basis: 100%;
    }
}

/* vyhodnocení (jeden obrázek) – vycentrované */
.easter-egg-content :deep(.ee-diff--result .ee-diff__images) {
    justify-content: center;
}

/* vyhodnocení má dva obrázky vedle sebe – stejně jako zadání sdílejí řádek rovným dílem */
.easter-egg-content :deep(.ee-diff--result .easter-egg-image) {
    flex: 1 1 0;
    min-width: 0;
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

/* na úzkém displeji i vyhodnocení pod sebe (musí být až za pravidlem výše, ať ho přebije) */
@media (max-width: 639px) {
    .easter-egg-content :deep(.ee-diff--result .easter-egg-image) {
        flex-basis: 100%;
    }
}
</style>
