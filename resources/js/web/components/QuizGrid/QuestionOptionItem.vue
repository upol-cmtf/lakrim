<template>
    <div>
        <button
            class="my-2 disabled:cursor-not-allowed lg:text-lg inline-flex items-center px-4 py-1.5 text-base font-medium text-left rounded-md"
            :class="{
                    'bg-slate-50 outline outline-1 outline-gray-300 hover:outline-gray-600 text-slate-800': !store.evaluationLoading && (!question?.answered && !option?.selected && !store.evaluated) || (store.evaluated && !option?.selected && store.answerWrong && store.answerAttempt<=1),
                    'bg-sky-800 text-white': !store.evaluationLoading && option?.selected && !store.evaluated,
                    'bg-emerald-600 text-white': !store.evaluationLoading && store.evaluated && option?.selected && option?.evaluation?.rightAnswer,
                    'bg-rose-600 text-white': !store.evaluationLoading && store.evaluated && option?.selected && !option?.evaluation?.rightAnswer,
                    'bg-red-100 outline outline-1 outline-gray-300 text-slate-600': !store.evaluationLoading && (store.answerRight || store.answerAttempt === 2) && store.evaluated && !option?.selected && !option?.evaluation?.rightAnswer,
                    'bg-green-100 outline outline-1 outline-gray-300 text-slate-600': !store.evaluationLoading && (store.answerRight || store.answerAttempt === 2) && store.evaluated && !option?.selected && option?.evaluation?.rightAnswer,
                    'bg-gray-200 text-slate-600 outline outline-1 outline-gray-300': store.evaluationLoading,

            }"
            :disabled="store.beingEvaluated"
            @click="handleClick(option)">

            <div class="flex items-center justify-center">
                <icon-check class="h-5 w-5 mr-2" v-if="option?.selected"/>
                <div>{{ option?.name }} {{ option?.description }}</div>
            </div>
        </button>
    </div>
</template>

<script setup>
import {computed, defineProps} from 'vue';
import {useQuestionsTilesStore} from '../../stores/QuestionsTilesStore.js';
import IconCheck from '../Icons/IconCheck.vue';

const props = defineProps({
    optionId: {
        type: Number,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const store = useQuestionsTilesStore();

const question = computed(() => store.question);
const option = computed(() => question.value?.options.find(o => o.id === props.optionId));

const handleClick = (option) => {
    if (!store.evaluated) {
        const selected = !option.selected;
        store.handleClickToOption(option.id, selected);
    }
};
</script>
