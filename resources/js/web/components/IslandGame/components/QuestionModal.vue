<template>
    <transition name="modal-fade">
        <div
            v-if="question"
            class="question-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="question.title"
            @click.self="$emit('close')"
        >
            <div
                class="question-modal relative flex h-full w-full max-w-[1600px] flex-col rounded-2xl bg-white p-6 sm:p-8 shadow-2xl"
                :class="{ 'question-modal--dimmed': dimmed }"
            >
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

                <!-- prohlížení vyřešeného kamene – info, že je situace už zodpovězená -->
                <div
                    v-if="reviewMode"
                    class="review-banner mb-4 mr-10 sm:mr-12"
                    role="status"
                >
                    <svg viewBox="0 0 24 24" class="review-banner-icon" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" fill="currentColor"/>
                        <path d="M12 11v5" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/>
                        <circle cx="12" cy="8" r="1.2" fill="#fff"/>
                    </svg>
                    <span>{{ $t('islandGame.question.alreadyAnswered') }}</span>
                </div>

                <!-- odpočet času – jen u situací s limitem (settings.time_limit) -->
                <div
                    v-if="questionTimeLimit !== null && !reviewMode"
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
                        <span class="question-timer-label">{{ timeIsLow ? $t('islandGame.question.hurry') : $t('islandGame.question.timeLabel') }}</span>
                    </div>
                    <div class="question-timer-track" aria-hidden="true">
                        <div class="question-timer-fill" :style="{ width: `${timerPercent}%` }"></div>
                    </div>
                </div>

                <!-- na mobilu scrolluje celý řádek (sloupce jsou pod sebou),
                     na desktopu scrollují sloupce samostatně – ať dlouhé zadání nehýbe celým modalem -->
                <div class="flex min-h-0 flex-1 flex-col gap-6 overflow-y-auto sm:flex-row sm:gap-8 sm:overflow-hidden">
                    <!-- levá část: popis situace (vlastní scroll u delšího zadání) -->
                    <div class="sm:w-3/5 sm:min-h-0 sm:overflow-y-auto sm:pr-3">
                        <p
                            v-if="question.perex"
                            class="mb-3 text-sm font-semibold leading-relaxed text-slate-800"
                        >
                            {{ question.perex }}
                        </p>

                        <div
                            v-if="question.description"
                            :class="[
                                'question-modal-text text-sm leading-relaxed text-slate-700',
                                { 'question-modal-text--cover': descriptionImageOnly },
                            ]"
                            v-html="question.description"
                            @click="$emit('zoom', $event)"
                        ></div>
                    </div>

                    <!-- pravá část: možnosti na výběr (vlastní scroll, ať se neořízne při delším seznamu) -->
                    <div class="sm:w-2/5 sm:border-l sm:border-slate-200 sm:pl-8 sm:min-h-0 sm:overflow-y-auto">
                        <h3 class="mb-3 text-base font-semibold text-slate-900">
                            {{ $t('islandGame.question.prompt') }}
                        </h3>
                        <p class="mb-4 text-sm text-slate-600">
                            {{ reviewMode
                                ? $t('islandGame.question.reviewHint')
                                : $t('islandGame.question.chooseHint') }}
                        </p>
                        <ul class="space-y-2">
                        <li
                            v-for="option in question.options"
                            :key="option.id"
                        >
                            <button
                                type="button"
                                :class="optionClass(option)"
                                :disabled="isOptionDisabled(option)"
                                @click="$emit('answer', option)"
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
</template>

<script setup>
defineProps({
    question: { type: Object, default: null },
    reviewMode: { type: Boolean, default: false },
    dimmed: { type: Boolean, default: false },
    questionTimeLimit: { type: Number, default: null },
    timeIsLow: { type: Boolean, default: false },
    formattedRemainingTime: { type: String, default: '' },
    timerPercent: { type: Number, default: 0 },
    descriptionImageOnly: { type: Boolean, default: false },
    optionClass: { type: Function, required: true },
    optionVariant: { type: Function, required: true },
    isOptionDisabled: { type: Function, required: true },
});
defineEmits(['close', 'answer', 'zoom']);
</script>

<style scoped lang="scss">
@use '../styles/shared' as *;

/* vnitřní transform modalu (základní fade řeší sdílený _shared.scss) */
.modal-fade-enter-active .question-modal,
.modal-fade-leave-active .question-modal {
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-fade-enter-from .question-modal,
.modal-fade-leave-to .question-modal {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
}

/* když průvodce zobrazí bublinu nad modal, ztlumíme obsah modalu, aby s ní nesoupeřil */
.question-modal--dimmed {
    transition: filter 0.2s ease, opacity 0.2s ease;
    filter: blur(2px) grayscale(0.7) brightness(0.92);
    opacity: 0.9;
    pointer-events: none;
}

.question-modal-text :deep(img) {
    display: block;
    max-width: 100%;
    max-height: 30rem;
    width: auto;
    height: auto;
    margin: 0 auto 0.75rem;
    border-radius: 0.5rem;
    /* obrázek lze kliknutím zvětšit */
    cursor: zoom-in;
    transition: filter 0.18s ease;
}

.question-modal-text :deep(img:hover) {
    filter: brightness(1.04) drop-shadow(0 4px 10px rgba(8, 38, 70, 0.3));
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

/* --- info pruh při prohlížení už vyřešené situace (neutrální, modré) --- */
.review-banner {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.7rem 0.95rem;
    border-radius: 0.75rem;
    font-size: 0.9rem;
    font-weight: 600;
    line-height: 1.35;
    background: #eff6ff;
    color: #1e3a8a;
    border: 1px solid #bfdbfe;
}

.review-banner-icon {
    flex-shrink: 0;
    width: 1.4rem;
    height: 1.4rem;
    color: #3b82f6;
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

@media (prefers-reduced-motion: reduce) {
    .question-timer--low .question-timer-badge { animation: none; }
}
</style>
