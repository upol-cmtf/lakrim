<template>
    <button-blue>
        <slot/>
        <icon-arrow-right class="ml-3"></icon-arrow-right>
    </button-blue>
</template>

<script setup>
import ButtonBlue from './ButtonBlue.vue';
import IconArrowRight from './Icons/IconArrowRight.vue';
</script>
