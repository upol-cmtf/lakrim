<template>
    <p class="lg:text-3xl text-center mb-3 font-bold">
        Závěrečné vyhodnocení
    </p>

    <div class="lg:text-xl">
        <div v-for="data in summary" class="mb-5" v-html="data"></div>
    </div>
</template>

<script setup>
import {getRespondentSummary} from '../../services/QuizAPI.js';
import {onMounted, ref} from 'vue';

const summary = ref([]);

const getSummary = async () => {
    summary.value = await getRespondentSummary();
};

onMounted(async () => {
    await getSummary();
});
</script>