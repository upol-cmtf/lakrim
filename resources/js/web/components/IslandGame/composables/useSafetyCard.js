import { ref, onBeforeUnmount } from 'vue';

// Karta bezpečí, která po správné odpovědi vyskočí vpravo nahoře a po chvíli
// sama zmizí.
export function useSafetyCard() {
    const safetyCard = ref(null);
    let safetyCardTimer = null;
    const safetyCardDismissDelay = 11000;

    const showSafetyCard = (content) => {
        clearTimeout(safetyCardTimer);
        safetyCard.value = content;
        safetyCardTimer = setTimeout(() => {
            safetyCard.value = null;
        }, safetyCardDismissDelay);
    };

    const hideSafetyCard = () => {
        clearTimeout(safetyCardTimer);
        safetyCard.value = null;
    };

    onBeforeUnmount(() => {
        clearTimeout(safetyCardTimer);
    });

    return { safetyCard, showSafetyCard, hideSafetyCard };
}
