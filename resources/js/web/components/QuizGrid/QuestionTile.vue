<template>
    <div class="p-4 aspect-square w-20 md:w-32 text-center justify-center flex items-center"
         :class="{
            'bg-white hover:scale-105 transform transition-transform duration-300 hover:z-50 hover:outline-gray-600': !tile?.answered,
            'bg-red-300 blur-sm': tile?.answeredWrong,
            'cursor-pointer outline outline-1 outline-gray-300': !tile?.answered,
         }"
         @click="handleClick(tile)"
    >
        <span v-if="!tile?.answered" v-html="tileId"></span>
    </div>
</template>

<script async setup>
import {computed, defineProps, inject} from 'vue';
import {useQuestionsTilesStore} from '../../stores/QuestionsTilesStore.js';

const props = defineProps({
    tileId: {
        type: Number,
        required: true,
    },
})

const EventBus = inject('EventBus');

const store = useQuestionsTilesStore();
const tile = computed(() => store.getTileById(props.tileId))
const handleClick = (tile) => {
    // if (!(tile.answered && tile.answeredRight)) { // TODO doresit co se ma dit, kdyz odpovi spatne
    if (!(tile?.answered)) {
        store.lastSelectedTile = props.tileId;
        EventBus.emit('tile:clicked');
    }
};
</script>
