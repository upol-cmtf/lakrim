<template>
    QUIZ COMPONENT
</template>

<script setup>
import {defineProps} from 'vue';
import {onMounted} from 'vue';
import {useRespondentTokenStore} from '../stores/RespondentTokenStore.js';
import {loadQuestion} from '../services/QuizAPI.js';

const respondentTokenStore = useRespondentTokenStore();

const props = defineProps({
    uuid: {
        type: String,
        required: true,
    },
});

onMounted(async () => {
    respondentTokenStore.setToken(props.uuid);

    await loadQuestion();
});
</script>
