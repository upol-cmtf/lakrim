<template>
    <question-loader v-if="questionState.loading"/>

    <template v-if="questionState.success && !questionState.loading">

        <div class="grid grid-cols-6 gap-x-8">
            <div class="md:col-span-3 col-span-6 p-2 md:p-6">
                <h1 v-if="question.perex">{{ question.perex }}</h1>
                <div class="text-xl md:text-2xl md:mb-10 question-description"
                     v-html="question.description"></div>
            </div>

            <div class="bg-gray-50 shadow-lg p-4 rounded-lg col-span-6 md:col-span-3 p-1 md:p-6">
                <div class="text-xl md:text-2xl md:font-bold mb-4 underline underline-offset-4">Co uděláte?</div>

                <div v-for="option in question.options"
                     :key="option.id">
                    <button-blue class="my-3 disabled:cursor-not-allowed"
                                 :class="{'disabled:bg-gray-400': evaluation.loading || (option.id !== clickedButtonId), 'disabled:bg-emerald-600': (option.id === clickedButtonId && evaluation.rightAnswer === true), 'disabled:bg-rose-600': (option.id === clickedButtonId && evaluation.rightAnswer === false)}"
                                 :disabled="evaluationState.loading || evaluationState.success"
                                 @click="selectOption(option.id)">
                        <!--                        {{ String.fromCharCode(65 + index) }}) {{ option.name }}-->
                        {{ option.name }}
                    </button-blue>
                </div>

                <div v-if="!evaluationState.loading && !evaluationState.success"
                     class="flex justify-center items-center invisible md:visible mt-10">
                    <img src="/images/thinking-grandparents.png" class="object-center"/>
                </div>

                <div v-if="evaluationState.loading">
                    <p>Vaše odpověď se vyhodnocuje...</p>
                </div>

                <div v-if="!evaluationState.loading && evaluationState.success">
                    <hr>
                    <div class="flex my-3 lg:text-3xl border-solid border-2 p-3 rounded-lg text-center"
                         :class="{'text-green-700 border-green-700': evaluation.rightAnswer, 'text-red-700 border-red-700':!evaluation.rightAnswer}">
                        <div class="mx-2 align-middle">
                            <icon-hand-thumb-up v-if="evaluation.rightAnswer" class="h-10 w-10"/>
                            <icon-hand-raised v-else class="h-10 w-10"/>
                        </div>
                        <div class="font-bold underline">{{ evaluation.title }}</div>
                    </div>

                    <div class="text-xl" v-html="evaluation.text"></div>

                    <div class="mt-10">
                        <button-blue-with-arrow-right v-if="evaluation.hasAnotherQuestion" @click="getQuestion">
                            Pokračovat na další otázku
                        </button-blue-with-arrow-right>

                        <button-blue-with-arrow-right v-else @click="endQuiz">
                            Přejít na dokončení
                        </button-blue-with-arrow-right>
                    </div>
                </div>
            </div>
        </div>
    </template>
</template>

<script setup>
import {defineProps, inject, onMounted, ref} from 'vue';
import {useRespondentTokenStore} from '../../stores/RespondentTokenStore.js';
import {loadQuestion, storeRespondentAnswer} from '../../services/QuizAPI.js';
import ButtonBlue from '../ButtonBlue.vue';
import ButtonBlueWithArrowRight from '../ButtonBlueWithArrowRight.vue';
import QuestionLoader from './QuestionLoader.vue';
import moment from 'moment';
import IconHandThumbUp from '../Icons/IconHandThumbUp.vue';
import IconHandRaised from "../Icons/IconHandRaised.vue";

const EventBus = inject('EventBus');

const props = defineProps({
    token: {
        type: String,
        required: true,
    },
});

const question = ref({
    id: null,
    perex: null,
    description: null,
    options: [],
    loadedAt: null,
});

const questionState = ref({
    loading: true,
    error: false,
    success: false,
});

const evaluation = ref({
    text: null,
    hasAnotherQuestion: false,
    rightAnswer: null,
});

const evaluationState = ref({
    loading: false,
    error: false,
    success: false,
});

const respondentTokenStore = useRespondentTokenStore();

const endQuiz = () => {
    EventBus.emit('quiz:finished');
};

const clickedButtonId = ref(null);

const clearEvaluation = () => {
    evaluation.value.title = null;
    evaluation.value.text = null;
    evaluation.value.hasAnotherQuestion = false;
    evaluation.value.rightAnswer = null;

    evaluationState.value.loading = false;
    evaluationState.value.error = false;
    evaluationState.value.success = false;
};

const getQuestion = async () => {
    clearEvaluation();
    questionState.value.loading = true;

    const questionData = await loadQuestion();

    if (!questionData) {
        // TODO nejaka chyba
        return;
    }

    question.value.id = questionData.id;
    question.value.perex = questionData.perex;
    question.value.description = questionData.description;
    question.value.options = questionData.options;
    question.value.loadedAt = moment();

    questionState.value.loading = false;
    questionState.value.success = true;

    window.scrollTo(0, 0);
};

const selectOption = async (optionId) => {
    clickedButtonId.value = optionId;
    const seconds = moment().diff(question.value.loadedAt, 'seconds');

    evaluationState.value.loading = true;
    const evaluationData = await storeRespondentAnswer(question.value.id, optionId, seconds);

    evaluation.value.title = evaluationData.evaluationTitle;
    evaluation.value.text = evaluationData.evaluation;
    evaluation.value.hasAnotherQuestion = !evaluationData.end;
    evaluation.value.rightAnswer = evaluationData.rightAnswer;

    evaluationState.value.loading = false;
    evaluationState.value.success = true;
};

onMounted(async () => {
    respondentTokenStore.setToken(props.token);

    await getQuestion();
});
</script>
