import { defineStore } from 'pinia';

export const useRespondentTokenStore = defineStore('respondentTokenStore', {
	state: () => ({
		token: null,
	}),
	actions: {
		getToken() {
			return this.token;
		},
		setToken(token) {
			this.token = token;
		}
	},
});
