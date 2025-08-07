<template>
    <div>
        <div v-if="showTiles" id="questions-grid" class="grid grid-cols-4 mt-5">
            <question-tile
                v-for="n in 16"
                :tile-id="n"
                :key="n"
            />
        </div>

        <div v-if="showQuestionDetailModal" class="fixed inset-0 bg-white flex items-center justify-center">
            <icon-loading v-if="store.questionLoading"/>

            <div v-if="store.questionLoaded" class="bg-white w-full h-full overflow-y-auto">
                <question-detail/>
            </div>
        </div>

        <div v-if="showFinalVideo">
            <div class="flex items-center justify-center max-w-2xl">
                <video controls autoplay>
                    <source src="/videos/policie.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <div class="flex justify-end mt-10 sticky bottom-4 right-4">
                <button-blue-with-arrow-right
                    @click="moveToCompletion"
                >
                    Přejít na dokončení
                </button-blue-with-arrow-right>
            </div>
        </div>

        <respondent-identification
            v-if="showRespondentIdentification"
            class="p-4"
        />

        <quiz-end
            v-if="showQuizEnd"
            class="p-4"
            :completion-url="completionUrl"
            :show-progress-bar="false"
        />
    </div>

</template>

<script setup>
import {inject, onMounted, ref} from 'vue';
import {useQuestionsTilesStore} from '../../stores/QuestionsTilesStore.js';
import {useRespondentTokenStore} from '../../stores/RespondentTokenStore.js';
import ButtonBlueWithArrowRight from '../ButtonBlueWithArrowRight.vue';
import IconLoading from '../Icons/IconLoading.vue';
import QuestionDetail from './QuestionDetail.vue';
import QuestionTile from './QuestionTile.vue';
import QuizEnd from '../Quiz/QuizEnd.vue';
import RespondentIdentification from '../Quiz/RespondentIdentification.vue';

const props = defineProps({
    completionUrl: {
        type: String,
        required: true,
    },
    respondentToken: {
        type: String,
        required: true,
    }
});

const EventBus = inject('EventBus');

const showTiles = ref(true);
const showQuestionDetailModal = ref(false);
const showFinalVideo = ref(false);
const showRespondentIdentification = ref(false);
const showQuizEnd = ref(false);
const store = useQuestionsTilesStore();
const respondentTokenStore = useRespondentTokenStore();
const handleTileClicked = () => {
    store.loadQuestion();

    showTiles.value = false;
    showQuestionDetailModal.value = true;
};

const hideQuestionDetailModal = () => {
    showTiles.value = true;
    showQuestionDetailModal.value = false;
};

const moveToCompletion = () => {
    showRespondentIdentification.value = true;
    showFinalVideo.value = false;
};

onMounted(() => {
    respondentTokenStore.token = props.respondentToken;

    EventBus.on('tile:clicked', () => handleTileClicked());
    EventBus.on('close-question-detail', () => hideQuestionDetailModal());
    EventBus.on('respondentIdentification:finished', () => {
        showRespondentIdentification.value = false;
        showQuizEnd.value = true;
    });
    EventBus.on('show-final-video', () => {
        showFinalVideo.value = true;
        showQuestionDetailModal.value = false;
    });
});
</script>
