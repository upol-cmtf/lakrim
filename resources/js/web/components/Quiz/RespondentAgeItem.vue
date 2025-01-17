<template>
    <div class="rounded-lg overflow-hidden shadow-md"
         :class="{'bg-emerald-600 text-white': selected, 'bg-white': !selected}">
        <div class="px-6 py-8 text-center font-semibold relative" @click="handleClick">
            <label class="ml-2 cursor-pointer after:absolute after:inset-0">
                <input class="sr-only peer"
                       type="radio"
                       :checked="selected">
                <slot/>
            </label>
        </div>
    </div>
</template>

<script setup>
import {computed, defineProps} from 'vue';
import {useRespondentIdentificationStore} from '../../stores/RespondentIdentificationStore.js';

const respondentIdentificationStore = useRespondentIdentificationStore();

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    value: {
        type: Number,
        required: true,
    },
});

const handleClick = () => {
    respondentIdentificationStore.age_id = props.value;
};

const selected = computed(() => {
    return props.value === respondentIdentificationStore.age_id;
});
</script>
