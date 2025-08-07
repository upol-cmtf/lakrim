import {createApp} from 'vue';
import {createPinia} from 'pinia';
import mitt from 'mitt';

import ButtonBlue from './components/ButtonBlue.vue';
import IconArrowRight from './components/Icons/IconArrowRight.vue';
import QuizForm from './components/Quiz/QuizForm.vue';
import QuestionsTiles from "./components/QuizGrid/QuestionsTiles.vue";

const pinia = createPinia();
const app = createApp({});
const EventBus = mitt();

app.use(pinia);

app.provide('EventBus', EventBus);

app.component('ButtonBlue', ButtonBlue)
	.component('IconArrowRight', IconArrowRight)
	.component('QuizForm', QuizForm)
	.component('QuestionsTiles', QuestionsTiles);

app.mount('#app-web');
