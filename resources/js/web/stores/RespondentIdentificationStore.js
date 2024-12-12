import {defineStore} from 'pinia';

const initialState = {
	age_id: 0,
	sex: null,
};

export const useRespondentIdentificationStore = defineStore('respondentIdentificationStore', {
	state: () => ({...initialState}),
});
