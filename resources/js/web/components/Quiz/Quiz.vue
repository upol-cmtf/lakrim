<template>
    <div>
        <question-loader v-if="questionStore.isLoading" class="p-5"/>

        <template v-if="questionStore.isLoaded">

            <div class="grid grid-cols-6 gap-x-8">
                <div class="md:col-span-3 col-span-6 p-2 md:p-6">
                    <h1 v-if="questionStore.perex">{{ questionStore.perex }}</h1>
                    <div class="text-xl md:text-2xl md:font-semibold md:mb-10 question-description"
                         v-html="questionStore.description">
                    </div>
                </div>

                <div class="bg-slate-200 shadow-lg p-4 rounded-r-lg col-span-6 md:col-span-3 p-1 md:p-6">
                    <div class="text-xl md:text-2xl md:font-bold mb-4 underline underline-offset-4">
                        Co uděláte?
                    </div>

                    <question-option
                        v-for="option in questionStore.options"
                        :option=option
                        :key="option.id"
                        :disabled="questionStore.isLoadingEvaluation"
                    />

                    <div v-if="!questionStore.isLoadingEvaluation && !questionStore.isEvaluationLoaded"
                         class="flex justify-center items-center invisible md:visible mt-10">
                        <img src="/images/thinking-grandparents.png" class="object-center"
                             alt="Přemýšlející senioři"/>
                    </div>

                    <div v-if="questionStore.isLoadingEvaluation">
                        <p>Vaše odpověď se vyhodnocuje...</p>
                    </div>

                    <div v-if="!questionStore.isLoadingEvaluation && questionStore.isEvaluationLoaded"
                         class="relative">
                        <hr>
                        <div
                            v-if="questionStore.isAnswered && (questionStore.selectedOptionId === questionStore.answeredOptionId)"
                            class="flex my-3 lg:text-3xl border-solid border-2 p-3 rounded-lg text-center"
                            :class="{
                                'text-green-700 border-green-700': optionEvaluationStore.rightAnswer,
                                'text-red-700 border-red-700':!optionEvaluationStore.rightAnswer
                             }"
                        >
                            <div class="mx-2 align-middle">
                                <icon-hand-thumb-up v-if="optionEvaluationStore.rightAnswer" class="h-10 w-10"/>
                                <icon-hand-raised v-else class="h-10 w-10"/>
                            </div>
                            <div class="font-bold underline">{{ optionEvaluationStore.title }}</div>
                        </div>

                        <div class="text-xl" v-html="optionEvaluationStore.text"></div>

                        <div class="mt-10 sticky bottom-4 right-4 text-right">
                            <button-blue-with-arrow-right
                                v-if="!isLastQuestion"
                                @click="getQuestion"
                                class="shadow-2xl">
                                Pokračovat na další otázku
                            </button-blue-with-arrow-right>

                            <button-blue-with-arrow-right
                                v-else
                                @click="endQuiz"
                                class="shadow-2xl">
                                Přejít na dokončení
                            </button-blue-with-arrow-right>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import {defineProps, inject, onMounted, ref} from 'vue';
import {storeRespondentAnswer} from '../../services/QuizAPI.js';
import {useOptionEvaluationStore} from '../../stores/OptionEvalutationStore.js';
import {useQuestionStore} from '../../stores/QuestionStore.js';
import {useQuizSettingsStore} from '../../stores/QuizSettingsStore.js';
import {useQuizStatusBarStore} from '../../stores/QuizStatusBarStore.js';
import {useRespondentTokenStore} from '../../stores/RespondentTokenStore.js';
import ButtonBlueWithArrowRight from '../ButtonBlueWithArrowRight.vue';
import IconHandRaised from '../Icons/IconHandRaised.vue';
import IconHandThumbUp from '../Icons/IconHandThumbUp.vue';
import QuestionLoader from './QuestionLoader.vue';
import QuestionOption from './QuestionOption.vue';
import moment from 'moment';

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

const isLastQuestion = ref(false);

const evaluation = ref({
    text: null,
    hasAnotherQuestion: false,
    rightAnswer: null,
});

const optionEvaluationStore = useOptionEvaluationStore();
const questionStore = useQuestionStore();
const quizSettingsStore = useQuizSettingsStore();
const quizStatusBarStore = useQuizStatusBarStore();
const respondentTokenStore = useRespondentTokenStore();

const endQuiz = () => {
    EventBus.emit('quiz:finished');
};

const getQuestion = async () => {
    await questionStore.loadQuestion();
    window.scrollTo(0, 0);
};

const handleShowEvaluation = (optionId) => {
    optionEvaluationStore.setOptionEvaluation(questionStore.getEvaluationByOptionId(optionId));
};

const handleOptionSelected = async (optionId) => {
    if (!questionStore.isAnswerStored) {
        questionStore.selectedOptionId = optionId;
        questionStore.answeredOptionId = optionId;
        questionStore.isAnswerStored = true;
        questionStore.isLoadingEvaluation = true;

        const seconds = moment().diff(questionStore.loadedAt, 'seconds');
        questionStore.isLoadingEvaluation = true;
        const data = await storeRespondentAnswer(questionStore.id, optionId, seconds);
        if (!data) {
            return;
        }

        questionStore.setEvaluations(data.evaluations);
        isLastQuestion.value = data.end;

        optionEvaluationStore.setOptionEvaluation(questionStore.getEvaluationByOptionId(optionId));
        if (optionEvaluationStore.rightAnswer) {
            quizStatusBarStore.incrementRightAnswers();
            quizStatusBarStore.setLastTopicState(1);
        } else {
            quizStatusBarStore.incrementWrongAnswers();
        }

        questionStore.setIsEvaluationLoaded();
    } else if (quizSettingsStore.showEvaluationsForOtherOptions) {
        questionStore.selectedOptionId = optionId;
        return handleShowEvaluation(optionId);
    }
};

onMounted(async () => {
    respondentTokenStore.token = props.token;

    EventBus.on('option:clicked', (optionId) => handleOptionSelected(optionId));

    await getQuestion();
});
</script>
