<template>
    <div class="p-5">
        <p class="font-bold">Pro spuštění kvízu vyplňte své studijní číslo.</p>
        <div class="flex items-center mt-5">
            <input
                type="text"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"
                :class="{
                    'border-red-500 text-red-500': isInputError,
                    'border-gray-300 text-gray-900': !isInputError
                }"
                placeholder="Studijní číslo CXXXXX"
                v-model="studentId"
                :disabled="storingStudentId"
                required
            />
            <button
                type="submit"
                class="ml-2 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center"
                @click="handleClick"
                :disabled="storingStudentId"
            >
                Uložit
            </button>
        </div>
    </div>
</template>

<script setup>
    import {computed, defineModel, inject, ref} from 'vue';
    import {storeStudentId} from '../../services/QuizAPI.js';

    const EventBus = inject('EventBus');

    const studentId = defineModel();
    const storingStudentId = ref(false);

    const isInputError = ref(false);

    const handleClick = async () => {
        isInputError.value = false;

        if (!studentId.value) {
            isInputError.value = true;
            return;
        }

        if(!/C[0-9]/.test(studentId.value)) {
            isInputError.value = true;
            return;
        }

        await storeStudentId(studentId.value);
        EventBus.emit('studentId:stored');
    };
</script>
