import {defineStore} from 'pinia';

export const useQuizStatusBarStore = defineStore('quizStatusBarStore', {
	state: () => ({
		loadedQuestions: 0,
		maxQuestions: 0,
		rightAnswers: 0,
		wrongAnswers: 0,
		topics: [],
	}),
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
		setMaxQuestions(maxQuestions) {
			this.maxQuestions = maxQuestions;
		},
		getMaxQuestions() {
			return this.maxQuestions;
		},
		getLoadedQuestions() {
			return this.loadedQuestions;
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
			return Math.ceil((this.getAnsweredQuestionsCount() * 100) / this.getMaxQuestions()) + '%';
		}
	},
});
