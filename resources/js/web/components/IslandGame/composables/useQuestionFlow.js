import { computed, ref, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

// Rozehraná situace v modálu: načtení otázky pro tlačítko, odpočet času,
// vyhodnocení odpovědi (zelená / oranžová / červená), karta bezpečí, prohlížení
// už vyřešených kamenů a lightbox obrázků ze zadání.
export function useQuestionFlow(props, {
    selectedIsland,
    guideMessage,
    guideTone,
    guideAction,
    showSafetyCard,
    hideSafetyCard,
    collectedSafetyCards,
    close,
}) {
    const { t } = useI18n();

    const activeQuestion = ref(null);
    // stav rozehrané situace v modálu: 'live' = čeká na odpověď, 'retry' = po 1. špatném pokusu, 'finished' = vyhodnoceno (správně nebo 2. špatně)
    const questionStatus = ref('live');
    // prohlížení už vyřešeného kamene – jen čtení, nejde znovu odpovídat
    const reviewMode = ref(false);
    // při prohlížení: byl kámen vyřešen správně? (kvůli zobrazení stavu „už zodpovězeno")
    const reviewCorrect = ref(false);
    const loadingQuestion = ref(false);
    const answerSubmitting = ref(false);
    // historie zvolených možností v právě otevřené situaci – zvýrazňuje se v modalu
    const wrongOptionIds = ref([]);
    const correctOptionId = ref(null);

    const optionVariant = (option) => {
        if (correctOptionId.value === option.id) return 'correct';
        if (wrongOptionIds.value.includes(option.id)) return 'wrong';
        return 'default';
    };

    const optionClass = (option) => {
        const base = 'flex w-full items-center justify-between gap-3 rounded-lg border px-4 py-3 text-left text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:opacity-80';
        const variant = optionVariant(option);
        if (variant === 'correct') return `${base} border-emerald-400 bg-emerald-50 text-emerald-900`;
        if (variant === 'wrong') return `${base} border-red-300 bg-red-50 text-red-900`;
        return `${base} border-slate-200 bg-slate-50 text-slate-800 hover:border-blue-400 hover:bg-blue-50`;
    };

    const isOptionDisabled = (option) => (
        reviewMode.value
        || answerSubmitting.value
        || questionStatus.value === 'finished'
        || wrongOptionIds.value.includes(option.id)
    );

    // popis otázky tvořený jen obrázkem (žádný text) – obrázek pak vyplní modal
    const descriptionImageOnly = computed(() => {
        const description = activeQuestion.value?.description ?? '';
        return /^\s*<img\b[^>]*>\s*$/i.test(description);
    });

    // --- časový limit na odpověď (volitelný, settings.time_limit = počet sekund) ---
    const remainingSeconds = ref(0);
    let questionTimer = null;

    // počet sekund z nastavení otázky; null = situace bez limitu
    const parseTimeLimit = (settings) => {
        const seconds = Number(settings?.time_limit);
        return Number.isFinite(seconds) && seconds > 0 ? Math.floor(seconds) : null;
    };

    const questionTimeLimit = computed(() => activeQuestion.value?.timeLimit ?? null);
    const timeIsLow = computed(() => questionTimeLimit.value !== null && remainingSeconds.value <= 10);

    const formattedRemainingTime = computed(() => {
        const total = Math.max(0, remainingSeconds.value);
        return `${Math.floor(total / 60)}:${String(total % 60).padStart(2, '0')}`;
    });

    const timerPercent = computed(() => (
        questionTimeLimit.value ? Math.max(0, remainingSeconds.value) / questionTimeLimit.value * 100 : 0
    ));

    const stopQuestionTimer = () => {
        if (questionTimer !== null) {
            clearInterval(questionTimer);
            questionTimer = null;
        }
    };

    // po vyhodnocení situace (správně / 2× špatně) nabídneme pokračování nebo dokončení ostrova
    const buildContinueAction = (island, i) => {
        const isLastStone = i === island.buttonStates.length - 1;
        return isLastStone
            ? { label: t('islandGame.common.backToMap'), handler: () => { closeQuestion(); close(); } }
            : { label: t('islandGame.guide.continueNext'), handler: closeQuestion };
    };

    const retryAction = {
        label: t('islandGame.guide.retry'),
        handler: () => {
            guideMessage.value = null;
        },
    };

    // uloží snímek právě vyřešené situace, ať se k ní hráč může později vrátit a prohlédnout si ji
    const storeReview = (island, i) => {
        if (!activeQuestion.value) return;
        island.reviews[i] = {
            question: { ...activeQuestion.value },
            wrongOptionIds: [...wrongOptionIds.value],
            correctOptionId: correctOptionId.value,
            correct: island.buttonStates[i] === 'green',
        };
    };

    // vypršení času se počítá jako špatný pokus – po prvním zoranžoví, po druhém zčervená a odemkne další úkol
    const handleTimeUp = () => {
        stopQuestionTimer();

        const question = activeQuestion.value;
        const island = selectedIsland.value;
        if (!question || !island) return;

        const i = question.buttonIndex;
        const attemptNumber = island.attempts[i] + 1;
        island.attempts[i] = attemptNumber;

        if (attemptNumber >= 2) {
            island.buttonStates[i] = 'red';
            island.cachedQuestions[i] = null;
            if (i + 1 < island.buttonStates.length && island.buttonStates[i + 1] === 'locked') {
                island.buttonStates[i + 1] = 'default';
            }
            questionStatus.value = 'finished';
            storeReview(island, i);
            guideTone.value = 'failure';
            guideMessage.value = t('islandGame.guide.timeUpFinal');
            guideAction.value = buildContinueAction(island, i);
        } else {
            island.buttonStates[i] = 'orange';
            island.cachedQuestions[i] = { ...question };
            // modal zůstává otevřený – hráč si po dovysvětlení může zkusit odpovědět znovu
            questionStatus.value = 'retry';
            guideTone.value = 'retry';
            guideMessage.value = t('islandGame.guide.timeUpRetry');
            guideAction.value = retryAction;
        }
    };

    const startQuestionTimer = () => {
        stopQuestionTimer();

        if (questionTimeLimit.value === null) return;

        remainingSeconds.value = questionTimeLimit.value;
        questionTimer = setInterval(() => {
            remainingSeconds.value -= 1;

            if (remainingSeconds.value <= 0) {
                handleTimeUp();
            }
        }, 1000);
    };

    // načte situaci pro dané tlačítko z DB (endpoint vybere otázku adaptivní obtížnosti)
    const openQuestion = async (buttonIndex) => {
        const island = selectedIsland.value;
        const state = island?.buttonStates[buttonIndex];
        if (!island) return;

        // už vyřešený kámen (správně i 2× špatně) → jen prohlížení situace a vlastní odpovědi, nelze odpovídat
        if (state === 'green' || state === 'red') {
            const review = island.reviews[buttonIndex];
            if (!review) return;
            guideMessage.value = null;
            reviewMode.value = true;
            reviewCorrect.value = review.correct;
            questionStatus.value = 'finished';
            wrongOptionIds.value = [...review.wrongOptionIds];
            correctOptionId.value = review.correctOptionId;
            activeQuestion.value = { ...review.question };
            return;
        }

        if (state === 'locked') return;
        if (loadingQuestion.value) return;

        reviewMode.value = false;
        guideMessage.value = null;
        questionStatus.value = 'live';
        wrongOptionIds.value = [];
        correctOptionId.value = null;

        // po prvním špatném pokusu nabízíme stejnou situaci znovu – bez dalšího volání endpointu
        const cached = island.cachedQuestions[buttonIndex];
        if (cached) {
            activeQuestion.value = { ...cached, openedAt: Date.now() };
            startQuestionTimer();
            return;
        }

        loadingQuestion.value = true;

        try {
            const { data } = await axios.post(
                props.situationUrl,
                {
                    respondent_token: props.respondentToken,
                    island_id: island.key,
                    button: buttonIndex + 1,
                },
                { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } },
            );

            const situation = data.data;
            activeQuestion.value = {
                buttonIndex,
                situationId: situation.id,
                questionId: situation.question.id,
                title: situation.title || situation.question.perex,
                // perex ukážeme jen tehdy, když má situace vlastní titulek (jinak by se opakoval)
                perex: situation.title ? situation.question.perex : null,
                description: situation.question.description,
                options: situation.question.options,
                timeLimit: parseTimeLimit(situation.question.settings),
                openedAt: Date.now(),
            };
            startQuestionTimer();
        } catch (error) {
            guideMessage.value = error.response?.status === 404
                ? t('islandGame.guide.noMoreSituations')
                : t('islandGame.guide.loadError');
        } finally {
            loadingQuestion.value = false;
        }
    };

    const closeQuestion = () => {
        stopQuestionTimer();
        activeQuestion.value = null;
        questionStatus.value = 'live';
        reviewMode.value = false;
        wrongOptionIds.value = [];
        correctOptionId.value = null;
        hideSafetyCard();
    };

    // --- lightbox obrázku ze zadání situace ---
    // obrázky jsou v HTML popisu, proto klik chytáme delegovaně na kontejneru
    const zoomImage = ref(null);
    const zoomAlt = ref('');

    const openImageZoom = (event) => {
        const img = event.target.closest('img');
        if (!img) return;
        zoomImage.value = img.currentSrc || img.src;
        zoomAlt.value = img.alt || '';
    };

    const closeImageZoom = () => {
        zoomImage.value = null;
    };

    // zpětná vazba po špatné odpovědi – hodnocení zvolené možnosti, případně otázky
    const wrongAnswerHint = (result, optionId) => {
        const chosen = (result.evaluations ?? []).find((evaluation) => evaluation.optionId === optionId);
        return chosen?.evaluation || result.correctAnswerEvaluation || null;
    };

    // odešle zvolenou odpověď na endpoint, ten vyhodnotí správnost a případnou kartu bezpečí
    const answerQuestion = async (option) => {
        const question = activeQuestion.value;
        const island = selectedIsland.value;
        // v režimu prohlížení vyřešeného kamene se neodpovídá
        if (reviewMode.value || !question || !island || answerSubmitting.value) return;

        // hráč odpověděl včas – odpočet zastavíme
        stopQuestionTimer();
        answerSubmitting.value = true;
        const i = question.buttonIndex;
        const attemptNumber = island.attempts[i] + 1;

        try {
            const { data } = await axios.post(
                props.answerUrl,
                {
                    respondent_token: props.respondentToken,
                    question_id: question.questionId,
                    option_ids: [option.id],
                    seconds: Math.max(0, Math.round((Date.now() - question.openedAt) / 1000)),
                    attempt: attemptNumber,
                    island_id: island.key,
                    button: i + 1,
                },
                { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } },
            );

            const result = data.data;

            if (result.correct) {
                // správně – tlačítko zazelená, odemkne se další a hráč získá kartu bezpečí
                correctOptionId.value = option.id;
                if (island.buttonStates[i] !== 'green') {
                    island.tasks.completed = Math.min(island.tasks.total, island.tasks.completed + 1);
                    // novou kartu bezpečí přidáme do sbírky na nástěnce v majáku
                    if (result.safetyCard) {
                        collectedSafetyCards.value.push({ island: island.name, content: result.safetyCard });
                    }
                }
                island.buttonStates[i] = 'green';
                island.cachedQuestions[i] = null;
                if (i + 1 < island.buttonStates.length && island.buttonStates[i + 1] === 'locked') {
                    island.buttonStates[i + 1] = 'default';
                }
                questionStatus.value = 'finished';
                storeReview(island, i);
                guideTone.value = 'success';
                if (result.safetyCard) {
                    // karta vpravo nahoře (obrázek s rámem) ukáže obsah karty ve svém bílém poli
                    showSafetyCard(result.safetyCard);
                    guideMessage.value = t('islandGame.guide.successCard');
                } else {
                    guideMessage.value = t('islandGame.guide.success');
                }
                guideAction.value = buildContinueAction(island, i);
            } else {
                island.attempts[i] = attemptNumber;
                if (!wrongOptionIds.value.includes(option.id)) {
                    wrongOptionIds.value = [...wrongOptionIds.value, option.id];
                }
                const hint = wrongAnswerHint(result, option.id);

                if (attemptNumber >= 2) {
                    // druhá špatná odpověď – tlačítko zčervená, další úkol se odemkne
                    island.buttonStates[i] = 'red';
                    island.cachedQuestions[i] = null;
                    if (i + 1 < island.buttonStates.length && island.buttonStates[i + 1] === 'locked') {
                        island.buttonStates[i + 1] = 'default';
                    }
                    questionStatus.value = 'finished';
                    storeReview(island, i);
                    guideTone.value = 'failure';
                    guideMessage.value = hint
                        ? t('islandGame.guide.wrongFinalWithHint', { hint })
                        : t('islandGame.guide.wrongFinal');
                    guideAction.value = buildContinueAction(island, i);
                } else {
                    // první špatná odpověď – tlačítko zoranžoví a stejná situace se nabídne k druhému pokusu
                    island.buttonStates[i] = 'orange';
                    island.cachedQuestions[i] = { ...question };
                    questionStatus.value = 'retry';
                    guideTone.value = 'retry';
                    guideMessage.value = hint
                        ? t('islandGame.guide.wrongRetryWithHint', { hint })
                        : t('islandGame.guide.wrongRetry');
                    guideAction.value = retryAction;
                }
            }
        } catch (error) {
            guideMessage.value = t('islandGame.guide.answerError');
        } finally {
            answerSubmitting.value = false;
        }
    };

    onBeforeUnmount(() => {
        stopQuestionTimer();
    });

    return {
        activeQuestion,
        loadingQuestion,
        answerSubmitting,
        reviewMode,
        wrongOptionIds,
        correctOptionId,
        optionVariant,
        optionClass,
        isOptionDisabled,
        descriptionImageOnly,
        questionTimeLimit,
        timeIsLow,
        formattedRemainingTime,
        timerPercent,
        openQuestion,
        closeQuestion,
        answerQuestion,
        zoomImage,
        zoomAlt,
        openImageZoom,
        closeImageZoom,
    };
}
