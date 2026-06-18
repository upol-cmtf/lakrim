<template>
    <!-- rybky (easter eggy) plují přes celou modrou plochu, za ostrovy; vrstva sama nechytá klik, jen rybky -->
    <div class="sea-fish-layer">
        <button
            v-for="f in fish"
            :key="f.key"
            type="button"
            class="sea-fish absolute"
            :style="fishStyle(f)"
            :aria-label="$t('islandGame.sea.catchFishAria')"
            @click="$emit('catch', f)"
        >
            <span class="sea-fish-bob">
                <img class="sea-fish-img" :src="f.img" :style="fishInnerStyle(f)" alt="" aria-hidden="true">
            </span>
        </button>
    </div>
</template>

<script setup>
defineProps({
    fish: { type: Array, default: () => [] },
    fishStyle: { type: Function, required: true },
    fishInnerStyle: { type: Function, required: true },
});
defineEmits(['catch']);
</script>

<style scoped lang="scss">
/* --- rybky (easter eggy) plující přes celou plochu moře, za ostrovy --- */
/* plnoplošná vrstva pod centrálním obsahem; vrstvu kliky ignorují, reagují jen rybky */
.sea-fish-layer {
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
}

.sea-fish {
    width: 3.4%;
    padding: 0;
    border: 0;
    background: transparent;
    cursor: pointer;
    /* rybky reagují na klik i přes propustný kontejner; ostrovy mají vyšší z-index */
    pointer-events: auto;
    z-index: 1;
    transform: scale(var(--fish-scale, 1));
    transform-origin: center;
    transition: transform 0.2s ease;
    animation-name: fish-cross;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
    will-change: left;
}

/* najetí myší / fokus – rybka se zastaví, zvětší a vyskočí nad ostrovy se září */
.sea-fish:hover,
.sea-fish:focus-visible {
    z-index: 5;
    transform: scale(calc(var(--fish-scale, 1) * 1.35));
    animation-play-state: paused;
    outline: none;
}

.sea-fish:hover .sea-fish-bob,
.sea-fish:focus-visible .sea-fish-bob {
    animation-play-state: paused;
}

@media (max-width: 639px) {
    .sea-fish {
        width: 6%;
    }
}

@keyframes fish-cross {
    from { left: -12%; }
    to   { left: 112%; }
}

.sea-fish-bob {
    display: block;
    width: 100%;
    animation: fish-bob 3.2s ease-in-out infinite;
    will-change: transform;
}

@keyframes fish-bob {
    0%, 100% { transform: translateY(0) rotate(-2deg); }
    50%      { transform: translateY(-14%) rotate(2deg); }
}

.sea-fish-img {
    display: block;
    width: 100%;
    height: auto;
    filter: drop-shadow(0 4px 4px rgba(8, 38, 70, 0.4));
    transition: filter 0.2s ease;
}

.sea-fish:hover .sea-fish-img,
.sea-fish:focus-visible .sea-fish-img {
    filter:
        drop-shadow(0 4px 4px rgba(8, 38, 70, 0.45))
        drop-shadow(0 0 10px rgba(255, 224, 140, 0.95));
}
</style>
