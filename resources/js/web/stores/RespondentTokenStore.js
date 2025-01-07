import {defineStore} from 'pinia';

const storeName = 'respondentTokenStore';

const defaultState = {
	token: null,
};
export const useRespondentTokenStore = defineStore(storeName, {
	state: () => ({...defaultState}),
});
