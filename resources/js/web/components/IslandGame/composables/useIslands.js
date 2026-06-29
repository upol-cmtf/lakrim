import { computed, reactive, ref, watch, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    asset,
    defaultPathButtonPositions,
    initialButtonStates,
    islandLayouts,
} from './assets';

// Stav ostrovů, výběr ostrova a úvodní zprávy průvodce. Postup hráče (karty
// bezpečí) i mlha/záře majáku se odvozují odsud.
export function useIslands(props, { guideMessage, guideAction, hideSafetyCard }) {
    const { t } = useI18n();

    const islands = reactive(
        props.islandsData.map((island, index) => {
            const layout = islandLayouts[index % islandLayouts.length];

            return {
                key: island.id,
                name: island.name,
                image: asset(island.image),
                detailImage: asset(`detail/${island.image}`),
                guideImage: island.guide ? asset(island.guide) : null,
                introMessage: island.intro ?? null,
                position: layout.position,
                bobClass: layout.bobClass,
                beamAngle: layout.beamAngle,
                tasks: { completed: 0, total: 5 },
                pathButtonPositions: defaultPathButtonPositions,
                buttonStates: initialButtonStates(),
                // počet dosavadních neúspěšných pokusů a uložená rozehraná situace pro každé tlačítko
                attempts: [0, 0, 0, 0, 0],
                cachedQuestions: [null, null, null, null, null],
                // snímek vyřešené situace (otázka + jak hráč odpověděl) pro pozdější prohlížení
                reviews: [null, null, null, null, null],
            };
        }),
    );

    const selectedIsland = ref(null);

    // celkový postup hráče – z něj se odvozuje mlha i záře majáku
    const totalCards = computed(() => islands.reduce((sum, island) => sum + island.tasks.total, 0));
    const collectedCards = computed(() => islands.reduce((sum, island) => sum + island.tasks.completed, 0));
    const progress = computed(() => (totalCards.value > 0 ? collectedCards.value / totalCards.value : 0));

    // hra je dokončená, když je každý kámen na každém ostrově vyřešený (zeleně i červeně)
    const allStonesResolved = computed(() =>
        islands.length > 0
        && islands.every((island) =>
            island.buttonStates.every((state) => state === 'green' || state === 'red')),
    );

    // --- obnovení rozehrané hry ---
    // stav ostrovů (vyřešené kameny, pokusy, snímky pro prohlížení, rozehraná
    // situace) i sbírka karet bezpečí přežijí odchod na úvodní stránku a návrat
    // zpět – držíme je v localStorage pod klíčem svázaným s tokenem respondenta
    // (stejně jako ulovené ryby), takže hráč pokračuje tam, kde skončil.
    const progressStorageKey = `lakrim.islandProgress.${props.respondentToken}`;

    const loadProgress = () => {
        try {
            const raw = window.localStorage.getItem(progressStorageKey);
            const parsed = raw ? JSON.parse(raw) : null;
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (error) {
            return {};
        }
    };

    const savedProgress = loadProgress();

    // úklid: po „začít znovu" má hráč nový token → smažeme postup starých
    // respondentů, ať se v localStorage nehromadí osiřelá data
    try {
        const prefix = 'lakrim.islandProgress.';
        for (let i = window.localStorage.length - 1; i >= 0; i--) {
            const key = window.localStorage.key(i);
            if (key && key.startsWith(prefix) && key !== progressStorageKey) {
                window.localStorage.removeItem(key);
            }
        }
    } catch (error) {
        // úložiště nemusí být dostupné – tiše ignorujeme
    }

    // obnovíme stav jednotlivých ostrovů (klíč = id ostrova); defenzivně, ať
    // případná změna struktury dat hru nerozbije
    const savedIslands = savedProgress.islands ?? {};
    islands.forEach((island) => {
        const saved = savedIslands[island.key];
        if (!saved) return;
        if (Array.isArray(saved.buttonStates) && saved.buttonStates.length === island.buttonStates.length) {
            island.buttonStates = [...saved.buttonStates];
        }
        if (Array.isArray(saved.attempts) && saved.attempts.length === island.attempts.length) {
            island.attempts = [...saved.attempts];
        }
        if (Array.isArray(saved.cachedQuestions) && saved.cachedQuestions.length === island.cachedQuestions.length) {
            island.cachedQuestions = [...saved.cachedQuestions];
        }
        if (Array.isArray(saved.reviews) && saved.reviews.length === island.reviews.length) {
            island.reviews = [...saved.reviews];
        }
        if (typeof saved.completed === 'number') {
            island.tasks.completed = Math.min(island.tasks.total, Math.max(0, saved.completed));
        }
    });

    // karty bezpečí získané za správně vyřešené situace (sbírka na nástěnce v majáku)
    const collectedSafetyCards = ref(Array.isArray(savedProgress.safetyCards) ? savedProgress.safetyCards : []);

    const persistProgress = () => {
        try {
            const data = { islands: {}, safetyCards: collectedSafetyCards.value };
            islands.forEach((island) => {
                data.islands[island.key] = {
                    completed: island.tasks.completed,
                    buttonStates: island.buttonStates,
                    attempts: island.attempts,
                    cachedQuestions: island.cachedQuestions,
                    reviews: island.reviews,
                };
            });
            window.localStorage.setItem(progressStorageKey, JSON.stringify(data));
        } catch (error) {
            // úložiště nemusí být dostupné (privátní režim apod.) – tiše ignorujeme
        }
    };

    // jakákoli změna stavu ostrovů nebo sbírky karet se hned uloží
    watch([islands, collectedSafetyCards], persistProgress, { deep: true });

    // najetí myší na maják (na úvodní obrazovce) mraky kolem majáku jen rozestoupí (nezmizí)
    const lighthouseHovered = ref(false);
    // jak moc se mraky při hoveru rozestoupí – dost na odhalení celého majáku včetně špičky, ale zůstanou viditelné okolo
    const cloudHoverSpread = 0.85;
    // mraky halí maják na začátku; s postupem hráče odplují, při hoveru se jen pootevřou kolem majáku
    const cloudStyle = (cloud) => {
        // posun (rozestup) – buď podle postupu, nebo aspoň o kousek při hoveru
        const spread = lighthouseHovered.value
            ? Math.max(progress.value, cloudHoverSpread)
            : progress.value;
        return {
            top: cloud.top,
            left: cloud.left,
            width: cloud.width,
            // průhlednost drží postup hráče – hover mraky neschová, jen je rozhrne
            opacity: Math.max(0, 1 - progress.value),
            transform: `translate(calc(${cloud.dx} * ${spread}), calc(${cloud.dy} * ${spread}))`,
        };
    };

    // první kámen pulzuje, dokud hráč na ostrově na nic neodpověděl – navádí, kde začít
    const shouldPulse = (i) => {
        const island = selectedIsland.value;
        if (!island || i !== 0) return false;
        return island.buttonStates[0] === 'default'
            && island.tasks.completed === 0
            && island.attempts.every((attempt) => attempt === 0);
    };

    // --- úvodní zpráva ostrova ---
    // klíč v sessionStorage – intro se ukáže jednou za session, příští spuštění aplikace ho zobrazí znovu
    const seenIntrosStorageKey = 'islandGame.seenIntros';

    const readSeenIntros = () => {
        try {
            const raw = window.sessionStorage.getItem(seenIntrosStorageKey);
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    };

    const hasSeenIntro = (islandId) => readSeenIntros().includes(islandId);

    const markIntroSeen = (islandId) => {
        try {
            const seen = readSeenIntros();
            if (!seen.includes(islandId)) {
                seen.push(islandId);
                window.sessionStorage.setItem(seenIntrosStorageKey, JSON.stringify(seen));
            }
        } catch (error) {
            // sessionStorage nemusí být dostupné (privátní režim) – v tom případě se intro ukáže příště znovu
        }
    };

    // tlačítko pod úvodní zprávou ostrova – zavře intro a hráč může začít plnit úkoly
    const startPlayingAction = {
        label: t('islandGame.guide.startPlaying'),
        handler: () => {
            guideMessage.value = null;
        },
    };

    // prodleva, než se po otevření detailu ostrova ukáže úvodní zpráva průvodce
    let introTimer = null;

    const open = (island) => {
        clearTimeout(introTimer);
        selectedIsland.value = island;
        guideMessage.value = null;

        // detail se otevře hned, úvodní zpráva průvodce naběhne až po krátké prodlevě
        // intro se ukáže jen při prvním otevření – další otevření už ho přeskočí
        if (island.introMessage && !hasSeenIntro(island.key)) {
            introTimer = setTimeout(() => {
                // pojistka: hráč mezitím nemusel detail zavřít / přepnout
                if (selectedIsland.value === island) {
                    guideMessage.value = island.introMessage;
                    guideAction.value = startPlayingAction;
                    markIntroSeen(island.key);
                }
            }, 700);
        }
    };

    // kliknutí na průvodce kdykoliv znovu vyvolá úvodní zprávu ostrova (i když už byla viděná)
    const replayIntro = () => {
        clearTimeout(introTimer);
        const island = selectedIsland.value;
        if (!island || !island.introMessage) {
            return;
        }
        guideMessage.value = island.introMessage;
        guideAction.value = startPlayingAction;
    };

    const close = () => {
        clearTimeout(introTimer);
        selectedIsland.value = null;
        guideMessage.value = null;
        hideSafetyCard();
    };

    onBeforeUnmount(() => {
        clearTimeout(introTimer);
    });

    return {
        islands,
        selectedIsland,
        totalCards,
        collectedCards,
        progress,
        allStonesResolved,
        collectedSafetyCards,
        lighthouseHovered,
        cloudStyle,
        shouldPulse,
        open,
        close,
        replayIntro,
    };
}
