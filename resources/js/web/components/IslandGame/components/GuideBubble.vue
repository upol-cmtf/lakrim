<template>
    <div
        class="island-guide absolute bottom-0 right-0"
        :class="{ 'island-guide--above-modal': aboveModal }"
    >
        <transition name="guide-fade">
            <div
                v-if="message"
                ref="bubbleEl"
                class="island-guide-bubble"
                role="status"
            >
                <span
                    v-if="tone !== 'default'"
                    :class="['island-guide-shield', `island-guide-shield--${tone}`]"
                    aria-hidden="true"
                >
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2l8 3v6c0 5-3.5 9.5-8 11-4.5-1.5-8-6-8-11V5l8-3z" fill="currentColor"/>
                        <path
                            v-if="tone === 'success'"
                            d="M8.5 12l2.5 2.5L16 9.5"
                            fill="none"
                            stroke="#fff"
                            stroke-width="2.2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            v-else-if="tone === 'failure'"
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
                    :aria-label="$t('islandGame.common.close')"
                    @click="$emit('close')"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="island-guide-text" v-html="message"></div>
                <button
                    v-if="action"
                    type="button"
                    class="island-guide-action"
                    @click="$emit('run-action')"
                >
                    {{ action.label }}
                </button>
            </div>
        </transition>
        <!-- kliknutí na průvodce kdykoliv znovu vyvolá úvodní zprávu ostrova -->
        <button
            type="button"
            class="island-guide-img-btn"
            :aria-label="$t('islandGame.guide.replay')"
            @click="$emit('replay-intro')"
        >
            <img :src="guideImage" :alt="$t('islandGame.guideAlt')" class="island-guide-img">
        </button>
    </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
    guideImage: { type: String, default: null },
    message: { type: String, default: null },
    tone: { type: String, default: 'default' },
    action: { type: Object, default: null },
    aboveModal: { type: Boolean, default: false },
});
const emit = defineEmits(['close', 'run-action', 'replay-intro']);

// kliknutí kamkoliv mimo bublinu ji zavře
const bubbleEl = ref(null);
let outsideClickTimer = null;

const handleOutsideClick = (event) => {
    if (bubbleEl.value && !bubbleEl.value.contains(event.target)) {
        emit('close');
    }
};

watch(() => props.message, (value) => {
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
</script>

<style scoped lang="scss">
.island-guide {
    /* nižší než modal (z-50), aby průvodce zůstal za detailem situace */
    z-index: 40;
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
    /* širší prostor, aby se dlouhé intro roztáhlo do šířky a nepřetékalo nahoře */
    max-width: min(96vw, 76rem);
    padding: 0.75rem;
}

/* nad otevřeným modalem – průvodce stojí v rohu obrazovky, bublina je vždy vidět */
.island-guide--above-modal {
    position: fixed;
    z-index: 60;
}

/* obal průvodce je tlačítko – klik znovu vyvolá úvodní zprávu ostrova */
.island-guide-img-btn {
    flex-shrink: 0;
    padding: 0;
    border: 0;
    background: transparent;
    cursor: pointer;
    line-height: 0;
    -webkit-tap-highlight-color: transparent;
}

.island-guide-img-btn:focus-visible {
    outline: none;
}

.island-guide-img {
    display: block;
    height: 14rem;
    width: auto;
    filter: drop-shadow(0 6px 8px rgba(8, 38, 70, 0.45));
    user-select: none;
    /* klik chytá obalující tlačítko */
    pointer-events: none;
    transition: transform 0.2s ease, filter 0.2s ease;
}

/* náznak, že je průvodce klikatelný */
.island-guide-img-btn:hover .island-guide-img,
.island-guide-img-btn:focus-visible .island-guide-img {
    transform: translateY(-3px) scale(1.04);
    filter:
        drop-shadow(0 8px 10px rgba(8, 38, 70, 0.5))
        drop-shadow(0 0 12px rgba(255, 238, 170, 0.65));
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

/* dlouhé intro neroztáhne bublinu nad horní okraj obrazovky – text se uvnitř roluje */
.island-guide-text {
    max-height: calc(100vh - 12rem);
    overflow-y: auto;
    overscroll-behavior: contain;
    /* prostor pro scrollbar, ať nepřekrývá text */
    padding-right: 0.25rem;
    margin-right: -0.25rem;
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

/* telefon naležato: nízká obrazovka – průvodce i jeho bublinu výrazně zmenšíme, ať nezabírají skoro celý displej */
@media (orientation: landscape) and (max-height: 600px) {
    .island-guide {
        gap: 0.4rem;
        padding: 0.4rem;
        /* drží se v pravé části obrazovky – vlevo je místo pro kartu bezpečí */
        max-width: 56vw;
    }
    .island-guide-img {
        height: 6.5rem;
        width: auto;
    }
    .island-guide-bubble {
        font-size: 0.78rem;
        line-height: 1.35;
        padding: 0.55rem 0.8rem;
        margin-bottom: 1.75rem;
        border-radius: 1rem;
    }
    .island-guide-text {
        max-height: calc(100dvh - 4rem);
    }
    .island-guide-action {
        margin-top: 0.8rem;
        padding: 0.4rem 0.9rem;
        font-size: 0.82rem;
    }
    .island-guide-shield {
        width: 2rem;
        height: 2rem;
        top: -0.8rem;
        left: -0.8rem;
    }
    .island-guide-shield svg {
        width: 1.3rem;
        height: 1.3rem;
    }
    .island-guide-close {
        width: 1.6rem;
        height: 1.6rem;
        top: -0.55rem;
        right: -0.55rem;
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
</style>
