<template>
    <div>
        <button
            class="my-3 disabled:cursor-not-allowed lg:text-lg inline-flex items-center px-4 py-2.5 text-sm font-medium text-left rounded-lg text-white"
            :class="{
                    'bg-blue-700 hover:bg-blue-800': !questionStore.isAnswered,
                    'bg-gray-400': disabled || (!isAnswered && questionStore.isAnswered),
                    'hover:bg-gray-700': !isAnswered && questionStore.isAnswered && quizSettingsStore.showEvaluationsForOtherOptions,
                    'bg-gray-700': isSelected && quizSettingsStore.showEvaluationsForOtherOptions && !isAnswered,
                    'bg-emerald-600': isAnswered && isRight && questionStore.isEvaluationLoaded,
                    'bg-rose-600': isAnswered && !isRight && questionStore.isEvaluationLoaded,
                    'cursor-no-drop	': !isAnswered && !quizSettingsStore.showEvaluationsForOtherOptions,
            }"
            :disabled="disabled"
            @click="handleClick">
            <div class="flex">
                <div class="text-white">{{ option.name }}</div>
                <div v-if="quizSettingsStore.showEvaluationsForOtherOptions && questionStore.isAnswered && !isAnswered"
                     class="align-middle"
                     :class="{'text-white': isAnswered}"
                >
                    <icon-hand-thumb-up
                        v-if="isRight"
                        class="h-7 w-7 text-emerald-600 ml-2"
                        :class="{'text-white': isAnswered}"
                    />
                    <icon-hand-raised
                        v-if="!isRight"
                        class="h-7 w-7 text-rose-600 ml-2"
                        :class="{'text-white': isAnswered}"
                    />
                </div>
            </div>
        </button>
    </div>
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

const isAnswered = computed(() => questionStore.answeredOptionId === props.option.id);
const isSelected = computed(() => questionStore.selectedOptionId === props.option.id);
const isRight = computed(() => props.option?.evaluation?.rightAnswer);

const handleClick = () => {
    EventBus.emit('option:clicked', props.option.id);
};
</script>
