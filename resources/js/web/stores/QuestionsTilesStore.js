import {defineStore} from 'pinia';
import {loadQuestion, storeRespondentAnswer} from '../services/QuizAPI.js';
import moment from 'moment/moment.js';

const maxTiles = 16;
const storeName = 'questionsTilesStore';
const version = 2;

export const useQuestionsTilesStore = defineStore(storeName, {
	state: () => ({
		beingEvaluated: false,
		evaluated: false,
		evaluationLoading: false,
		loadedAt: null,
		question: null,
		questionLoading: false,
		questionLoaded: false,
		questionsLoaded: 0,
		lastSelectedTile: null,
		isLastAnsweredQuestion: false,
		tiles: Array.from({length: maxTiles}, (_, i) => ({
			id: i + 1,
			answered: false,
			answeredRight: false,
			answeredWrong: false,
		}))
	}),
	getters: {
		allTilesAreUncovered: (state) => state.tiles.every(tile => tile.answered && tile.answeredRight),
		getTileById: (state) => (id) => state.tiles[id - 1],
		hasSelectedOptions: (state) => state.question.options.filter(option => option.selected === true).length > 0,
		getTotalRightOptions: (state) => state.question.options.filter(option => option.evaluation.rightAnswer).length,
		getTotalSelectedRightOptions: (state) => state.question.options.filter(option => option.selected && option.evaluation.rightAnswer).length,
		getMultiselectSummary: (state) => {
			if (state.question.type !== 'multiselect') {
				return null;
			}

			if (!state.question.settings?.multiselectSummary) {
				return null;
			}

			return state.question.settings?.multiselectSummary
				.replace(':totalRight', state.getTotalRightOptions)
				.replace(':totalSelected', state.getTotalSelectedRightOptions);
		}
	},
	actions: {
		handleClickToOption(id, selected) {
			if (this.question.type === 'select') {
				this.question.options.map(option => option.selected = false);
			}

			const option = this.question.options.find(option => option.id === id);
			option.selected = selected;
		},

		async loadQuestion() {
			this.beingEvaluated = false;
			this.evaluated = false;
			this.evaluationLoading = false;
			this.questionLoading = true;

			return loadQuestion(version)
				.then(question => {
					this.question = question;
					this.questionLoaded = true;
					this.questionLoading = false;
					this.questionsLoaded += 1;
					this.loadedAt = moment();
				});
		},

		async evaluateQuestion() {
			this.evaluationLoading = true;
			this.question.answered = true;

			const optionIds = this.question.options
				.filter(option => option.selected === true)
				.map(option => option.id);
			const seconds = moment().diff(this.loadedAt, 'seconds');

			storeRespondentAnswer(this.question.id, optionIds, seconds)
				.then(answer => {
					this.evaluationLoading = false;
					this.evaluated = true;

					this.isLastAnsweredQuestion = answer.end;

					answer.evaluations.forEach(evaluation => {
						this.question.options.filter(option => option.id === evaluation.optionId).map(option => {
							option.evaluation = evaluation;
						});
					});

					const rightAnsweredOptionCount = this.question.options.filter(
						option => option.selected && option.evaluation && option.evaluation.rightAnswer
					).length;

					const lastSelectedTile = this.tiles.find(tile => tile.id === this.lastSelectedTile);
					lastSelectedTile.answered = true;
					lastSelectedTile.answeredRight = rightAnsweredOptionCount > 0;
					lastSelectedTile.answeredWrong = rightAnsweredOptionCount === 0;
				});
		}
	},
});
