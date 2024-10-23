import axios from 'axios';
import {useRespondentTokenStore} from '../stores/RespondentTokenStore.js';

function loadQuestion() {
	const endpoint = '/question';

	const respondentTokenStore = useRespondentTokenStore();

	return new Promise(function (resolve, reject) {
		axios.post(
			buildEndpointUrl(endpoint, {}),
			{
				respondent_token: respondentTokenStore.getToken(),
			},
			requestConfig(),
		)
			.then(response => {
				resolve(response);
			})
			.catch(error => {
				reject(error);
			});
	});
}

function requestConfig() {
	return {
		headers: {
			'Content-Type': 'application/json',
			'Accept': 'application/json',
		}
	};
}

function buildEndpointUrl(endpoint) {
	return import.meta.env.VITE_QUIZ_API_URL.trimEnd('/') + endpoint;
}

export {
	loadQuestion,
};
