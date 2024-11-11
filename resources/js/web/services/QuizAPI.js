import axios from 'axios';
import {useRespondentTokenStore} from '../stores/RespondentTokenStore.js';

function loadAgeList() {
	const endpoint = '/age-list';

	return new Promise(function (resolve, reject) {
		axios.get(
			buildEndpointUrl(endpoint, {}),
			requestConfig(),
		)
			.then(response => {
				resolve(response.data.data);
			})
			.catch(error => {
				reject(error);
			});
	});
}

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
				resolve(response.data.data);
			})
			.catch(error => {
				reject(error);
			});
	});
}

function storeRespondentAnswer(questionId, optionId, seconds) {
	const endpoint = '/answer';
	const respondentTokenStore = useRespondentTokenStore();

	return new Promise(function (resolve, reject) {
		axios.post(
			buildEndpointUrl(endpoint, {}),
			{
				question_id: questionId,
				option_id: optionId,
				respondent_token: respondentTokenStore.getToken(),
				seconds: seconds,
			},
			requestConfig(),
		)
			.then(response => {
				resolve(response.data.data);
			})
			.catch(error => {
				reject(error);
			});
	});
}

function storeRespondentIdentification(sex, ageId) {
	const endpoint = '/respondent/identification';
	const respondentTokenStore = useRespondentTokenStore();

	return new Promise(function (resolve, reject) {
		axios.post(
			buildEndpointUrl(endpoint, {}),
			{
				sex: sex,
				age_id: ageId,
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

function getRespondentSummary() {
	const endpoint = '/respondent/summary';
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
				resolve(response.data.data);
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
	loadAgeList,
	loadQuestion,
	getRespondentSummary,
	storeRespondentAnswer,
	storeRespondentIdentification,
};
