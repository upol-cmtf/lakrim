<template>
    <div class="p-8">
        <p class="lg:text-3xl text-center mb-3">
            Skvělá práce! Už jste skoro u konce. Ještě prosím zaklikněte své pohlaví a věk, a hned přejdeme k výsledkům.
        </p>

        <div class="md:text-lg lg:text-xl">
            <h3 class="font-bold mb-3 text-3xl">1. Pohlaví</h3>

            <div class="p-8">
                <div class="max-w-screen-lg mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-8">
                    <div class="rounded-lg overflow-hidden shadow-md"
                         :class="{'bg-emerald-600 text-white': form.sex === null, 'bg-white': form.sex !== null}">
                        <div class="px-4 py-6 text-center font-semibold relative">
                            <label for="sex-0" class="ml-2 cursor-pointer after:absolute after:inset-0">
                                <div class="hidden md:flex justify-center items-center mb-4">
                                    <img class="object-center object-contain h-14 w-14"
                                         src="/images/nezvole-ico@2x.png"
                                         alt="Nezadáno"/>
                                </div>
                                <input class="sr-only peer"
                                       type="radio"
                                       v-model="form.sex"
                                       :value="null"
                                       :checked="true"
                                       id="sex-0">
                                Nezadáno
                            </label>
                        </div>
                    </div>

                    <template v-for="sex in sexList">
                        <div class="rounded-lg overflow-hidden shadow-md"
                             :class="{'bg-emerald-600 text-white': form.sex === sex.id, 'bg-white': form.sex !== sex.id}">
                            <div class="px-6 py-8 text-center font-semibold relative">
                                <label :for="`sex-${sex.id}`" class="ml-2 cursor-pointer after:absolute after:inset-0">
                                    <div class="hidden md:flex justify-center items-center mb-4">
                                        <img class="object-center object-contain h-14 w-14"
                                             :src="sex.img"
                                             :alt="sex.name"
                                        />
                                    </div>
                                    <input class="sr-only peer"
                                           type="radio"
                                           v-model="form.sex"
                                           :value="sex.id"
                                           :id="`sex-${sex.id}`">
                                    {{ sex.name }}
                                </label>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <h3 class="font-bold mb-3 text-3xl">2. Věk</h3>
            <div class="p-8">
                <div class="max-w-screen-lg mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-8">
                    <div class="rounded-lg overflow-hidden shadow-md"
                         :class="{'bg-emerald-600 text-white': form.age_id === null, 'bg-white': form.age_id !== null}">
                        <div class="px-6 py-8 text-center font-semibold relative">
                            <div class="hidden md:flex justify-center items-center mb-4">
                                <img class="object-center object-contain h-14 w-14"
                                     src="/images/nezvole-ico@2x.png"
                                     alt="Nezadáno"/>
                            </div>
                            <label for="age-0" class="ml-2 cursor-pointer after:absolute after:inset-0">
                                <input class="sr-only peer"
                                       type="radio"
                                       v-model="form.age_id"
                                       :value="null"
                                       :checked="true"
                                       id="age-0">
                                Nezadáno
                            </label>
                        </div>
                    </div>

                    <template v-for="age in ageList">
                        <div class="rounded-lg overflow-hidden shadow-md"
                             :class="{'bg-emerald-600 text-white': form.age_id === age.id, 'bg-white': form.age_id !== age.id}">
                            <div class="px-6 py-8 text-center font-semibold relative">
                                <div class="hidden md:flex justify-center items-center mb-4">
                                    <img class="object-center object-contain h-14 w-14"
                                         :src="`/images/`+age.name+`_years@2x.png`"
                                         :alt="age.name"/>
                                </div>
                                <label :for="`age-${age.id}`" class="ml-2 cursor-pointer after:absolute after:inset-0">
                                    <input class="sr-only peer"
                                           type="radio"
                                           v-model="form.age_id"
                                           :value="age.id"
                                           :id="`age-${age.id}`">
                                    {{ age.name }}
                                </label>
                            </div>
                        </div>
                    </template>
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
// import ButtonBlue from '../ButtonBlue.vue';
import ButtonBlueWithArrowRight from '../ButtonBlueWithArrowRight.vue';

const EventBus = inject('EventBus');

const ageList = ref([]);

const getAgeList = async () => {
    ageList.value = await loadAgeList();
};

const form = ref(
    {
        sex: null,
        age_id: null,
    },
);

const sexList = [
    {id: 'M', name: 'Muž', checked: false, img: '/images/man@2x.png'},
    {id: 'F', name: 'Žena', checked: false, img: '/images/woman@2x.png'},
];

const submitRespondentIdentification = async () => {
    await storeRespondentIdentification(form.value.sex, form.value.age_id);

    EventBus.emit('respondentIdentification:finished');
};

onMounted(async () => {
    await getAgeList();
    window.scroll(0, 0)
});
</script>
