<template>
    <!-- karta bezpečí – po správné odpovědi vyskočí vpravo nahoře a po chvilce sama zmizí -->
    <transition name="safety-card-fade">
        <div v-if="content" class="safety-card" role="status" :aria-label="$t('islandGame.safetyCard.title')">
            <img :src="frameSrc" :alt="$t('islandGame.safetyCard.title')" class="safety-card-frame">
            <div class="safety-card-body" v-html="content"></div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    content: { type: String, default: null },
    frameSrc: { type: String, default: '' },
});
</script>

<style scoped lang="scss">
/* --- karta bezpečí – vyskočí vpravo nahoře jako sběratelská karta --- */
.safety-card {
    position: fixed;
    top: 1.25rem;
    right: 1.25rem;
    z-index: 70;
    width: min(21rem, calc(100vw - 2.5rem));
    /* obrázek si nese vlastní rám i nadpis, drží svůj poměr stran */
    max-height: calc(100vh - 2.5rem);
    filter: drop-shadow(0 20px 40px rgba(8, 38, 70, 0.4));
}

.safety-card-frame {
    display: block;
    width: 100%;
    height: auto;
}

/* text karty leží v prázdném bílém poli uvnitř obrázku – kopíruje jeho rozměry, ať se vejde bez scrollu */
.safety-card-body {
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
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.45;
    text-align: center;
}

.safety-card-body :deep(p) {
    margin: 0 0 0.6rem;
}

.safety-card-body :deep(p:last-child) {
    margin-bottom: 0;
}

.safety-card-body :deep(strong) {
    font-weight: 800;
}

.safety-card-fade-enter-active {
    transition: opacity 1.1s ease, transform 1.1s cubic-bezier(0.22, 1, 0.36, 1);
}

.safety-card-fade-leave-active {
    transition: opacity 0.7s ease, transform 0.7s ease;
}

.safety-card-fade-enter-from {
    opacity: 0;
    transform: translateY(-14px) scale(0.94);
}

.safety-card-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.97);
}
</style>
