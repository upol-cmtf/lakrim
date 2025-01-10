import {defineStore} from 'pinia';
import {loadQuestion} from '../services/QuizAPI.js';
import {useQuizStatusBarStore} from './QuizStatusBarStore.js';

import moment from 'moment';

const storeName = 'questionStore';

const defaultState = {
	id: null,
	perex: null,
	description: null,
	options: [],
	group: null,
	loadedAt: null,
	answeredOptionId: null,
	selectedOptionId: null,
	isLoading: false,
	isLoaded: false,
	isLoadingEvaluation: false,
	isEvaluationLoaded: false,
	isAnswerStored: false,
};

export const useQuestionStore = defineStore(storeName, {
	state: () => ({...defaultState}),
	getters: {
		isAnswered() {
			return this.answeredOptionId !== null;
		}
	},
	actions: {
		getEvaluationByOptionId(optionId) {
			return this.options.find(option => option.id === optionId).evaluation;
		},
		setQuestion(question) {
			this.id = question.id;
			this.perex = question.perex;
			this.description = question.description;
			this.options = question.options;
			this.group = question.group;
			this.loadedAt = moment();
		},
		setEvaluations(evaluations) {
			evaluations.forEach(evaluation => {
				this.options.find(option => option.id === evaluation.optionId).evaluation = evaluation;
			});
		},
		setIsLoaded() {
			this.isLoading = false;
			this.isLoaded = true;
		},
		setIsEvaluationLoaded() {
			this.isLoadingEvaluation = false;
			this.isEvaluationLoaded = true;
		},
		async loadQuestion() {
			this.reset();

			this.isLoading = true;

			return loadQuestion()
				.then(question => {
					const quizStatusBarStore = useQuizStatusBarStore();
					quizStatusBarStore.incrementLoadedQuestions();
					quizStatusBarStore.addTopic(question.group.id, question.group.name);

					this.setQuestion(question);
					this.setIsLoaded();
				});
		},
		reset() {
			Object.assign(this, defaultState);
		}
	},
});
