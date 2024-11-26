<template>
    <p class="lg:text-3xl text-2xl text-center mb-3 font-bold">
        Závěrečné vyhodnocení
    </p>

    <loading v-if="!summaryLoaded"/>

    <div v-if="summaryLoaded" class="lg:text-xl">
        <div v-if="summary.right.length" class="shadow-green-600 lg:mb-8">
            <h1 class="max-w-2xl lg:mb-8 mb-2 text-2xl font-extrabold leading-none tracking-tight xl:text-3xl text-green-600">
                + Pozitiva
            </h1>

            <ul class="list-disc lg:mx-10 ml-5">
                <li v-for="data in summary.right" class="mb-5" v-html="data"></li>
            </ul>
        </div>

        <div v-if="summary.wrong.length">
            <h1 class="max-w-2xl lg:mb-8 mb-2 text-2xl font-extrabold leading-none tracking-tight xl:text-3xl text-red-600">
                - Negativa
            </h1>

            <ul class="list-disc lg:mx-10 ml-5">
                <li v-for="data in summary.wrong" class="mb-5" v-html="data"></li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import {getRespondentSummary} from '../../services/QuizAPI.js';
import {onMounted, ref} from 'vue';
import Loading from "../Loading.vue";

const summary = ref([]);
const summaryLoaded = ref(false);

const getSummary = async () => {
    summary.value = await getRespondentSummary();
    summaryLoaded.value = true;
};

onMounted(async () => {
    await getSummary();
});
</script>
