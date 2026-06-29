import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

// Bonusový úkol (easter egg) – otevře se kliknutím na rybku ve scéně. Po zavření
// se rybka „uloví": zaloguje se splnění a přesune do koše v majáku. Úkol z koše
// lze znovu otevřít v režimu prohlížení (nic se neloguje ani neloví).
export function useEasterEggs(props, { catchFish, scheduleFishHint, basketOpen }) {
    const { t } = useI18n();

    const easterEggModalOpen = ref(false);
    // načtený easter egg { id, description, evaluation }
    const easterEgg = ref(null);
    // co se v modalu zobrazuje: 'description' = zadání, 'evaluation' = vyhodnocení
    const easterEggView = ref('description');
    const easterEggLoading = ref(false);
    // true = endpoint vrátil prázdno (žádný nesplněný easter egg už nezbývá)
    const easterEggEmpty = ref(false);
    // rybka, ze které se modal otevřel (po zavření ji ulovíme)
    const easterEggActiveFish = ref(null);
    // true = úkol jen prohlížíme z koše (znovu se neloguje ani neloví)
    const easterEggReviewMode = ref(false);
    // čas otevření modalu – pro výpočet, jak dlouho hráč u úkolu byl
    let easterEggOpenedAt = 0;

    // obsah, který se zrovna v modalu renderuje (zadání nebo vyhodnocení)
    const easterEggContent = computed(() => {
        if (!easterEgg.value) return null;
        return easterEggView.value === 'evaluation'
            ? easterEgg.value.evaluation
            : easterEgg.value.description;
    });

    // načte znění dalšího nesplněného easter eggu a otevře modal s jeho zadáním
    const loadEasterEgg = async (f) => {
        if (easterEggLoading.value) return;

        easterEggLoading.value = true;
        easterEggEmpty.value = false;
        easterEgg.value = null;
        easterEggView.value = 'description';
        easterEggActiveFish.value = f;
        easterEggModalOpen.value = true;

        try {
            const { data } = await axios.post(
                props.easterEggUrl,
                { respondent_token: props.respondentToken },
                { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } },
            );

            if (data.data && data.data.description) {
                easterEgg.value = data.data;
                easterEggOpenedAt = Date.now();
            } else {
                easterEggEmpty.value = true;
            }
        } catch (error) {
            easterEggEmpty.value = true;
        } finally {
            easterEggLoading.value = false;
        }
    };

    // po zavření modalu uloví rybku: zaloguje splnění do DB, schová rybku z moře
    // a přidá ji do koše ulovených ryb v majáku
    const catchActiveFish = () => {
        const egg = easterEgg.value;
        const activeFish = easterEggActiveFish.value;
        // úkol se musel reálně načíst (prázdný/chybný stav neukládáme)
        if (!egg || !activeFish) return;

        const seconds = Math.max(0, Math.round((Date.now() - easterEggOpenedAt) / 1000));

        // log do DB (fire-and-forget – UI nečeká na odpověď)
        axios.post(
            props.respondentEasterEggUrl,
            {
                respondent_token: props.respondentToken,
                easter_egg_id: egg.id,
                seconds,
            },
            { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } },
        ).catch(() => {});

        // rybka zmizí z moře a uloží se do koše (znění úkolu + obrázek)
        catchFish(activeFish, egg);

        // rybka ulovena → schováme případnou pobídku a odpočet spustíme znovu
        scheduleFishHint();
    };

    const closeEasterEgg = () => {
        // jen při prvním splnění úkol ulovíme a zalogujeme; při prohlížení z koše ne
        const wasReview = easterEggReviewMode.value;
        if (!wasReview) {
            catchActiveFish();
        }
        easterEggModalOpen.value = false;
        easterEgg.value = null;
        easterEggView.value = 'description';
        easterEggEmpty.value = false;
        easterEggActiveFish.value = null;
        easterEggReviewMode.value = false;
        // po zavření prohlížené rybky se vrátíme zpět do koše ulovených ryb
        if (wasReview) {
            basketOpen.value = true;
        }
    };

    // vytáhne nadpis úkolu z jeho HTML (text uvnitř prvního <h3 class="ee-title">…</h3>)
    const eggTitle = (egg) => {
        if (!egg || !egg.description) return t('islandGame.easterEgg.defaultTitle');
        const match = egg.description.match(/<h3[^>]*>([\s\S]*?)<\/h3>/i);
        return match ? match[1].replace(/<[^>]+>/g, '').trim() : t('islandGame.easterEgg.defaultTitle');
    };

    // znovu otevře úkol ulovené rybky v režimu prohlížení (nic se neloguje)
    const reviewEasterEgg = (cf) => {
        if (!cf || !cf.egg) return;
        basketOpen.value = false;
        easterEggActiveFish.value = null;
        easterEggReviewMode.value = true;
        easterEgg.value = cf.egg;
        easterEggView.value = 'description';
        easterEggEmpty.value = false;
        easterEggLoading.value = false;
        easterEggModalOpen.value = true;
    };

    return {
        easterEggModalOpen,
        easterEgg,
        easterEggView,
        easterEggLoading,
        easterEggEmpty,
        easterEggContent,
        loadEasterEgg,
        closeEasterEgg,
        reviewEasterEgg,
        eggTitle,
    };
}
