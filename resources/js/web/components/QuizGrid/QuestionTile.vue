<template>
    <div class="p-4 aspect-square w-20 md:w-32 text-center justify-center flex items-center"
         :class="{
            'bg-white hover:scale-105 transform transition-transform duration-300 hover:z-50 hover:outline-gray-600 hover:bg-cyan-600 hover:text-white hover:text-extrabold': !tile?.answered,
            'cursor-pointer outline outline-1 outline-gray-300': !tile?.answered,
         }"
         @click="handleClick(tile)"
         @mouseenter="hovered = true"
         @mouseleave="hovered = false"
    >
        <span v-if="!tile?.answered && !hovered" v-html="tileId"></span>
        <span v-if="!tile?.answered && hovered">Jdeme na to</span>
    </div>
</template>

<script async setup>
import {computed, defineProps, inject, ref} from 'vue';
import {useQuestionsTilesStore} from '../../stores/QuestionsTilesStore.js';

const props = defineProps({
    tileId: {
        type: Number,
        required: true,
    },
})

const EventBus = inject('EventBus');

const hovered = ref(false);
const store = useQuestionsTilesStore();
const tile = computed(() => store.getTileById(props.tileId))
const handleClick = (tile) => {
    if (!(tile?.answered)) {
        store.lastSelectedTile = props.tileId;
        EventBus.emit('tile:clicked');
    }
};
</script>
