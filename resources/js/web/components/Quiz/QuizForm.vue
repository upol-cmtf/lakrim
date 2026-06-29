<template>
    <div>
        <progress-bar v-if="showQuiz" class="sticky top-0 z-50"/>

        <div
            class="flex flex-col md:my-6 bg-white md:shadow-lg border border-slate-200 md:rounded-lg w-full md:h-min-[500px]">

            <quiz
                v-if="showQuiz"
            />

            <respondent-identification
                v-if="showRespondentIdentification"
                class="p-4"
            />

            <quiz-end
                v-if="showQuizEnd"
                class="p-4"
                :completion-url="completionUrl"
            />
        </div>
    </div>
</template>

<script setup>
import {defineProps, inject, onMounted, ref} from 'vue';
import {useRespondentTokenStore} from '../../stores/RespondentTokenStore.js';
import {useQuizSettingsStore} from '../../stores/QuizSettingsStore.js';
import ProgressBar from './ProgressBar.vue';
import Quiz from './Quiz.vue';
import QuizEnd from './QuizEnd.vue';
import RespondentIdentification from './RespondentIdentification.vue';

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
    completionUrl: {
        type: String,
        required: true,
    },
});

const quizSettingsStore = useQuizSettingsStore();
const respondentTokenStore = useRespondentTokenStore();

// token a nastavení musíme naplnit synchronně už v setupu – child Quiz se mountuje (a načítá první
// otázku přes respondent token) dřív než onMounted rodiče, takže v onMounted by token ještě chyběl
respondentTokenStore.token = props.token;
quizSettingsStore.maxQuestions = props.settings.maxQuestions;
quizSettingsStore.requireStudentId = props.settings.requireStudentId;
quizSettingsStore.showEvaluationsForOtherOptions = props.settings.showEvaluationsForOtherOptions;

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
    EventBus.on('quiz:finished', () => onQuizFinished());
    EventBus.on('respondentIdentification:finished', () => onRespondentIdentificationFinished());
});
</script>
