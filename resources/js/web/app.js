import {createApp} from 'vue';
import {createPinia} from 'pinia';
import mitt from 'mitt';

import i18n from './i18n';

import.meta.glob(['../../images/**']);

import ButtonBlue from './components/ButtonBlue.vue';
import IconArrowRight from './components/Icons/IconArrowRight.vue';
import IslandScene from './components/IslandGame/IslandScene.vue';
import QuizForm from './components/Quiz/QuizForm.vue';
import QuestionsTiles from "./components/QuizGrid/QuestionsTiles.vue";

const pinia = createPinia();
const app = createApp({});
const EventBus = mitt();

app.use(pinia);
app.use(i18n);

app.provide('EventBus', EventBus);

app.component('ButtonBlue', ButtonBlue)
	.component('IconArrowRight', IconArrowRight)
	.component('IslandScene', IslandScene)
	.component('QuizForm', QuizForm)
	.component('QuestionsTiles', QuestionsTiles);

app.mount('#app-web');
