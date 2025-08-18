<template>
    <div class="grid md:grid-cols-2 grid-cols-1 gap-x-8 h-full">
        <div class="p-2 md:p-6 text-xl">
            <div class="md:text-2xl md:font-semibold question-description"
                 v-html="store?.question.description">
            </div>
            <div v-if="store?.question.perex" v-html="store?.question.perex" class="question-perex"></div>
        </div>

        <div class="bg-slate-200 shadow-lg p-5 md:p-6">
            <div class="sticky top-7 h-fit self-start">
                <div
                    class="text-xl font-bold mb-4"
                    v-html="store?.question.settings?.actionTitle || 'Co uděláte?'"
                >
                </div>
                <div
                    v-if="store?.question.settings?.actionPerex"
                    v-html="store?.question.settings?.actionPerex"
                    class="text-xl mb-4">
                </div>

                <question-option-item
                    v-for="option in store?.question.options"
                    :option-id="option.id"
                    :key="option.id"
                    :disabled="false"
                />

                <evaluation v-if="store.evaluated"/>

                <div class="flex justify-end mt-10 sticky bottom-4 right-4">
                    <button-blue
                        class="right"
                        disabled="true"
                        v-if="store.evaluationLoading"
                    >
                        <icon-loading class="h-5 w-5 mr-2"/>
                        <template v-if="store.question.type === 'multiselect'">Kontroluji odpovědi...</template>
                        <template v-else>Kontroluji odpověd...</template>
                    </button-blue>
                    <button-blue-with-arrow-right
                        v-else-if="!store.evaluationLoading && !store.evaluated"
                        :disabled="!store.hasSelectedOptions || store.evaluationLoading"
                        @click="handleEvaluate"
                    >
                        <template v-if="store.question.type === 'multiselect'">Hotovo, zkontrolovat odpovědi</template>
                        <template v-else>Hotovo, zkontrolovat odpověď</template>
                    </button-blue-with-arrow-right>
                    <button-blue-with-arrow-right
                        v-else-if="store.isLastAnsweredQuestion && store.allTilesAreUncovered"
                        @click="showFinalVideo"
                    >
                        Pokračovat
                    </button-blue-with-arrow-right>
                    <button-blue-with-arrow-right
                        v-else
                        @click="handleContinueToTiles"
                    >
                        Vybrat další úkol
                    </button-blue-with-arrow-right>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {inject} from 'vue';
import {useQuestionsTilesStore} from '../../stores/QuestionsTilesStore.js';
import ButtonBlueWithArrowRight from '../ButtonBlueWithArrowRight.vue';
import QuestionOptionItem from './QuestionOptionItem.vue';
import IconLoading from '../Icons/IconLoading.vue';
import ButtonBlue from '../ButtonBlue.vue';
import Evaluation from './Evaluation.vue';

const EventBus = inject('EventBus');
const store = useQuestionsTilesStore();

const handleEvaluate = () => {
    store.evaluateQuestion();
};

const handleContinueToTiles = () => {
    EventBus.emit('close-question-detail');
};

const showFinalVideo = () => {
    EventBus.emit('show-final-video');
};
</script>
