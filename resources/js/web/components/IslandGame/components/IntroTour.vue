<template>
    <transition name="modal-fade">
        <div v-if="active" class="tour">
            <!-- blokuje kliknutí na mapu během prohlídky -->
            <div class="tour-blocker"></div>

            <!-- ztmavení: buď bodové (díra na prvku), nebo celé (úvod/závěr) -->
            <div v-if="targetRect" class="tour-spotlight" :style="spotlightStyle"></div>
            <div v-else class="tour-dim"></div>

            <div class="tour-bubble" :class="`tour-bubble--${bubblePosition}`">
                <h3 class="tour-bubble-title">{{ $t(`islandGame.tour.${step.key}.title`) }}</h3>
                <p class="tour-bubble-text">{{ $t(`islandGame.tour.${step.key}.text`) }}</p>

                <div class="tour-dots" aria-hidden="true">
                    <span
                        v-for="(s, i) in steps"
                        :key="i"
                        class="tour-dot"
                        :class="{ 'tour-dot--active': i === index }"
                    ></span>
                </div>

                <div class="tour-actions">
                    <button type="button" class="tour-skip" @click="$emit('finish')">
                        {{ $t('islandGame.tour.skip') }}
                    </button>
                    <span class="tour-actions-right">
                        <button
                            v-if="index > 0"
                            type="button"
                            class="tour-btn tour-btn--ghost"
                            @click="back"
                        >
                            {{ $t('islandGame.tour.back') }}
                        </button>
                        <button type="button" class="tour-btn" @click="next">
                            {{ isLast ? $t('islandGame.tour.start') : $t('islandGame.tour.next') }}
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    active: { type: Boolean, default: false },
});
const emit = defineEmits(['finish']);

// jednotlivé kroky prohlídky; selector = prvek na mapě, který se zvýrazní (null = vycentrovaná bublina)
const steps = [
    { key: 'welcome', selector: null },
    // u ostrova přidáme dole místo navíc, ať se do výřezu vejde i popisek pod ním
    { key: 'islands', selector: '.island-wrap:not(.lighthouse-wrap)', padBottom: 46 },
    // k majáku přidáme do výřezu i okolní mraky a počítadlo karet
    { key: 'lighthouse', selector: '.lighthouse-wrap', include: '.lighthouse-cloud, .lighthouse-counter' },
    { key: 'fish', selector: '.sea-fish' },
    { key: 'go', selector: null },
];

const index = ref(0);
const targetRect = ref(null);

const step = computed(() => steps[index.value]);
const isLast = computed(() => index.value === steps.length - 1);

const PADDING = 14;

// je střed prvku uvnitř viditelné oblasti okna?
const isInViewport = (node) => {
    const r = node.getBoundingClientRect();
    if (r.width === 0 && r.height === 0) {
        return false;
    }
    const cx = r.left + r.width / 2;
    const cy = r.top + r.height / 2;
    return cx >= 0 && cx <= window.innerWidth && cy >= 0 && cy <= window.innerHeight;
};

const updateRect = async () => {
    await nextTick();
    const selector = step.value.selector;
    // vybíráme jen prvek viditelný v okně (rybka může být rozplavaná mimo obraz);
    // když žádný takový není, ukážeme vycentrovanou bublinu místo „odletu" výřezu
    const el = selector
        ? Array.from(document.querySelectorAll(selector)).find(isInViewport) ?? null
        : null;
    if (!el) {
        targetRect.value = null;
        return;
    }
    const r = el.getBoundingClientRect();

    // případně rozšíříme výřez o další prvky (mraky kolem majáku apod.)
    let top = r.top;
    let left = r.left;
    let right = r.right;
    let bottom = r.bottom;
    if (step.value.include) {
        document.querySelectorAll(step.value.include).forEach((node) => {
            // mraky můžou později odletět mimo obraz – do unionu bereme jen viditelné
            if (!isInViewport(node)) {
                return;
            }
            const nr = node.getBoundingClientRect();
            top = Math.min(top, nr.top);
            left = Math.min(left, nr.left);
            right = Math.max(right, nr.right);
            bottom = Math.max(bottom, nr.bottom);
        });
    }

    const padBottom = step.value.padBottom ?? 0;
    targetRect.value = {
        top: top - PADDING,
        left: left - PADDING,
        width: (right - left) + PADDING * 2,
        height: (bottom - top) + PADDING * 2 + padBottom,
        centerY: (top + bottom) / 2,
    };
};

watch(() => props.active, (active) => {
    if (active) {
        index.value = 0;
        updateRect();
    }
}, { immediate: true });
watch(index, updateRect);

const onResize = () => updateRect();
onMounted(() => window.addEventListener('resize', onResize));
onBeforeUnmount(() => window.removeEventListener('resize', onResize));

const next = () => {
    if (isLast.value) {
        // poslední krok „Začít" prohlídku ukončí
        emit('finish');
    } else {
        index.value += 1;
    }
};
const back = () => {
    if (index.value > 0) {
        index.value -= 1;
    }
};

// bublina: vycentrovaná (bez cíle), nebo nahoře/dole podle polohy zvýrazněného prvku
const bubblePosition = computed(() => {
    if (!targetRect.value) {
        return 'center';
    }
    return targetRect.value.centerY > window.innerHeight / 2 ? 'top' : 'bottom';
});

const spotlightStyle = computed(() => {
    if (!targetRect.value) {
        return {};
    }
    return {
        top: `${targetRect.value.top}px`,
        left: `${targetRect.value.left}px`,
        width: `${targetRect.value.width}px`,
        height: `${targetRect.value.height}px`,
    };
});
</script>

<style scoped lang="scss">
@use '../styles/shared' as *;

.tour {
    position: fixed;
    inset: 0;
    z-index: 75;
}

.tour-blocker {
    position: fixed;
    inset: 0;
}

.tour-dim {
    position: fixed;
    inset: 0;
    background: rgba(8, 38, 70, 0.62);
}

/* bodové ztmavení – díra na prvku přes velký box-shadow */
.tour-spotlight {
    position: fixed;
    border-radius: 1rem;
    box-shadow:
        0 0 0 9999px rgba(8, 38, 70, 0.62),
        0 0 0 3px rgba(255, 224, 140, 0.9),
        0 0 22px 4px rgba(255, 224, 140, 0.55);
    pointer-events: none;
    transition: top 0.3s ease, left 0.3s ease, width 0.3s ease, height 0.3s ease;
}

.tour-bubble {
    position: fixed;
    left: 50%;
    width: min(24rem, calc(100vw - 2rem));
    padding: 1.25rem 1.4rem 1.1rem;
    border-radius: 1.1rem;
    background: rgba(255, 255, 255, 0.98);
    color: #11365e;
    box-shadow: 0 12px 32px rgba(8, 38, 70, 0.45);
    text-align: left;
}

.tour-bubble--center {
    top: 50%;
    transform: translate(-50%, -50%);
}

.tour-bubble--bottom {
    bottom: 2rem;
    transform: translateX(-50%);
}

.tour-bubble--top {
    top: 2rem;
    transform: translateX(-50%);
}

.tour-bubble-title {
    margin: 0 0 0.4rem;
    font-size: 1.15rem;
    font-weight: 800;
}

.tour-bubble-text {
    margin: 0 0 0.9rem;
    font-size: 0.95rem;
    font-weight: 500;
    line-height: 1.5;
    color: #334155;
}

.tour-dots {
    display: flex;
    justify-content: center;
    gap: 0.4rem;
    margin-bottom: 0.9rem;
}

.tour-dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 9999px;
    background: #cbd5e1;
    transition: background 0.2s ease, transform 0.2s ease;
}

.tour-dot--active {
    background: #f59e0b;
    transform: scale(1.2);
}

.tour-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.tour-actions-right {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.tour-skip {
    border: 0;
    background: transparent;
    color: #64748b;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0.3rem 0.2rem;
}

.tour-skip:hover,
.tour-skip:focus-visible {
    color: #334155;
    outline: none;
}

.tour-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1.1rem;
    border: 0;
    border-radius: 0.6rem;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%);
    color: #3a2a05;
    box-shadow: 0 3px 8px rgba(245, 158, 11, 0.4);
    transition: filter 0.15s ease, transform 0.15s ease;
}

.tour-btn:hover,
.tour-btn:focus-visible {
    filter: brightness(1.05);
    transform: translateY(-1px);
    outline: none;
}

.tour-btn--ghost {
    background: #fff;
    color: #11365e;
    box-shadow: inset 0 0 0 1px rgba(20, 74, 120, 0.35);
}
</style>
