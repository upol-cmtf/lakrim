<template>
    <div class="mt-5">
        <p class="font-bold"
            v-if="multiselectSummary"
            v-html="multiselectSummary">
        </p>
        <h1
            class="text-2xl font-bold my-5"
            :class="{
                'text-emerald-600': evaluation?.evaluation.rightAnswer,
                'text-rose-600': !evaluation?.evaluation.rightAnswer,
            }"
            v-html="evaluation?.evaluation.evaluationTitle">
        </h1>
        <div class="text-xl" v-html="evaluation?.evaluation.evaluation"></div>
    </div>
</template>

<script setup>
import {useQuestionsTilesStore} from '../../stores/QuestionsTilesStore.js';
import {computed} from "vue";

const store = useQuestionsTilesStore();

const evaluation = computed(() => store.question.options
    .find(option => option.evaluation && option.selected));

const multiselectSummary = computed(() => store.getMultiselectSummary);
</script>
