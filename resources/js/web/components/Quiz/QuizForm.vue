<template>
    <progress-bar v-if="showQuiz" class="sticky top-0"/>

    <div
        class="flex flex-col md:my-6 bg-white md:shadow-lg border border-slate-200 md:rounded-lg w-full md:h-min-[500px]">
        <quiz v-if="showQuiz" :token="token" class="mt-5"/>

        <respondent-identification v-if="showRespondentIdentification" class="p-4"/>

        <quiz-end v-if="showQuizEnd" class="p-4"/>
    </div>
</template>

<script setup>
import {defineProps, inject, onMounted, ref} from 'vue';
import Quiz from './Quiz.vue';
import QuizEnd from './QuizEnd.vue';
import RespondentIdentification from './RespondentIdentification.vue';
import {useQuizStatusBarStore} from '../../stores/QuizStatusBarStore.js';
import ProgressBar from "./ProgressBar.vue";

const EventBus = inject('EventBus');

const props = defineProps({
    token: {
        type: String,
        required: true,
    },
    settings: {
        type: Object,
        required: true,
    },
});

const quizStatusBarStore = useQuizStatusBarStore();

const showQuiz = ref(true);
const showQuizEnd = ref(false);
const showRespondentIdentification = ref(false);

const onQuizFinished = () => {
    showQuiz.value = false;
    showRespondentIdentification.value = true;
};

const onRespondentIdentificationFinished = () => {
    showQuizEnd.value = true;
    showRespondentIdentification.value = false;
};

onMounted(async () => {
    quizStatusBarStore.setMaxQuestions(props.settings.maxQuestions);

    EventBus.on('quiz:finished', () => onQuizFinished());
    EventBus.on('respondentIdentification:finished', () => onRespondentIdentificationFinished());
});
</script>
