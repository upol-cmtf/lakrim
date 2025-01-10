import {defineStore} from 'pinia';

const storeName = 'optionEvaluationStore';

const defaultState = {
	rightAnswer: null,
	title: null,
	text: null,
};

export const useOptionEvaluationStore = defineStore(storeName, {
	state: () => ({...defaultState}),
	actions: {
		setOptionEvaluation(optionEvaluation) {
			this.rightAnswer = optionEvaluation.rightAnswer;
			this.text = optionEvaluation.evaluation;
			this.title = optionEvaluation.evaluationTitle;
		},
		reset() {
			Object.assign(this, defaultState);
		}
	},
});
