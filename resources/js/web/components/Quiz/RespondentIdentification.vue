<template>
    <div class="p-8">
        <p class="lg:text-3xl text-center mb-3">
            Skvělá práce! Už jste skoro u konce. Ještě prosím zaklikněte své pohlaví a věk, a hned přejdeme k výsledkům.
        </p>

        <div class="md:text-lg lg:text-xl">
            <h3 class="font-bold mb-3 text-3xl">1. Pohlaví</h3>

            <div class="p-8">
                <div class="max-w-screen-lg mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-8">
                    <respondent-sex-item icon="/images/nezvole-ico@2x.png">Nezadáno</respondent-sex-item>
                    <respondent-sex-item v-for="sex in sexList" :icon="sex.img" :value="sex.id">
                        {{ sex.name }}
                    </respondent-sex-item>
                </div>
            </div>

            <h3 class="font-bold mb-3 text-3xl">2. Věk</h3>
            <div class="p-8">
                <div class="max-w-screen-lg mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-8">
                    <respondent-age-item :value="0" name="nezvole-ico">Nezadáno</respondent-age-item>
                    <respondent-age-item v-for="age in ageList" :name="age.name" :value="age.id">
                        {{ age.name }}
                    </respondent-age-item>
                </div>
            </div>
        </div>

        <div class="text-right">
            <button-blue-with-arrow-right @click="submitRespondentIdentification">
                Přejít na závěrečné vyhodnocení
            </button-blue-with-arrow-right>
        </div>
    </div>
</template>

<script setup>
import {loadAgeList, storeRespondentIdentification} from '../../services/QuizAPI.js';
import {inject, onMounted, ref} from 'vue';
import {useRespondentIdentificationStore} from '../../stores/RespondentIdentificationStore.js';
import ButtonBlueWithArrowRight from '../ButtonBlueWithArrowRight.vue';
import RespondentSexItem from './RespondentSexItem.vue';
import RespondentAgeItem from './RespondentAgeItem.vue';

const EventBus = inject('EventBus');

const ageList = ref([]);

const respondentIdentificationStore = useRespondentIdentificationStore();

const getAgeList = async () => {
    ageList.value = await loadAgeList();
};

const sexList = [
    {id: 'M', name: 'Muž', checked: false, img: '/images/man@2x.png'},
    {id: 'F', name: 'Žena', checked: false, img: '/images/woman@2x.png'},
];

const submitRespondentIdentification = async () => {
    await storeRespondentIdentification(
        respondentIdentificationStore.sex || null,
        respondentIdentificationStore.age_id || null,
    );

    EventBus.emit('respondentIdentification:finished');
};

onMounted(async () => {
    await getAgeList();
    window.scroll(0, 0)
});
</script>
