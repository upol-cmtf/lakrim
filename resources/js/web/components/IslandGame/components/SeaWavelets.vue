<template>
    <svg
        v-for="(w, i) in wavelets"
        :key="`page-wavelet-${i}`"
        :style="{ top: w.top, left: w.left, width: w.width, opacity: w.opacity }"
        class="sea-wavelet absolute"
        viewBox="0 0 40 8"
        aria-hidden="true"
    >
        <path
            :d="path"
            fill="none"
            stroke="currentColor"
            stroke-width="1.4"
            stroke-linecap="round"
            vector-effect="non-scaling-stroke"
        />
    </svg>
</template>

<script setup>
defineProps({
    wavelets: { type: Array, default: () => [] },
    path: { type: String, default: '' },
});
</script>

<style scoped lang="scss">
.sea-wavelet {
    color: rgba(255, 255, 255, 0.85);
    pointer-events: none;
    user-select: none;
}
</style>
