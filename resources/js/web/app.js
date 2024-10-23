import {createApp} from 'vue';
import {createPinia} from 'pinia';

import IconArrowRight from './components/Icons/IconArrowRight.vue';
import Quiz from './components/Quiz.vue';

const pinia = createPinia();
const app = createApp({});

app.use(pinia);

app
	.component('IconArrowRight', IconArrowRight)
	.component('Quiz', Quiz);

app.mount('#app-web');
