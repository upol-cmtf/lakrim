import {defineStore} from 'pinia';

const storeName = 'quizSettingsStore';

const defaultState = {
	maxQuestions: 0,
	showEvaluationsForOtherOptions: 0,
};

export const useQuizSettingsStore = defineStore(storeName, {
	state: () => ({...defaultState}),
});
