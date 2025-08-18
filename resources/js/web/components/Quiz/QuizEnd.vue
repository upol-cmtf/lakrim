<template>
    <div class="p-6">
        <p class="lg:text-3xl text-2xl text-center mb-3 font-bold mb-8">
            Vaše výsledky jsou tady
        </p>
        <p class="text-xl text-center mb-8">Jak si vedete v odhalování podvodníků?</p>

        <loading v-if="!summaryLoaded"/>

        <div v-if="summaryLoaded" class="lg:text-xl">
            <progress-bar v-if="showProgressBar" class="mb-5"/>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-2">
                <div v-if="summary.statistics.correctAnswers"
                     class="shadow-green-700 lg:mb-8 border-green-700 border-4 p-3 rounded-lg mb-5">
                    <div class="text-center font-semibold mb-8">
                        Správných odpovědí: {{ summary.statistics.correctAnswers }}<br/>
                        Úspěšnost: {{ summary.statistics.percentageCorrectAnswers }}%
                    </div>

                    <ul v-if="summary.evaluation.right.length" class="list-disc lg:mx-10 ml-5">
                        <li v-for="data in summary.evaluation.right" class="mb-5" v-html="data"></li>
                    </ul>
                </div>

                <div v-if="summary.statistics.incorrectAnswers"
                     class="shadow-red-700 lg:mb-8 border-red-700 border-4 p-3 rounded-lg">
                    <div class="text-center font-semibold mb-8">
                        Chybných odpovědí: {{ summary.statistics.incorrectAnswers }}<br/>
                        Chybovost: {{ summary.statistics.percentageIncorrectAnswers }}%
                    </div>

                    <ul v-if="summary.evaluation.wrong.length" class="list-disc lg:mx-10 ml-5">
                        <li v-for="data in summary.evaluation.wrong" class="mb-5" v-html="data"></li>
                    </ul>
                </div>
            </div>
        </div>

        <p class="text-right mt-2">
            <a :href="completionUrl"
               class="lg:text-lg inline-flex items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
                Ukončit hru a zobrazit užitečné kontakty
                <icon-arrow-right class="ml-3"></icon-arrow-right>
            </a>
        </p>
    </div>
</template>

<script setup>
import {getRespondentSummary} from '../../services/QuizAPI.js';
import {defineProps, onMounted, ref} from 'vue';
import IconArrowRight from '../Icons/IconArrowRight.vue';
import Loading from '../Loading.vue';
import ProgressBar from './ProgressBar.vue';

defineProps({
    completionUrl: {
        type: String,
        required: true,
    },
    showProgressBar: {
        type: Boolean,
        default: true,
    },
});

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
