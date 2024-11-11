<template>
    <div class="relative flex flex-col md:my-10 bg-white md:shadow-lg border border-slate-200 md:rounded-lg w-full md:h-min-[500px] p-5">
        <div class="p-4">
            <quiz v-if="showQuiz" :token="token"/>

            <respondent-identification v-if="showRespondentIdentification"/>

            <quiz-end v-if="showQuizEnd"/>
        </div>
    </div>
</template>

<script setup>
import {defineProps, inject, onMounted, ref} from 'vue';
import Quiz from './Quiz.vue';
import QuizEnd from './QuizEnd.vue';
import RespondentIdentification from './RespondentIdentification.vue';

const EventBus = inject('EventBus');

defineProps({
    token: {
        type: String,
        required: true,
    },
});

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
