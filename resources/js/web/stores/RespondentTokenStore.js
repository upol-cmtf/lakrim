import { defineStore } from 'pinia';

const initialState = {
	token: null,
};

export const useRespondentTokenStore = defineStore('respondentTokenStore', {
	state: () => ({...initialState}),
	actions: {
		getToken() {
			return this.token;
		},
		setToken(token) {
			this.token = token;
		}
	},
});
