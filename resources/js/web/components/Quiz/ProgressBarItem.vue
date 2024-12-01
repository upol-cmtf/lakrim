<template>
    <div
        class="w-full h-2.5 flex flex-col justify-center overflow-hidden text-xs text-white text-center whitespace-nowrap transition duration-500 dark:bg-neutral-600"
        :class="{'bg-gray-300': showDefaultColor, 'bg-emerald-600': !showDefaultColor && isAnsweredRight, 'bg-rose-600': !showDefaultColor && !isAnsweredRight}"
        role="progressbar"
        aria-valuenow="25"
        aria-valuemin="0"
        aria-valuemax="100">
    </div>
</template>

<script setup>
import {computed, defineProps} from 'vue';
import {useQuizStatusBarStore} from '../../stores/QuizStatusBarStore.js';

const props = defineProps({
    step: {
        type: Number,
        required: true,
    },
});

const quizStatusBarStore = useQuizStatusBarStore();

const showDefaultColor = computed(() => {
    return props.step > quizStatusBarStore.getAnsweredQuestionsCount();
});

const isAnsweredRight = computed(() => {
    let topic = quizStatusBarStore.getTopicByIndex(props.step - 1);
    return (topic !== undefined) && (topic.state === 1);
});
</script>
