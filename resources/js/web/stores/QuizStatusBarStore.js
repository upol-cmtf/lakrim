import {defineStore} from 'pinia';
import {useQuizSettingsStore} from './QuizSettingsStore.js';

const storeName = 'quizStatusBarStore';

const defaultState = {
	loadedQuestions: 0,
	rightAnswers: 0,
	wrongAnswers: 0,
	topics: [],
};

export const useQuizStatusBarStore = defineStore(storeName, {
	state: () => ({...defaultState}),
	getters: {
		maxQuestions() {
			return useQuizSettingsStore().maxQuestions;
		},
	},
	actions: {
		incrementLoadedQuestions() {
			this.loadedQuestions++;
		},
		incrementRightAnswers() {
			this.rightAnswers++;
		},
		incrementWrongAnswers() {
			this.wrongAnswers++;
		},
		addTopic(id, name) {
			this.topics.push({id: id, name: name, state: 0});
		},
		setLastTopicState(state) {
			this.topics[this.topics.length - 1].state = state;
		},
		getTopics() {
			return this.topics;
		},
		getAnsweredQuestionsCount() {
			return this.rightAnswers + this.wrongAnswers;
		},
		getTopicByIndex(index) {
			return this.topics[index];
		},
		getPercentComplete() {
			return Math.ceil((this.getAnsweredQuestionsCount() * 100) / this.maxQuestions) + '%';
		}
	},
});
