<template>
    <div class="flex items-center gap-x-1 p-5 bg-white shadow-md md:rounded-b-lg border border-slate-200">
        <progress-bar-item v-for="i in quizStatusBarStore.maxQuestions" :step="i"/>

        <div>
            <div class="w-10 text-end">
                <span class="text-sm text-gray-800 dark:text-white font-semibold">
                    {{ quizStatusBarStore.loadedQuestions }}/{{ quizStatusBarStore.maxQuestions }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import {useQuizStatusBarStore} from '../../stores/QuizStatusBarStore.js';
import ProgressBarItem from './ProgressBarItem.vue';

const quizStatusBarStore = useQuizStatusBarStore();
</script>
