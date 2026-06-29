import { ref, watch } from 'vue';

// Bublina průvodce vpravo dole – sdílí ji úvodní zpráva ostrova i zpětná vazba
// po odpovědi. Drží text, barevné ladění a volitelné akční tlačítko.
// (Zavírání kliknutím mimo bublinu řeší samotná komponenta GuideBubble, protože
// pracuje s konkrétním DOM prvkem.)
export function useGuide() {
    const guideMessage = ref(null);
    // barevné ladění bubliny: 'default' | 'success' | 'retry' | 'failure'
    const guideTone = ref('default');
    // volitelné tlačítko v bublině – { label, handler }
    const guideAction = ref(null);

    watch(guideMessage, (value) => {
        if (!value) {
            // s bublinou mizí i případné akční tlačítko a barevné ladění
            guideAction.value = null;
            guideTone.value = 'default';
        }
    });

    const runGuideAction = () => {
        const action = guideAction.value;
        guideAction.value = null;
        action?.handler();
    };

    return { guideMessage, guideTone, guideAction, runGuideAction };
}
