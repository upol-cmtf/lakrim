<template>
    QUIZ COMPONENT
</template>

<script setup>
import { computed } from 'vue';
import { useRespondentTokenStore } from '../stores/RespondentTokenStore.js';

const respondentTokenStore = useRespondentTokenStore();

const props = defineProps({
    uuid: {
        type: String,
        required: true,
    },
});

computed(() => {
    respondentTokenStore.setToken(props.uuid);
});
</script>
