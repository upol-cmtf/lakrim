import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

// Modal majáku a jeho hotspoty: důležité kontakty, nástěnka karet bezpečí a
// koš ulovených ryb. Drží jen stav otevření jednotlivých oken.
export function useLighthouseModals({ collectedSafetyCards, lighthouseHovered }) {
    const { t } = useI18n();

    // modal majáku – interiér (otevře se kliknutím na maják)
    const lighthouseModalOpen = ref(false);

    const openLighthouse = () => {
        lighthouseModalOpen.value = true;
    };

    const closeLighthouse = () => {
        lighthouseModalOpen.value = false;
        lighthouseHovered.value = false;
    };

    // důležité kontakty – otevře se kliknutím na záchranný kruh na stěně majáku.
    // texty jdou z překladů, telefonní čísla zůstávají v kódu (nejsou jazykové)
    const contactsOpen = ref(false);
    const importantContacts = computed(() => [
        { label: t('islandGame.contacts.police.label'), phone: '158', note: t('islandGame.contacts.police.note') },
        { label: t('islandGame.contacts.seniors.label'), phone: '800 200 007', note: t('islandGame.contacts.seniors.note') },
        { label: t('islandGame.contacts.dtest.label'), phone: '299 149 009', note: t('islandGame.contacts.dtest.note') },
        { label: t('islandGame.contacts.victims.label'), phone: '116 006', note: t('islandGame.contacts.victims.note') },
        { label: t('islandGame.contacts.ambulance.label'), phone: '155', note: t('islandGame.contacts.ambulance.note') },
        { label: t('islandGame.contacts.bank.label'), phone: null, phoneNote: t('islandGame.contacts.bank.phoneNote'), note: t('islandGame.contacts.bank.note') },
    ]);

    const openContacts = () => {
        contactsOpen.value = true;
    };

    const closeContacts = () => {
        contactsOpen.value = false;
    };

    // moje karty bezpečí – sbírka získaných karet (nástěnka v majáku)
    const cardsOpen = ref(false);
    // na nástěnce ukazujeme max 3 karty, zbytek je vidět v modalu po kliknutí
    const boardSafetyCards = computed(() => collectedSafetyCards.value.slice(0, 3));

    // karty na korku rozložíme do vějíře – vedle sebe, každá kousek doprava a lehce natočená
    const boardCardStyle = (idx) => ({
        left: `${30 + idx * 15}%`,
        top: `${44 + idx * 2}%`,
        transform: `translate(-50%, -50%) rotate(${-6 + idx * 6}deg)`,
    });

    const openCards = () => {
        cardsOpen.value = true;
    };

    const closeCards = () => {
        cardsOpen.value = false;
    };

    // koš ulovených ryb – modal s přehledem
    const basketOpen = ref(false);

    const openBasket = () => {
        basketOpen.value = true;
    };

    const closeBasket = () => {
        basketOpen.value = false;
    };

    return {
        lighthouseModalOpen,
        openLighthouse,
        closeLighthouse,
        contactsOpen,
        importantContacts,
        openContacts,
        closeContacts,
        cardsOpen,
        boardSafetyCards,
        boardCardStyle,
        openCards,
        closeCards,
        basketOpen,
        openBasket,
        closeBasket,
    };
}
