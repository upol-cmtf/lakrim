import { reactive, ref, watch, onBeforeUnmount } from 'vue';
import { fishImgs } from './assets';

// Rybky plující po moři (každá je jeden easter egg) + koš ulovených ryb +
// pobídka „zkuste chytit rybku". Rozmístění/rychlost/směr jsou náhodné.
export function useSeaFish(props, selectedIsland) {
    // ulovené rybky („Moje ulovené ryby") přežívají i přenačtení stránky – držíme
    // je v localStorage pod klíčem svázaným s tokenem respondenta, i se zněním
    // úkolu, aby se daly v koši znovu otevřít a projít.
    const caughtFishStorageKey = `lakrim.caughtFish.${props.respondentToken}`;

    const loadCaughtFish = () => {
        try {
            const raw = window.localStorage.getItem(caughtFishStorageKey);
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    };

    const caughtFish = ref(loadCaughtFish());

    // úklid: po „začít znovu" (nový token) smažeme ulovené ryby starých
    // respondentů, ať v localStorage nezůstávají osiřelá data
    try {
        const prefix = 'lakrim.caughtFish.';
        for (let i = window.localStorage.length - 1; i >= 0; i--) {
            const key = window.localStorage.key(i);
            if (key && key.startsWith(prefix) && key !== caughtFishStorageKey) {
                window.localStorage.removeItem(key);
            }
        }
    } catch (error) {
        // úložiště nemusí být dostupné – tiše ignorujeme
    }

    const persistCaughtFish = () => {
        try {
            window.localStorage.setItem(caughtFishStorageKey, JSON.stringify(caughtFish.value));
        } catch (error) {
            // úložiště nemusí být dostupné (privátní režim apod.) – tiše ignorujeme
        }
    };

    // v moři plavou už jen nesplněné easter eggy (ty ulovené leží v koši)
    const fishCount = Math.max(0, props.easterEggsCount - caughtFish.value.length);

    // vyvážené náhodné rozdělení směrů – polovina doprava, polovina doleva,
    // pak zamícháno (Fisher–Yates), ať jsou obě strany vidět i při pár rybkách
    const fishDirections = Array.from({ length: fishCount }, (_, i) => i < Math.ceil(fishCount / 2));
    for (let i = fishDirections.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [fishDirections[i], fishDirections[j]] = [fishDirections[j], fishDirections[i]];
    }

    // každá rybka v moři má unikátní obrázek – nikdy nejsou vidět dvě stejné.
    // vynecháme i obrázky už ulovených ryb, ať se nepřekrývá celá sada (max 5 < 9).
    const usedImgs = new Set(caughtFish.value.map((cf) => cf.img));
    const availableImgs = fishImgs.filter((img) => !usedImgs.has(img));
    for (let i = availableImgs.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [availableImgs[i], availableImgs[j]] = [availableImgs[j], availableImgs[i]];
    }

    // aby rybky nikdy neplavaly „ve dvojici", rozdělíme moře na tolik vodorovných
    // pruhů, kolik je rybek – každá dostane vlastní pruh (nikdy stejná výška) a
    // k tomu vlastní časovou fázi rovnoměrně po dráze (nikdy nevyplavou současně).
    // Pořadí pruhů i fází zamícháme zvlášť, ať mezi výškou a fází není závislost.
    const laneCount = Math.max(1, fishCount);
    const laneSpan = 74 / laneCount; // celý rozsah výšek je 10–84 %
    const lanes = Array.from({ length: laneCount }, (_, i) => i);
    const phaseSlots = Array.from({ length: laneCount }, (_, i) => i);
    const shuffle = (arr) => {
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
    };
    shuffle(lanes);
    shuffle(phaseSlots);

    // reactive – ulovená rybka se z pole odebere a zmizí z moře
    const fish = reactive(Array.from({ length: fishCount }, (_, i) => {
        const duration = 30 + Math.random() * 26;
        // pruh + drobný jitter uvnitř pruhu, ať nesedí přesně na mřížce, ale se
        // zachovaným odstupem od sousedů (10 % okraj z obou stran pruhu)
        const top = 10 + (lanes[i] + 0.1 + Math.random() * 0.8) * laneSpan;
        // fáze rovnoměrně po celé dráze (slot + jitter v rámci slotu) → posun do
        // animace je záporný, takže každá rybka startuje v jiném místě dráhy
        const phase = (phaseSlots[i] + Math.random()) / laneCount;
        return {
            key: `fish-${i}`,
            top: `${top.toFixed(1)}%`,
            duration: `${duration.toFixed(1)}s`,
            delay: `${(-phase * duration).toFixed(1)}s`,
            scale: (0.75 + Math.random() * 0.55).toFixed(2),
            // unikátní obrázek rybky; pojistka pro případ, že by úkolů bylo víc než obrázků
            img: availableImgs[i] ?? fishImgs[i % fishImgs.length],
            // plné spektrum – využívá se ještě pro siluetu v koši ulovených ryb
            hue: Math.round(Math.random() * 360),
            // true = plave zleva doprava, false = zprava doleva
            rightward: fishDirections[i],
        };
    }));

    const fishStyle = (f) => ({
        top: f.top,
        animationDuration: f.duration,
        animationDelay: f.delay,
        animationDirection: f.rightward ? 'normal' : 'reverse',
        // základní velikost rybky drží CSS proměnná, ať ji hover umí zvětšit
        '--fish-scale': f.scale,
    });

    // obrázky rybek jsou nakreslené hlavou doleva, takže zrcadlíme obráceně:
    // plave-li doprava (rightward), otočíme ji hlavou doprava
    const fishInnerStyle = (f) => ({
        transform: `scaleX(${f.rightward ? -1 : 1})`,
    });

    // odebere rybku z moře a uloví ji do koše (uloží i znění úkolu a obrázek)
    const catchFish = (activeFish, egg) => {
        const index = fish.findIndex((x) => x.key === activeFish.key);
        if (index !== -1) fish.splice(index, 1);

        caughtFish.value.push({
            key: `caught-${egg.id}`,
            egg,
            img: activeFish.img,
            hue: activeFish.hue,
            x: `${24 + Math.random() * 52}%`,
            y: `${14 + Math.random() * 26}%`,
            rot: Math.round(Math.random() * 50 - 25),
            scale: (0.9 + Math.random() * 0.3).toFixed(2),
        });
        persistCaughtFish();
    };

    // --- pobídka „zkuste chytit rybku" ---
    // ukáže se na přehledu moře, když hráč delší dobu žádnou rybku neulovil
    // a nějaké mu tam ještě plavou
    const fishHintVisible = ref(false);
    const fishHintDelay = 25000;
    let fishHintTimer = null;

    const hideFishHint = () => {
        clearTimeout(fishHintTimer);
        fishHintVisible.value = false;
    };

    // (re)start odpočtu: po nečinnosti hlášku ukážeme, jakmile se chytne rybka nebo
    // odejde z přehledu, schováme ji a odpočet běží znovu od začátku
    const scheduleFishHint = () => {
        hideFishHint();
        if (selectedIsland.value || fish.length === 0) return;
        fishHintTimer = setTimeout(() => {
            if (!selectedIsland.value && fish.length > 0) {
                fishHintVisible.value = true;
            }
        }, fishHintDelay);
    };

    // na přehledu moře odpočet běží, v detailu ostrova se hláška schová
    watch(selectedIsland, () => {
        scheduleFishHint();
    }, { immediate: true });

    onBeforeUnmount(() => {
        clearTimeout(fishHintTimer);
    });

    return {
        fish,
        caughtFish,
        persistCaughtFish,
        catchFish,
        fishStyle,
        fishInnerStyle,
        fishHintVisible,
        hideFishHint,
        scheduleFishHint,
    };
}
