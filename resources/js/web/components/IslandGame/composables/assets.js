// Statická data a obrázky pro ostrovní hru.
// Cesty jsou relativní k tomuto souboru (o úroveň hlouběji než IslandScene.vue),
// proto o jedno `../` víc než v původní komponentě.
const asset = (filename) => new URL(`../../../../../images/islands/${filename}`, import.meta.url).href;

const buttonVariants = [1, 2, 3, 4, 5].map((n) => ({
    default: new URL(`../../../../../images/button${n}_default.svg`, import.meta.url).href,
    green: new URL(`../../../../../images/button${n}_green.svg`, import.meta.url).href,
    orange: new URL(`../../../../../images/button${n}_orange.svg`, import.meta.url).href,
    red: new URL(`../../../../../images/button${n}_red.svg`, import.meta.url).href,
}));

// button state: 'locked' | 'default' | 'green' (správně) | 'orange' (1× špatně – lze zkusit znovu) | 'red' (2× špatně – další úkol odemčen)
export const buttonSrc = (index, state) => {
    const variant = buttonVariants[index];
    if (state === 'green') return variant.green;
    if (state === 'orange') return variant.orange;
    if (state === 'red') return variant.red;
    return variant.default;
};

export const defaultPathButtonPositions = [
    { top: '63%', left: '38%' },
    { top: '56%', left: '46%' },
    { top: '49%', left: '54%' },
    { top: '42%', left: '61%' },
    { top: '35%', left: '69%' },
];

// první tlačítko je odemčené, zbytek se odemyká postupně
export const initialButtonStates = () => ['default', 'locked', 'locked', 'locked', 'locked'];

export const seaWaveletPath = 'M2,4 Q7,1 12,4 T22,4 T32,4 T38,4';

const waveletHeightFactor = 0.3;

const boxesOverlap = (a, b, pad = 0) => !(
    a.left + a.width + pad < b.left ||
    b.left + b.width + pad < a.left ||
    a.top + a.height + pad < b.top ||
    b.top + b.height + pad < a.top
);

const generateWavelets = (count) => {
    const placed = [];
    let safety = 0;
    while (placed.length < count && safety < count * 80) {
        safety++;
        const width = 3 + Math.random() * 4;
        const height = width * waveletHeightFactor;
        const left = 1 + Math.random() * (98 - width);
        const top = 1 + Math.random() * (98 - height);
        const candidate = { top, left, width, height };

        if (placed.some(p => boxesOverlap(candidate, p, 1.5))) continue;

        placed.push(candidate);
    }
    return placed.map(w => ({
        top: `${w.top.toFixed(1)}%`,
        left: `${w.left.toFixed(1)}%`,
        width: `${w.width.toFixed(1)}%`,
        opacity: (0.15 + Math.random() * 0.25).toFixed(2),
    }));
};

export const seaWavelets = generateWavelets(70);

export const waveOuterPath = 'M210.0,38.0L211.3,39.3L211.6,40.8L210.8,41.9L209.1,42.5L207.1,42.5L205.4,42.5L204.1,43.2L203.2,44.7L202.4,46.8L201.3,49.0L199.6,50.6L197.3,51.3L194.6,51.1L191.7,50.5L188.8,50.2L186.0,50.7L183.4,52.2L180.7,54.3L177.9,56.3L174.7,57.7L171.2,58.1L167.5,57.6L163.6,56.7L159.7,56.0L155.9,56.3L152.1,57.4L148.3,59.2L144.3,61.0L140.2,62.1L136.0,62.1L131.7,61.2L127.3,59.9L123.0,58.9L118.7,58.7L114.3,59.6L110.0,61.0L105.6,62.4L101.2,63.1L96.9,62.7L92.6,61.4L88.4,59.7L84.2,58.3L80.1,57.8L75.9,58.2L71.7,59.2L67.6,60.3L63.6,60.6L59.7,59.8L56.2,58.1L52.8,56.1L49.5,54.4L46.1,53.5L42.7,53.6L39.3,54.3L35.9,54.9L32.8,54.9L30.1,53.8L27.8,51.9L25.9,49.7L24.1,47.7L22.2,46.6L20.0,46.5L17.6,46.8L15.3,47.1L13.4,46.7L12.2,45.4L11.8,43.5L12.1,41.5L12.5,40.1L12.4,39.2L11.5,38.7L10.0,38.0L8.7,36.7L8.4,35.2L9.2,34.1L10.9,33.5L12.9,33.5L14.6,33.5L15.9,32.8L16.8,31.3L17.6,29.2L18.7,27.0L20.4,25.4L22.7,24.7L25.4,24.9L28.3,25.5L31.2,25.8L34.0,25.3L36.6,23.8L39.3,21.7L42.1,19.7L45.3,18.3L48.8,17.9L52.5,18.4L56.4,19.3L60.3,20.0L64.1,19.7L67.9,18.6L71.7,16.8L75.7,15.0L79.8,13.9L84.0,13.9L88.3,14.8L92.7,16.1L97.0,17.1L101.3,17.3L105.7,16.4L110.0,15.0L114.4,13.6L118.8,12.9L123.1,13.3L127.4,14.6L131.6,16.3L135.8,17.7L139.9,18.2L144.1,17.8L148.3,16.8L152.4,15.7L156.4,15.4L160.3,16.2L163.8,17.9L167.2,19.9L170.5,21.6L173.9,22.5L177.3,22.4L180.7,21.7L184.1,21.1L187.2,21.1L189.9,22.2L192.2,24.1L194.1,26.3L195.9,28.3L197.8,29.4L200.0,29.5L202.4,29.2L204.7,28.9L206.6,29.3L207.8,30.6L208.2,32.5L207.9,34.5L207.5,35.9L207.6,36.8L208.5,37.3Z';
export const waveInnerPath = 'M188.0,38.0L188.9,38.9L189.3,40.1L188.8,41.0L187.7,41.5L186.3,41.6L184.8,41.4L183.5,41.4L182.4,41.9L181.5,43.0L180.6,44.5L179.5,46.1L178.1,47.5L176.3,48.3L174.3,48.5L172.0,48.2L169.6,47.7L167.2,47.4L164.8,47.6L162.4,48.3L160.0,49.6L157.6,51.2L154.9,52.5L152.1,53.3L149.2,53.4L146.1,52.9L142.9,52.2L139.7,51.5L136.5,51.3L133.3,51.6L130.1,52.6L126.9,53.8L123.6,54.9L120.2,55.6L116.8,55.6L113.4,55.0L110.0,54.0L106.6,53.0L103.2,52.2L99.9,52.1L96.5,52.6L93.1,53.5L89.8,54.4L86.4,54.9L83.2,54.8L80.0,54.1L77.0,52.8L74.1,51.4L71.2,50.3L68.3,49.7L65.5,49.7L62.6,50.2L59.8,50.9L57.0,51.2L54.5,51.1L52.2,50.2L50.1,48.9L48.2,47.3L46.5,45.9L44.8,44.9L43.0,44.5L41.1,44.7L39.2,45.0L37.4,45.3L35.8,45.0L34.7,44.2L34.1,42.9L34.0,41.3L34.1,40.0L34.1,39.1L33.9,38.7L33.1,38.5L32.0,38.0L31.1,37.1L30.7,35.9L31.2,35.0L32.3,34.5L33.7,34.4L35.2,34.6L36.5,34.6L37.6,34.1L38.5,33.0L39.4,31.5L40.5,29.9L41.9,28.5L43.7,27.7L45.7,27.5L48.0,27.8L50.4,28.3L52.8,28.6L55.2,28.4L57.6,27.7L60.0,26.4L62.4,24.8L65.1,23.5L67.9,22.7L70.8,22.6L73.9,23.1L77.1,23.8L80.3,24.5L83.5,24.7L86.7,24.4L89.9,23.4L93.1,22.2L96.4,21.1L99.8,20.4L103.2,20.4L106.6,21.0L110.0,22.0L113.4,23.0L116.8,23.8L120.1,23.9L123.5,23.4L126.9,22.5L130.2,21.6L133.6,21.1L136.8,21.2L140.0,21.9L143.0,23.2L145.9,24.6L148.8,25.7L151.7,26.3L154.5,26.3L157.4,25.8L160.2,25.1L163.0,24.8L165.5,24.9L167.8,25.8L169.9,27.1L171.8,28.7L173.5,30.1L175.2,31.1L177.0,31.5L178.9,31.3L180.8,31.0L182.6,30.7L184.2,31.0L185.3,31.8L185.9,33.1L186.0,34.7L185.9,36.0L185.9,36.9L186.1,37.3L186.9,37.5Z';

export const lighthouseImg = asset('lighthouse.webp');
export const lampOffImg = new URL('../../../../../images/turned_off_lamp.webp', import.meta.url).href;
export const lampOnImg = new URL('../../../../../images/turned_on_lamp.webp', import.meta.url).href;
export const safetyCardImg = new URL('../../../../../images/safety_card.webp', import.meta.url).href;
export const lighthouseInteriorImg = new URL('../../../../../images/lighthouse_interior.webp', import.meta.url).href;
export const lifeRingImg = new URL('../../../../../images/life_ring.webp', import.meta.url).href;
export const fishingBasketImg = new URL('../../../../../images/fishing_basket.webp', import.meta.url).href;
export const corkboardImg = new URL('../../../../../images/corkboard.webp', import.meta.url).href;

// všechny varianty rybek – každá plující rybka dostane náhodně jednu z nich
export const fishImgs = Object.values(
    import.meta.glob('../../../../../images/fishes/*.webp', { eager: true, query: '?url', import: 'default' }),
);

// mraky kolem majáku – základní pozice (překrývají maják) a směr odplutí
export const clouds = [
    { top: '-20%', left: '5%',   width: '92%', dx: '-12%',  dy: '-185%', floatClass: 'cloud-float-a', delay: '0s'  },
    { top: '8%',   left: '-32%', width: '84%', dx: '-150%', dy: '-25%',  floatClass: 'cloud-float-b', delay: '-4s' },
    { top: '2%',   left: '50%',  width: '88%', dx: '150%',  dy: '-35%',  floatClass: 'cloud-float-c', delay: '-7s' },
    { top: '20%',  left: '8%',   width: '88%', dx: '5%',    dy: '-185%', floatClass: 'cloud-float-b', delay: '-2s' },
    { top: '42%',  left: '-24%', width: '72%', dx: '-150%', dy: '75%',   floatClass: 'cloud-float-a', delay: '-9s' },
    { top: '42%',  left: '52%',  width: '76%', dx: '150%',  dy: '85%',   floatClass: 'cloud-float-c', delay: '-5s' },
];

// rozmístění ostrovů ve scéně – přiřazuje se podle pořadí, není v DB
// beamAngle = natočení paprsku majáku k danému ostrovu
// (0° = dolů, kladné = po směru hodin / doleva, záporné = doprava)
export const islandLayouts = [
    { position: { top: '11%', left: '6%' }, bobClass: 'island-1', beamAngle: 106 },
    { position: { top: '11%', right: '6%' }, bobClass: 'island-2', beamAngle: -106 },
    { position: { bottom: '9%', left: '6%' }, bobClass: 'island-3', beamAngle: 56 },
    { position: { right: '6%', bottom: '9%' }, bobClass: 'island-4', beamAngle: -56 },
];

export { asset };
