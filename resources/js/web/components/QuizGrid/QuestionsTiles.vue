<template>
    <div class="flex flex-col items-center p-2">
        <template v-if="showStudentIdForm">
            <student-id-form/>
        </template>

        <template v-if="showTiles">
            <h1 class="max-w-4xl mb-3 text-2xl font-bold leading-8 tracking-tight text-center mt-5">
                Je tu pro Vás připraveno několik příběhů. Každý se ukrývá pod jedním číslem. Záleží jen na Vás, kterým začnete. <br/>Co se stane, až je odhalíte všechny?
            </h1>

            <div id="questions-grid" class="grid grid-cols-4 mt-5">
                <question-tile
                    v-for="n in 16"
                    :tile-id="n"
                    :key="n"
                />
            </div>
        </template>

        <template v-if="showQuestionDetailModal">
            <div class="fixed inset-0 bg-white flex items-center justify-center">
                <icon-loading v-if="store.questionLoading"/>

                <div v-if="store.questionLoaded" class="bg-white w-full h-full overflow-y-auto">
                    <question-detail/>
                </div>
            </div>
        </template>

        <template v-if="showFinalVideo">
            <h1 class="max-w-4xl mb-8 text-2xl font-bold leading-8 tracking-tight text-center mt-5">
                Jen ten, kdo na sobě pracuje, je připravený se bránit.<br/>
                Jen díky lidem jako vy je naše práce snazší.<br/>
                Buďte na sebe pyšní – právě jste udělali velký krok pro vaši bezpečnost.
            </h1>

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
        </template>

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
    import {computed, inject, onMounted, ref} from 'vue';
import {useQuestionsTilesStore} from '../../stores/QuestionsTilesStore.js';
import {useRespondentTokenStore} from '../../stores/RespondentTokenStore.js';
import ButtonBlueWithArrowRight from '../ButtonBlueWithArrowRight.vue';
import IconLoading from '../Icons/IconLoading.vue';
import QuestionDetail from './QuestionDetail.vue';
import QuestionTile from './QuestionTile.vue';
import QuizEnd from '../Quiz/QuizEnd.vue';
import RespondentIdentification from '../Quiz/RespondentIdentification.vue';
    import StudentIdForm from '../Quiz/StudentIdForm.vue';

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

const isFilledStudentId = ref(false);
const showFinalVideo = ref(false);
const showQuestionDetailModal = ref(false);
const showQuizEnd = ref(false);
const showRespondentIdentification = ref(false);
const showTiles = ref(false);

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

const showStudentIdForm = computed(() => {
    return !isFilledStudentId.value;
});

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
    EventBus.on('studentId:stored', () => {
        isFilledStudentId.value = true;
        showTiles.value = true;
    });
});
</script>
