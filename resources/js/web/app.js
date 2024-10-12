import { createApp } from 'vue';
import { appComponents } from './components.js';
import { createPinia } from 'pinia';

const pinia = createPinia();

const app = createApp({
	components: appComponents,
});

app.use(pinia);

app.mount('#app-web');
