<template>
    <p class="lg:text-3xl text-2xl text-center mb-3 font-bold mb-8">
        Závěrečné vyhodnocení
    </p>

    <loading v-if="!summaryLoaded"/>

    <div v-if="summaryLoaded" class="lg:text-xl">
        <div v-if="summary.right.length" class="shadow-green-700 lg:mb-8 border-green-700 border-2 p-3 rounded-lg mb-5">
            <h1 class="max-w-2xl lg:mb-8 mb-2 text-2xl font-extrabold leading-none tracking-tight xl:text-3xl text-green-700">
                <div class="flex">
                    <div class="mx-2 align-middle">
                        <icon-hand-thumb-up class="h-10 w-10"/>
                    </div>
                    <div class="font-bold underline">Pozitiva</div>
                </div>
            </h1>

            <ul class="list-disc lg:mx-10 ml-5">
                <li v-for="data in summary.right" class="mb-5" v-html="data"></li>
            </ul>
        </div>

        <div v-if="summary.wrong.length" class="shadow-red-700 lg:mb-8 border-red-700 border-2 p-3 rounded-lg">
            <h1 class="max-w-2xl lg:mb-8 mb-2 text-2xl font-extrabold leading-none tracking-tight xl:text-3xl text-red-700">
                <div class="flex">
                    <div class="mx-2 align-middle">
                        <icon-hand-raised class="h-10 w-10"/>
                    </div>
                    <div class="font-bold underline">Negativa</div>
                </div>
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
import Loading from '../Loading.vue';
import IconHandThumbUp from '../Icons/IconHandThumbUp.vue';
import IconHandRaised from '../Icons/IconHandRaised.vue';

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
