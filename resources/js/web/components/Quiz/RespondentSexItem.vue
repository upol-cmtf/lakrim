<template>
    <div class="rounded-lg overflow-hidden shadow-md"
         :class="{'bg-emerald-600 text-white': selected, 'bg-white': !selected}">
        <div class="px-4 py-6 text-center font-semibold relative" @click="handleClick">
            <label for="sex-0" class="ml-2 cursor-pointer after:absolute after:inset-0">
                <div class="hidden md:flex justify-center items-center mb-4">
                    <img class="object-center object-contain h-14 w-14"
                         :src="icon"
                         alt=""/>
                </div>
                <input class="sr-only peer"
                       type="radio"
                       :value="value"
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
    icon: {
        type: String,
        required: true,
    },
    value: {
        type: String,
        required: false,
        default: null,
    },
});

const handleClick = () => {
    respondentIdentificationStore.sex = props.value;
};

const selected = computed(() => {
    return props.value === respondentIdentificationStore.sex;
});
</script>
