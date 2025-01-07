<template>
    <button
        class="my-3 disabled:cursor-not-allowed lg:text-lg inline-flex items-center px-5 py-2.5 text-sm font-medium text-left rounded-lg text-white"
        :class="{
                    'bg-blue-700 hover:bg-blue-800': !isSelected && !questionStore.isAnswered,
                    'bg-gray-400': disabled || (!isSelected && questionStore.isAnswered),
                    'bg-emerald-600': isSelected && isRight && questionStore.isAnswered,
                    'bg-rose-600': isSelected && !isRight && questionStore.isAnswered,
                    '': isSelected, // TODO kdyz bude klikat na jine
                 }"
        :disabled="disabled"
        @click="handleClick">
        <div class="flex">
            <div v-if="quizSettingsStore.showEvaluationsForOtherOptions" :class="{'text-white':isSelected}">
                <icon-hand-thumb-up
                    v-if="isRight && questionStore.isAnswered"
                    class="h-5 w-5 text-emerald-600 mr-2"
                    :class="{'text-white':isSelected}"
                />
                <icon-hand-raised
                    v-if="!isRight && questionStore.isAnswered"
                    class="h-5 w-5 text-rose-600 mr-2"
                    :class="{'text-white':isSelected}"
                />
            </div>
            <div class="text-white">{{ option.name }}</div>
        </div>
    </button>
</template>

<script setup>
import {computed, defineProps, inject} from 'vue';
import {useOptionEvaluationStore} from '../../stores/OptionEvalutationStore.js';
import {useQuestionStore} from '../../stores/QuestionStore.js';
import {useQuizSettingsStore} from '../../stores/QuizSettingsStore.js';
import IconHandRaised from '../Icons/IconHandRaised.vue';
import IconHandThumbUp from '../Icons/IconHandThumbUp.vue';

const props = defineProps({
    option: {
        type: Object,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const EventBus = inject('EventBus');
const optionEvaluationStore = useOptionEvaluationStore();
const questionStore = useQuestionStore();
const quizSettingsStore = useQuizSettingsStore();

const isSelected = computed(() => questionStore.selectedOptionId === props.option.id);
const isRight = computed(() => props.option?.evaluation?.rightAnswer);

const handleClick = () => {
    // TODO kdyz jiz jednou klikl, tak at se nevyvola emit, aby se znovu odeslala odpoved
    questionStore.selectedOptionId = props.option.id;

    EventBus.emit('option:clicked', props.option.id);
};
</script>
