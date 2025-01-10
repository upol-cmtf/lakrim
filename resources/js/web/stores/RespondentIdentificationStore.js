import {defineStore} from 'pinia';

const storeName = 'respondentIdentificationStore';

const defaultState = {
	age_id: 0,
	sex: null,
};

export const useRespondentIdentificationStore = defineStore(storeName, {
	state: () => ({...defaultState}),
});
