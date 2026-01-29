<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dobrodružství na ostrovech</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .game-container {
            width: 95%;
            max-width: 1400px;
            height: 90vh;
            min-height: 500px;
            background: linear-gradient(180deg, #5dade2 0%, #3498db 40%, #2874a6 70%, #1a5276 100%);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            position: relative;
        }

        @media (max-width: 768px) {
            body {
                padding: 5px;
            }

            .game-container {
                width: 100%;
                height: 95vh;
                border-radius: 20px;
            }
        }

        .ocean {
            position: relative;
            width: 100%;
            height: 100%;
            background:
                    radial-gradient(ellipse at 20% 30%, rgba(255,255,255,0.15) 0%, transparent 50%),
                    radial-gradient(ellipse at 80% 70%, rgba(255,255,255,0.1) 0%, transparent 50%),
                    linear-gradient(180deg, #5dade2 0%, #3498db 40%, #2874a6 70%, #1a5276 100%);
        }

        .waves {
            position: absolute;
            width: 200%;
            height: 100%;
            background: repeating-linear-gradient(
                    90deg,
                    transparent 0px,
                    rgba(255, 255, 255, 0.05) 50px,
                    transparent 100px
            );
            animation: wave 20s linear infinite;
        }

        @keyframes wave {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .wave-layer {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .wave-line {
            position: absolute;
            width: 150%;
            height: 40px;
            left: -20%;
            opacity: 0.4;
        }

        .wave-line::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background:
                    radial-gradient(ellipse 100px 20px at 10% 50%, rgba(255,255,255,0.4) 0%, transparent 70%),
                    radial-gradient(ellipse 120px 25px at 30% 50%, rgba(255,255,255,0.5) 0%, transparent 70%),
                    radial-gradient(ellipse 80px 18px at 50% 50%, rgba(255,255,255,0.3) 0%, transparent 70%),
                    radial-gradient(ellipse 110px 22px at 70% 50%, rgba(255,255,255,0.45) 0%, transparent 70%),
                    radial-gradient(ellipse 90px 20px at 90% 50%, rgba(255,255,255,0.35) 0%, transparent 70%);
        }

        .wave-line:nth-child(1) {
            top: 15%;
            animation: waveMove1 12s ease-in-out infinite;
        }

        .wave-line:nth-child(2) {
            top: 35%;
            animation: waveMove2 15s ease-in-out infinite;
            animation-delay: 2s;
        }

        .wave-line:nth-child(3) {
            top: 55%;
            animation: waveMove1 18s ease-in-out infinite;
            animation-delay: 4s;
        }

        .wave-line:nth-child(4) {
            top: 70%;
            animation: waveMove2 14s ease-in-out infinite;
            animation-delay: 1s;
        }

        .wave-line:nth-child(5) {
            top: 85%;
            animation: waveMove1 16s ease-in-out infinite;
            animation-delay: 3s;
        }

        @keyframes waveMove1 {
            0%, 100% {
                transform: translateX(0) translateY(0);
                opacity: 0.3;
            }
            50% {
                transform: translateX(15%) translateY(-10px);
                opacity: 0.6;
            }
        }

        @keyframes waveMove2 {
            0%, 100% {
                transform: translateX(0) translateY(0);
                opacity: 0.25;
            }
            50% {
                transform: translateX(20%) translateY(-8px);
                opacity: 0.5;
            }
        }

        .foam {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .foam-particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255,255,255,0.7);
            border-radius: 50%;
            animation: foamFloat 8s ease-in-out infinite;
        }

        .foam-particle:nth-child(1) { left: 10%; top: 20%; animation-delay: 0s; }
        .foam-particle:nth-child(2) { left: 25%; top: 45%; animation-delay: 1.5s; }
        .foam-particle:nth-child(3) { left: 40%; top: 30%; animation-delay: 3s; }
        .foam-particle:nth-child(4) { left: 55%; top: 60%; animation-delay: 0.7s; }
        .foam-particle:nth-child(5) { left: 70%; top: 25%; animation-delay: 2.3s; }
        .foam-particle:nth-child(6) { left: 85%; top: 50%; animation-delay: 4s; }
        .foam-particle:nth-child(7) { left: 15%; top: 75%; animation-delay: 1.2s; }
        .foam-particle:nth-child(8) { left: 60%; top: 80%; animation-delay: 3.5s; }

        @keyframes foamFloat {
            0%, 100% {
                transform: translate(0, 0) scale(1);
                opacity: 0.3;
            }
            25% {
                transform: translate(15px, -20px) scale(1.3);
                opacity: 0.7;
            }
            50% {
                transform: translate(30px, -10px) scale(0.9);
                opacity: 0.5;
            }
            75% {
                transform: translate(20px, 10px) scale(1.1);
                opacity: 0.6;
            }
        }

        .fish-container {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 45;
        }

        .fish {
            position: absolute;
            width: 60px;
            height: 40px;
            animation: swimFish 18s linear infinite;
            pointer-events: none;
            opacity: 0;
        }

        .fish-1 {
            top: 20%;
            animation-delay: 2s;
        }

        .fish-2 {
            top: 40%;
            animation-delay: 6s;
        }

        .fish-3 {
            top: 60%;
            animation-delay: 10s;
        }

        .fish-4 {
            top: 80%;
            animation-delay: 14s;
        }

        @keyframes swimFish {
            0% {
                left: -70px;
                opacity: 0;
            }
            2% {
                opacity: 1;
            }
            98% {
                opacity: 1;
            }
            100% {
                left: calc(100% + 70px);
                opacity: 0;
            }
        }

        .light-rays {
            position: absolute;
            width: 100%;
            height: 100%;
            background:
                    radial-gradient(ellipse at 30% 20%, rgba(255,255,255,0.15) 0%, transparent 40%),
                    radial-gradient(ellipse at 70% 35%, rgba(255,255,255,0.1) 0%, transparent 35%);
            animation: lightShift 20s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes lightShift {
            0%, 100% {
                opacity: 0.5;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.05);
            }
        }

        .score-board {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 40px;
            border-radius: 50px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 100;
            display: flex;
            gap: 30px;
            align-items: center;
        }

        @media (max-width: 768px) {
            .score-board {
                padding: 10px 20px;
                gap: 15px;
                top: 10px;
            }

            .score-item {
                font-size: 14px !important;
            }

            .score-number {
                font-size: 18px !important;
            }

            .island svg {
                width: 70px !important;
                height: 70px !important;
            }

            .island-number {
                display: none !important;
            }

            .status-mark {
                font-size: 22px !important;
            }
        }

        .score-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 600;
        }

        .score-number {
            color: #2874a6;
            font-size: 24px;
            font-weight: 700;
        }

        .island {
            position: absolute;
            cursor: pointer;
            transition: transform 0.3s ease;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
            z-index: 50;
        }

        .island:hover {
            transform: scale(1.1);
            filter: drop-shadow(0 6px 12px rgba(0,0,0,0.4));
        }

        .island svg {
            width: 180px;
            height: 180px;
        }

        .island-number {
            position: absolute;
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.98);
            color: #2874a6;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            box-shadow: 0 3px 8px rgba(0,0,0,0.25);
            z-index: 60;
            white-space: nowrap;
            max-width: 190px;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }

        @media (max-width: 768px) {
            .island-number {
                font-size: 9px !important;
                padding: 4px 8px !important;
                max-width: 120px !important;
                bottom: -5px !important;
            }
        }

        .status-mark {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 45px;
            font-weight: 900;
            z-index: 55;
            text-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }

        .island.correct .status-mark {
            display: block;
            color: #4CAF50;
        }

        .island.wrong .status-mark {
            display: block;
            color: #F44336;
        }

        .island.correct .status-mark::before {
            content: '✓';
        }

        .island.wrong .status-mark::before {
            content: '✗';
        }

        .island.correct .island-number {
            background: #4CAF50;
            color: white;
            box-shadow: 0 3px 10px rgba(76, 175, 80, 0.5);
        }

        .island.wrong .island-number {
            background: #F44336;
            color: white;
            box-shadow: 0 3px 10px rgba(244, 67, 54, 0.5);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
            max-height: 80vh;
            overflow-y: auto;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            font-size: 26px;
            font-weight: 700;
            color: #2874a6;
            margin-bottom: 20px;
            text-align: center;
        }

        .modal-question {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 25px;
            color: #333;
        }

        .options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option {
            background: #f0f0f0;
            padding: 15px 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 3px solid transparent;
            font-weight: 500;
        }

        .option:hover {
            background: #e0e0e0;
            transform: translateX(5px);
        }

        .option.selected {
            border-color: #2874a6;
            background: #d4e9f7;
        }

        .submit-btn {
            background: #2874a6;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
        }

        .submit-btn:hover {
            background: #1a5276;
            transform: scale(1.02);
        }

        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: scale(1);
        }

        .feedback {
            margin-top: 20px;
            padding: 15px;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
            display: none;
        }

        .feedback.correct {
            background: #c8e6c9;
            color: #2e7d32;
            display: block;
        }

        .feedback.wrong {
            background: #ffcdd2;
            color: #c62828;
            display: block;
        }

        .close-btn {
            background: #666;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
            display: none;
            font-family: 'Poppins', sans-serif;
        }

        .close-btn.visible {
            display: inline-block;
        }

        @media (max-width: 768px) {
            .modal-content {
                padding: 25px !important;
                width: 95% !important;
            }

            .modal-header {
                font-size: 22px !important;
            }

            .modal-question {
                font-size: 16px !important;
            }

            .option {
                padding: 12px 15px !important;
                font-size: 14px !important;
            }
        }
    </style>
</head>
<body>
<div class="game-container">
    <div class="ocean">
        <div class="waves"></div>
        <div class="light-rays"></div>

        <!-- Vlnové linie -->
        <div class="wave-layer">
            <div class="wave-line"></div>
            <div class="wave-line"></div>
            <div class="wave-line"></div>
            <div class="wave-line"></div>
            <div class="wave-line"></div>
        </div>

        <!-- Pěnové částice -->
        <div class="foam">
            <div class="foam-particle"></div>
            <div class="foam-particle"></div>
            <div class="foam-particle"></div>
            <div class="foam-particle"></div>
            <div class="foam-particle"></div>
            <div class="foam-particle"></div>
            <div class="foam-particle"></div>
            <div class="foam-particle"></div>
        </div>

        <!-- Ryby -->
        <div class="fish-container">
            <div class="fish fish-1">
                <svg viewBox="0 0 50 35">
                    <!-- Tělo ryby -->
                    <ellipse cx="25" cy="17" rx="18" ry="10" fill="#FF6B6B" opacity="0.85"/>
                    <!-- Hlava -->
                    <ellipse cx="35" cy="17" rx="8" ry="7" fill="#FF8787" opacity="0.9"/>
                    <!-- Oko -->
                    <circle cx="38" cy="15" r="2.5" fill="#2C3E50"/>
                    <circle cx="39" cy="14.5" r="1" fill="#FFFFFF"/>
                    <!-- Ocasní ploutev -->
                    <path d="M 7,17 L 0,10 L 5,17 L 0,24 Z" fill="#FF4757" opacity="0.8"/>
                    <!-- Hřbetní ploutev -->
                    <path d="M 22,8 L 20,3 L 25,8 Z" fill="#FF4757" opacity="0.8"/>
                    <!-- Břišní ploutev -->
                    <path d="M 20,24 L 18,29 L 23,24 Z" fill="#FF4757" opacity="0.8"/>
                </svg>
            </div>

            <div class="fish fish-2">
                <svg viewBox="0 0 50 35">
                    <ellipse cx="25" cy="17" rx="17" ry="9" fill="#4A90E2" opacity="0.85"/>
                    <ellipse cx="34" cy="17" rx="7" ry="6.5" fill="#5FA3E8" opacity="0.9"/>
                    <circle cx="37" cy="15.5" r="2.3" fill="#2C3E50"/>
                    <circle cx="38" cy="15" r="0.9" fill="#FFFFFF"/>
                    <path d="M 8,17 L 1,11 L 6,17 L 1,23 Z" fill="#3A7BC8" opacity="0.8"/>
                    <path d="M 23,9 L 21,4 L 26,9 Z" fill="#3A7BC8" opacity="0.8"/>
                    <path d="M 21,24 L 19,28 L 24,24 Z" fill="#3A7BC8" opacity="0.8"/>
                </svg>
            </div>

            <div class="fish fish-3">
                <svg viewBox="0 0 50 35">
                    <ellipse cx="25" cy="17" rx="16" ry="9" fill="#FFB84D" opacity="0.85"/>
                    <ellipse cx="33" cy="17" rx="7" ry="6" fill="#FFCA6F" opacity="0.9"/>
                    <circle cx="36" cy="15.5" r="2.2" fill="#2C3E50"/>
                    <circle cx="37" cy="15" r="0.8" fill="#FFFFFF"/>
                    <path d="M 9,17 L 2,12 L 7,17 L 2,22 Z" fill="#FF9F33" opacity="0.8"/>
                    <path d="M 23,10 L 21,5 L 26,10 Z" fill="#FF9F33" opacity="0.8"/>
                </svg>
            </div>

            <div class="fish fish-4">
                <svg viewBox="0 0 50 35">
                    <ellipse cx="25" cy="17" rx="17" ry="8.5" fill="#9B59B6" opacity="0.85"/>
                    <ellipse cx="34" cy="17" rx="7" ry="6" fill="#B07CC6" opacity="0.9"/>
                    <circle cx="37" cy="16" r="2.2" fill="#2C3E50"/>
                    <circle cx="38" cy="15.5" r="0.9" fill="#FFFFFF"/>
                    <path d="M 8,17 L 1,12 L 6,17 L 1,22 Z" fill="#8E44AD" opacity="0.8"/>
                    <path d="M 23,10 L 21,6 L 26,10 Z" fill="#8E44AD" opacity="0.8"/>
                    <path d="M 21,23 L 19,27 L 24,23 Z" fill="#8E44AD" opacity="0.8"/>
                </svg>
            </div>
        </div>

        <div class="score-board">
            <div class="score-item">
                <span>🏆</span>
                <span>Skóre: <span class="score-number" id="score">0</span></span>
            </div>
            <div class="score-item">
                <span>✅</span>
                <span><span class="score-number" id="correct">0</span>/13</span>
            </div>
        </div>

        <div id="islands"></div>
    </div>
</div>

<div class="modal" id="modal">
    <div class="modal-content">
        <div class="modal-header" id="modalHeader">Ostrov #1</div>
        <div class="modal-question" id="question"></div>
        <div class="options" id="options"></div>
        <button class="submit-btn" id="submitBtn" disabled>Potvrdit odpověď</button>
    </div>
</div>

<script>
	const islandLabels = [
		"Poznám důvěryhodný web",
		"Romantická legenda",
		"Nabídka snadného výdělku",
		"Falešný technik",
		"Deepfake",
		"Kurýr chce peníze na nečekanou zásilku",
		"Známý chce přeposlat kód",
		"Recenze pod příspěvky na FB",
		"Poplašná zpráva",
		"Předžalobní výzva",
		"Váš účet bude zablokován",
		"Článek cílící na city",
		"\"Ahoj babi, mám nové číslo\""
	];

	const challenges = [
		{
			question: "Na ostrově jste objevili starou mapu. Podle hvězd na mapě je poklad ukryt na severovýchodě. Jakým směrem se vydáte?",
			options: ["Doleva a nahoru", "Doprava a nahoru", "Doleva a dolů", "Doprava a dolů"],
			correct: 1
		},
		{
			question: "Našli jste studnu, ale lano je příliš krátké. Máte: provaz 3m, tyč 2m a plachtu. Co použijete k prodloužení?",
			options: ["Provaz", "Tyč", "Plachtu roztrháte na pruhy", "Svážete provaz a tyč"],
			correct: 3
		},
		{
			question: "V jeskyni jsou 3 dveře: Za první je oheň, za druhou lev, za třetí pokoj plný zrcadel. Kterými dveřmi projdete?",
			options: ["Prvními - oheň zhasnu vodou", "Druhými - lev už je hladový", "Třetími - zrcadla nejsou nebezpečná", "Žádnými"],
			correct: 2
		},
		{
			question: "Potřebujete přeplavat řeku, ale je v ní 10 krokodýlů. Máte prázdný sud. Co uděláte?",
			options: ["Použiji sud jako člun", "Počkám až krokodýli odplanou", "Hledám mělčí místo", "Hodím sud jako návnadu"],
			correct: 0
		},
		{
			question: "Na stromě rostou kokosy 6 metrů vysoko. Máte jen meč. Jak se dostanete ke kokosům?",
			options: ["Srazím je mečem", "Vyšplhám se", "Podříznu strom", "Počkám až spadnou"],
			correct: 1
		},
		{
			question: "Máte 3 pochodně a musíte projet 5 tmavými jeskyněmi. Každá pochodeň vydrží projít jen 2 jeskyně. Jak to uděláte?",
			options: ["Vezmu si 3 pochodně najednou", "Rozsvítím vždy když potřebuji", "Použiji 2, poslední zapálím od starých", "Nejde to"],
			correct: 2
		},
		{
			question: "Na pláži je 20 krabů a vy máte hlad. Kolik krabů si vezmete, když chcete mít jídlo na 3 dny?",
			options: ["6 krabů", "9 krabů", "Všech 20", "Záleží na velikosti"],
			correct: 3
		},
		{
			question: "Musíte překonat propast 5m širokou. Máte 2 prkna po 4,5m. Jak to zvládnete?",
			options: ["Svážu prkna", "Položím křížem přes úzčí místo", "Udělám most ve tvaru V", "Nejde to"],
			correct: 2
		},
		{
			question: "V láhvi je mapa, ale otvor je moc malý. Máte nůž, vodu a oheň. Jak mapu dostanete ven?",
			options: ["Rozbiju láhev", "Nalákat vodou", "Zahřát a láhev praskne", "Vytáhnout po kousku nožem"],
			correct: 0
		},
		{
			question: "Na ostrově je 5 palem. První den spadl 1 kokos, druhý 2, třetí 4. Kolik jich spadne čtvrtý den?",
			options: ["8 kokosů", "7 kokosů", "5 kokosů", "Záleží na větru"],
			correct: 0
		},
		{
			question: "Máte lodičku, která unese max. 100kg. Vy vážíte 80kg a máte 2 sudy po 15kg. Jak se všichni přepravíte?",
			options: ["Nejde to", "Vyhodím 10kg vody ze sudů", "Pojedu 2x", "Přeplavu s jedním sudem"],
			correct: 2
		},
		{
			question: "V truhle je 100 zlatých mincí, ale můžete vzít pouze tolik, kolik odhadnete. Kolik vezmete?",
			options: ["50 mincí", "Všech 100", "Co si spočítám", "75 mincí"],
			correct: 2
		},
		{
			question: "Na ostrově najdete 3 cesty: Jedna vede přes hory (1 den), druhá džunglí (2 dny), třetí po pláži (3 dny). Která je nejbezpečnější?",
			options: ["Hory - nejrychlejší", "Džungle - máte skrýše", "Pláž - vidíte kolem", "Všechny stejně"],
			correct: 3
		}
	];

	let score = 0;
	let correctAnswers = 0;
	let currentIsland = null;
	let selectedOption = null;
	let islandStates = Array(13).fill('unanswered');

	const islandPositions = [
		{ x: 6, y: 8 }, { x: 24, y: 14 }, { x: 48, y: 8 },
		{ x: 72, y: 15 }, { x: 88, y: 6 }, { x: 10, y: 34 },
		{ x: 32, y: 42 }, { x: 58, y: 36 }, { x: 82, y: 45 },
		{ x: 12, y: 62 }, { x: 38, y: 68 }, { x: 65, y: 62 },
		{ x: 84, y: 64 }
	];

	// Mnohem realističtější ostrovy s lepšími tvary a detaily
	const islandShapes = [
		// Ostrov 1 - Klasický tropický ostrov
		`<svg viewBox="0 0 120 120">
                <!-- Stín ve vodě -->
                <ellipse cx="60" cy="90" rx="40" ry="12" fill="rgba(0,50,100,0.2)"/>
                <!-- Písečný břeh -->
                <path d="M 30,85 Q 35,82 45,80 Q 55,78 60,78 Q 65,78 75,80 Q 85,82 90,85 Q 85,87 75,89 Q 65,90 60,90 Q 55,90 45,89 Q 35,87 30,85 Z" fill="#F5DEB3"/>
                <!-- Hlavní ostrov -->
                <path d="M 35,80 Q 38,75 42,72 Q 48,68 55,67 Q 60,66 65,67 Q 72,68 78,72 Q 82,75 85,80 Q 82,82 75,83 Q 65,84 60,84 Q 55,84 45,83 Q 38,82 35,80 Z" fill="#90A955"/>
                <!-- Vegetace -->
                <ellipse cx="50" cy="74" rx="6" ry="7" fill="#5F7A3D"/>
                <ellipse cx="70" cy="75" rx="5" ry="6" fill="#5F7A3D"/>
                <!-- Palma -->
                <line x1="60" y1="72" x2="60" y2="55" stroke="#8B6F47" stroke-width="3" stroke-linecap="round"/>
                <path d="M 60,55 Q 52,50 48,52" stroke="#6B8E23" stroke-width="2.5" fill="none"/>
                <path d="M 60,55 Q 68,50 72,52" stroke="#6B8E23" stroke-width="2.5" fill="none"/>
                <path d="M 60,55 Q 58,48 58,45" stroke="#6B8E23" stroke-width="2" fill="none"/>
            </svg>`,

		// Ostrov 2 - Skalnatý ostrov
		`<svg viewBox="0 0 120 120">
                <ellipse cx="60" cy="92" rx="38" ry="11" fill="rgba(0,50,100,0.2)"/>
                <path d="M 32,87 Q 38,84 48,82 Q 58,81 68,82 Q 78,84 88,87 Q 82,89 72,90 Q 62,91 52,90 Q 42,89 32,87 Z" fill="#D2B48C"/>
                <path d="M 38,82 Q 42,77 48,73 Q 54,69 60,67 Q 66,69 72,73 Q 78,77 82,82 Q 78,84 70,85 Q 60,86 50,85 Q 42,84 38,82 Z" fill="#A0826D"/>
                <!-- Skály -->
                <path d="M 52,70 L 56,60 L 60,58 L 64,60 L 68,70 Q 64,72 60,72 Q 56,72 52,70 Z" fill="#7A6B5D"/>
                <path d="M 56,68 L 58,62 L 60,61 L 62,62 L 64,68 Q 62,69 60,69 Q 58,69 56,68 Z" fill="#8C7A6A"/>
                <!-- Keře -->
                <ellipse cx="46" cy="78" rx="4" ry="4" fill="#6B8E23"/>
                <ellipse cx="74" cy="79" rx="3.5" ry="3.5" fill="#6B8E23"/>
            </svg>`,

		// Ostrov 3 - Písečný atol
		`<svg viewBox="0 0 120 120">
                <ellipse cx="60" cy="90" rx="42" ry="12" fill="rgba(0,50,100,0.2)"/>
                <path d="M 28,85 Q 35,82 46,81 Q 58,80 70,81 Q 81,82 92,85 Q 85,87 74,88 Q 62,89 50,88 Q 39,87 28,85 Z" fill="#F4E4C1"/>
                <path d="M 35,83 Q 42,80 52,79 Q 60,79 68,80 Q 78,81 85,83 Q 78,85 68,86 Q 60,86 52,85 Q 42,84 35,83 Z" fill="#B8D4A0"/>
                <!-- Palmy -->
                <line x1="50" y1="77" x2="50" y2="62" stroke="#A0826D" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M 50,62 Q 44,58 42,60" stroke="#7BA05B" stroke-width="2" fill="none"/>
                <path d="M 50,62 Q 56,58 58,60" stroke="#7BA05B" stroke-width="2" fill="none"/>
                <line x1="70" y1="78" x2="70" y2="65" stroke="#A0826D" stroke-width="2.2" stroke-linecap="round"/>
                <path d="M 70,65 Q 65,61 63,63" stroke="#7BA05B" stroke-width="1.8" fill="none"/>
                <path d="M 70,65 Q 75,61 77,63" stroke="#7BA05B" stroke-width="1.8" fill="none"/>
            </svg>`,

		// Ostrov 4 - Velký lesnatý ostrov
		`<svg viewBox="0 0 120 120">
                <ellipse cx="60" cy="91" rx="44" ry="12" fill="rgba(0,50,100,0.2)"/>
                <path d="M 26,86 Q 34,83 46,82 Q 58,81 70,82 Q 82,83 94,86 Q 86,88 74,89 Q 62,90 50,89 Q 38,88 26,86 Z" fill="#D4C5A9"/>
                <path d="M 32,84 Q 40,80 50,79 Q 60,79 70,80 Q 80,81 88,84 Q 82,86 72,87 Q 60,88 48,87 Q 38,86 32,84 Z" fill="#88A65E"/>
                <!-- Stromy -->
                <ellipse cx="45" cy="78" rx="5" ry="6" fill="#5F7A3D"/>
                <ellipse cx="55" cy="76" rx="6" ry="7" fill="#5F7A3D"/>
                <ellipse cx="60" cy="75" rx="6.5" ry="7.5" fill="#4A6B2E"/>
                <ellipse cx="65" cy="76" rx="6" ry="7" fill="#5F7A3D"/>
                <ellipse cx="75" cy="78" rx="5" ry="6" fill="#5F7A3D"/>
            </svg>`,

		// Ostrov 5 - Horský ostrov
		`<svg viewBox="0 0 120 120">
                <ellipse cx="60" cy="92" rx="40" ry="11" fill="rgba(0,50,100,0.2)"/>
                <path d="M 30,87 Q 38,84 48,83 Q 58,82 68,83 Q 78,84 90,87 Q 82,89 72,90 Q 62,91 52,90 Q 42,89 30,87 Z" fill="#C9B8A3"/>
                <path d="M 36,84 Q 42,78 48,74 Q 54,70 60,68 Q 66,70 72,74 Q 78,78 84,84 Q 78,86 68,87 Q 60,88 52,87 Q 42,86 36,84 Z" fill="#8C7A6A"/>
                <!-- Hora -->
                <path d="M 50,74 L 56,64 L 60,60 L 64,64 L 70,74 Q 65,76 60,76 Q 55,76 50,74 Z" fill="#7A6B5D"/>
                <path d="M 55,72 L 58,66 L 60,64 L 62,66 L 65,72 Q 62,73 60,73 Q 58,73 55,72 Z" fill="#9B8B7E"/>
                <!-- Keř -->
                <ellipse cx="44" cy="80" rx="4" ry="4" fill="#6B8E23"/>
            </svg>`,

		// Ostrov 6 - Malý ostrůvek
		`<svg viewBox="0 0 120 120">
                <ellipse cx="60" cy="90" rx="35" ry="11" fill="rgba(0,50,100,0.2)"/>
                <path d="M 35,85 Q 42,82 52,81 Q 60,81 68,82 Q 78,83 85,85 Q 78,87 68,88 Q 60,88 52,87 Q 42,86 35,85 Z" fill="#F5DEB3"/>
                <path d="M 40,83 Q 46,80 54,79 Q 60,79 66,80 Q 74,81 80,83 Q 74,85 66,86 Q 60,86 54,85 Q 46,84 40,83 Z" fill="#A8C686"/>
                <!-- Palma -->
                <line x1="60" y1="78" x2="60" y2="64" stroke="#9B7653" stroke-width="2.8" stroke-linecap="round"/>
                <path d="M 60,64 Q 53,59 50,61" stroke="#7BA05B" stroke-width="2.2" fill="none"/>
                <path d="M 60,64 Q 67,59 70,61" stroke="#7BA05B" stroke-width="2.2" fill="none"/>
                <path d="M 60,64 Q 58,57 58,54" stroke="#7BA05B" stroke-width="1.8" fill="none"/>
            </svg>`,

		// Ostrov 7 - Tři kopce
		`<svg viewBox="0 0 120 120">
                <ellipse cx="60" cy="91" rx="43" ry="12" fill="rgba(0,50,100,0.2)"/>
                <path d="M 27,86 Q 35,83 47,82 Q 59,81 71,82 Q 83,83 93,86 Q 85,88 73,89 Q 61,90 49,89 Q 37,88 27,86 Z" fill="#D8C9B0"/>
                <path d="M 33,84 Q 38,79 44,76 Q 48,74 52,76 Q 56,78 58,82 L 62,82 Q 64,78 68,76 Q 72,74 76,76 Q 82,79 87,84 Q 82,86 74,87 Q 60,88 46,87 Q 38,86 33,84 Z" fill="#90A76D"/>
                <!-- Vegetace na kopcích -->
                <ellipse cx="48" cy="80" rx="4" ry="4" fill="#5F7A3D"/>
                <ellipse cx="60" cy="79" rx="4.5" ry="5" fill="#5F7A3D"/>
                <ellipse cx="72" cy="80" rx="4" ry="4" fill="#5F7A3D"/>
            </svg>`,

		// Ostrov 8 - Dlouhý úzký ostrov
		`<svg viewBox="0 0 120 120">
                <ellipse cx="60" cy="90" rx="46" ry="11" fill="rgba(0,50,100,0.2)"/>
                <path d="M 24,85 Q 32,82 44,81 Q 56,80 68,81 Q 80,82 96,85 Q 88,87 76,88 Q 64,89 52,88 Q 40,87 24,85 Z" fill="#EFE3C8"/>
                <path d="M 30,83 Q 38,80 50,79 Q 60,79 70,80 Q 82,81 90,83 Q 82,85 70,86 Q 60,86 50,85 Q 38,84 30,83 Z" fill="#9AB973"/>
                <!-- Palmy podél ostrova -->
                <line x1="42" y1="78" x2="42" y2="65" stroke="#A0826D" stroke-width="2.2" stroke-linecap="round"/>
                <path d="M 42,65 Q 37,61 35,63" stroke="#7BA05B" stroke-width="1.7" fill="none"/>
                <path d="M 42,65 Q 47,61 49,63" stroke="#7BA05B" stroke-width="1.7" fill="none"/>
                <line x1="60" y1="77" x2="60" y2="63" stroke="#A0826D" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M 60,63 Q 54,59 52,61" stroke="#7BA05B" stroke-width="2" fill="none"/>
                <path d="M 60,63 Q 66,59 68,61" stroke="#7BA05B" stroke-width="2" fill="none"/>
                <line x1="78" y1="79" x2="78" y2="67" stroke="#A0826D" stroke-width="2" stroke-linecap="round"/>
                <path d="M 78,67 Q 73,63 71,65" stroke="#7BA05B" stroke-width="1.6" fill="none"/>
                <path d="M 78,67 Q 83,63 85,65" stroke="#7BA05B" stroke-width="1.6" fill="none"/>
            </svg>`,

		// Ostrov 9 - Členitý ostrov
		`<svg viewBox="0 0 120 120">
                <ellipse cx="60" cy="91" rx="41" ry="12" fill="rgba(0,50,100,0.2)"/>
                <path d="M 29,86 Q 36,83 46,82 Q 56,81 66,82 Q 76,83 91,86 Q 84,88 74,89 Q 64,90 54,89 Q 44,88 29,86 Z" fill="#DDD3BA"/>
                <path d="M 34,84 Q 40,80 48,78 Q 54,77 58,79 L 62,79 Q 66,77 72,78 Q 80,80 86,84 Q 80,86 72,87 Q 60,88 48,87 Q 40,86 34,84 Z" fill="#8FA76B"/>
                <!-- Vegetace -->
                <ellipse cx="48" cy="81" rx="5" ry="5" fill="#5F7A3D"/>
                <!-- Palma -->
                <line x1="70" y1="80" x2="70" y2="66" stroke="#9B7653" stroke-width="2.5" stroke-linecap="round"/>
                <path d="M 70,66 Q 64,62 62,64" stroke="#7BA05B" stroke-width="2" fill="none"/>
                <path d="M 70,66 Q 76,62 78,64" stroke="#7BA05B" stroke-width="2" fill="none"/>
            </svg>`,

		// Ostrov 10 - Hustá džungle
		`<svg viewBox="0 0 120 120">
                <ellipse cx="60" cy="91" rx="42" ry="12" fill="rgba(0,50,100,0.2)"/>
                <path d="M 28,86 Q 36,83 48,82 Q 60,81 72,82 Q 84,83 92,86 Q 84,88 72,89 Q 60,90 48,89 Q 36,88 28,86 Z" fill="#CFC5AD"/>
                <path d="M 34,84 Q 42,80 52,79 Q 60,79 68,80 Q 78,81 86,84 Q 80,86 70,87 Q 60,88 50,87 Q 40,86 34,84 Z" fill="#7A9B4D"/>
                <!-- Hustá vegetace -->
                <ellipse cx="42" cy="79" rx="5" ry="6" fill="#5F7A3D"/>
                <ellipse cx="50" cy="77" rx="6" ry="7" fill="#4A6B2E"/>
                <ellipse cx="58" cy="76" rx="6.5" ry="7.5" fill="#5F7A3D"/>
                <ellipse cx="62" cy="76" rx="6" ry="7" fill="#4A6B2E"/>
                <ellipse cx="70" cy="77" rx="6" ry="7" fill="#5F7A3D"/>
                <ellipse cx="78" cy="79" rx="5" ry="6" fill="#5F7A3D"/>
            </svg>`,

		// Ostrov 11 - Skalnaté útesy
		`<svg viewBox="0 0 120 120">
                <ellipse cx="60" cy="92" rx="39" ry="11" fill="rgba(0,50,100,0.2)"/>
                <path d="M 31,87 Q 38,84 48,83 Q 58,82 68,83 Q 78,84 89,87 Q 82,89 72,90 Q 62,91 52,90 Q 42,89 31,87 Z" fill="#C9BBA8"/>
                <path d="M 37,84 Q 42,79 48,75 Q 54,72 60,71 Q 66,72 72,75 Q 78,79 83,84 Q 78,86 70,87 Q 60,88 50,87 Q 42,86 37,84 Z" fill="#8C7A6A"/>
                <!-- Skály -->
                <path d="M 50,76 L 54,68 L 58,66 L 60,65 L 62,66 L 66,68 L 70,76 Q 66,78 60,78 Q 54,78 50,76 Z" fill="#7A6B5D"/>
                <path d="M 54,74 L 56,70 L 60,69 L 64,70 L 66,74 Q 64,75 60,75 Q 56,75 54,74 Z" fill="#9B8B7E"/>
                <!-- Malé keře -->
                <ellipse cx="45" cy="81" rx="3.5" ry="3.5" fill="#6B8E23"/>
                <ellipse cx="75" cy="82" rx="3" ry="3" fill="#6B8E23"/>
            </svg>`,

		// Ostrov 12 - Luxusní resort
		`<svg viewBox="0 0 120 120">
                <ellipse cx="60" cy="90" rx="43" ry="12" fill="rgba(0,50,100,0.2)"/>
                <path d="M 27,85 Q 35,82 47,81 Q 59,80 71,81 Q 83,82 93,85 Q 85,87 73,88 Q 61,89 49,88 Q 37,87 27,85 Z" fill="#F7EDD8"/>
                <path d="M 33,83 Q 41,79 51,78 Q 60,78 69,79 Q 79,80 87,83 Q 81,85 71,86 Q 60,87 49,86 Q 39,85 33,83 Z" fill="#A3C57D"/>
                <!-- Krásné palmy -->
                <line x1="48" y1="76" x2="48" y2="60" stroke="#A67C52" stroke-width="3" stroke-linecap="round"/>
                <path d="M 48,60 Q 40,55 37,57" stroke="#7BA05B" stroke-width="2.5" fill="none"/>
                <path d="M 48,60 Q 42,56 39,60" stroke="#7BA05B" stroke-width="2" fill="none"/>
                <path d="M 48,60 Q 56,55 59,57" stroke="#7BA05B" stroke-width="2.5" fill="none"/>
                <path d="M 48,60 Q 54,56 57,60" stroke="#7BA05B" stroke-width="2" fill="none"/>
                <line x1="72" y1="77" x2="72" y2="62" stroke="#A67C52" stroke-width="2.8" stroke-linecap="round"/>
                <path d="M 72,62 Q 65,57 62,59" stroke="#7BA05B" stroke-width="2.3" fill="none"/>
                <path d="M 72,62 Q 79,57 82,59" stroke="#7BA05B" stroke-width="2.3" fill="none"/>
                <path d="M 72,62 Q 70,55 70,52" stroke="#7BA05B" stroke-width="1.9" fill="none"/>
            </svg>`,

		// Ostrov 13 - Tajemný opuštěný ostrov
		`<svg viewBox="0 0 120 120">
                <ellipse cx="60" cy="91" rx="40" ry="11" fill="rgba(0,50,100,0.2)"/>
                <path d="M 30,86 Q 37,83 48,82 Q 58,81 68,82 Q 79,83 90,86 Q 83,88 72,89 Q 62,90 52,89 Q 41,88 30,86 Z" fill="#C5B8A1"/>
                <path d="M 36,83 Q 42,79 50,77 Q 58,76 66,77 Q 74,79 84,83 Q 78,85 68,86 Q 60,87 52,86 Q 42,85 36,83 Z" fill="#7D8C5E"/>
                <!-- Tajemný kopec -->
                <path d="M 48,78 L 54,70 L 58,67 L 60,66 L 62,67 L 66,70 L 72,78 Q 68,80 60,80 Q 52,80 48,78 Z" fill="#7A6B5D"/>
                <path d="M 52,76 L 56,71 L 60,69 L 64,71 L 68,76 Q 65,77 60,77 Q 55,77 52,76 Z" fill="#8C7A6A"/>
                <!-- Stará nakloněná palma -->
                <path d="M 50,80 Q 45,76 42,74" stroke="#7D6B51" stroke-width="2.5" stroke-linecap="round" fill="none" opacity="0.7"/>
                <path d="M 42,74 Q 38,72 36,73" stroke="#6B7A47" stroke-width="1.5" fill="none" opacity="0.6"/>
                <!-- Živá vegetace -->
                <ellipse cx="70" cy="81" rx="4" ry="4" fill="#5F7A3D"/>
            </svg>`
	];

	function createIslands() {
		const container = document.getElementById('islands');

		// Detekce mobilního zařízení
		const isMobile = window.innerWidth <= 768;

		// Mobilní pozice - menší a hustěji uspořádané
		const mobilePositions = [
			{ x: 8, y: 12 }, { x: 28, y: 18 }, { x: 50, y: 12 },
			{ x: 72, y: 18 }, { x: 88, y: 10 }, { x: 10, y: 35 },
			{ x: 32, y: 42 }, { x: 58, y: 38 }, { x: 82, y: 45 },
			{ x: 15, y: 62 }, { x: 42, y: 68 }, { x: 68, y: 63 },
			{ x: 85, y: 72 }
		];

		const positions = isMobile ? mobilePositions : islandPositions;

		positions.forEach((pos, index) => {
			const island = document.createElement('div');
			island.className = 'island unanswered';
			island.style.left = `${pos.x}%`;
			island.style.top = `${pos.y}%`;
			island.innerHTML = `
                    ${islandShapes[index]}
                    <div class="status-mark"></div>
                    <div class="island-number">${islandLabels[index]}</div>
                `;
			island.addEventListener('click', () => openChallenge(index));
			container.appendChild(island);
		});
	}

	function openChallenge(islandIndex) {
		if (islandStates[islandIndex] !== 'unanswered') return;

		currentIsland = islandIndex;
		selectedOption = null;

		const challenge = challenges[islandIndex];
		document.getElementById('modalHeader').textContent = islandLabels[islandIndex];
		document.getElementById('question').textContent = challenge.question;

		const optionsContainer = document.getElementById('options');
		optionsContainer.innerHTML = '';

		challenge.options.forEach((option, index) => {
			const optionDiv = document.createElement('div');
			optionDiv.className = 'option';
			optionDiv.textContent = option;
			optionDiv.addEventListener('click', () => selectOption(index));
			optionsContainer.appendChild(optionDiv);
		});

		document.getElementById('submitBtn').disabled = true;
		document.getElementById('modal').classList.add('active');
	}

	function selectOption(index) {
		selectedOption = index;
		document.querySelectorAll('.option').forEach((opt, i) => {
			opt.classList.toggle('selected', i === index);
		});
		document.getElementById('submitBtn').disabled = false;
	}

	function submitAnswer() {
		if (selectedOption === null) return;

		const challenge = challenges[currentIsland];
		const isCorrect = selectedOption === challenge.correct;

		if (isCorrect) {
			score += 10;
			correctAnswers++;
			islandStates[currentIsland] = 'correct';
		} else {
			islandStates[currentIsland] = 'wrong';
		}

		updateScore();
		updateIslandAppearance(currentIsland);

		// Zavřít modal okamžitě
		closeModal();

		// Zkontrolovat dokončení hry
		if (correctAnswers === 13) {
			setTimeout(() => {
				alert(`🎊 Gratulujeme! Dokončili jste všechny ostrovy!\n\nVaše skóre: ${score} bodů\nSprávných odpovědí: ${correctAnswers}/13`);
			}, 500);
		}
	}

	function updateScore() {
		document.getElementById('score').textContent = score;
		document.getElementById('correct').textContent = correctAnswers;
	}

	function updateIslandAppearance(index) {
		const islands = document.querySelectorAll('.island');
		const island = islands[index];
		island.className = `island ${islandStates[index]}`;

		// Změna barev SVG podle stavu
		const svg = island.querySelector('svg');
		if (islandStates[index] === 'correct') {
			svg.querySelectorAll('path[fill="#D4A574"], path[fill="#E8D4A0"]').forEach(el => el.setAttribute('fill', '#C8E6C9'));
			svg.querySelectorAll('path[fill="#8FB085"], path[fill="#A5C79C"], path[fill="#7A8B7A"], path[fill="#8B7A6A"]').forEach(el => el.setAttribute('fill', '#4CAF50'));
			svg.querySelectorAll('circle[fill="#4A7C59"]').forEach(el => el.setAttribute('fill', '#2E7D32'));
			svg.querySelectorAll('rect[fill="#6B4423"]').forEach(el => el.setAttribute('fill', '#1B5E20'));
			svg.querySelectorAll('path[stroke="#8B6F47"]').forEach(el => el.setAttribute('stroke', '#1B5E20'));
			svg.querySelectorAll('path[stroke="#6B8E23"]').forEach(el => el.setAttribute('stroke', '#2E7D32'));
			svg.querySelectorAll('path[fill="#6B6B6B"]').forEach(el => el.setAttribute('fill', '#2E7D32'));
			svg.querySelectorAll('path[fill="#8C8C8C"]').forEach(el => el.setAttribute('fill', '#4CAF50'));
		} else if (islandStates[index] === 'wrong') {
			svg.querySelectorAll('path[fill="#D4A574"], path[fill="#E8D4A0"]').forEach(el => el.setAttribute('fill', '#FFCDD2'));
			svg.querySelectorAll('path[fill="#8FB085"], path[fill="#A5C79C"], path[fill="#7A8B7A"], path[fill="#8B7A6A"]').forEach(el => el.setAttribute('fill', '#F44336'));
			svg.querySelectorAll('circle[fill="#4A7C59"]').forEach(el => el.setAttribute('fill', '#C62828'));
			svg.querySelectorAll('rect[fill="#6B4423"]').forEach(el => el.setAttribute('fill', '#B71C1C'));
			svg.querySelectorAll('path[stroke="#8B6F47"]').forEach(el => el.setAttribute('stroke', '#B71C1C'));
			svg.querySelectorAll('path[stroke="#6B8E23"]').forEach(el => el.setAttribute('stroke', '#C62828'));
			svg.querySelectorAll('path[fill="#6B6B6B"]').forEach(el => el.setAttribute('fill', '#C62828'));
			svg.querySelectorAll('path[fill="#8C8C8C"]').forEach(el => el.setAttribute('fill', '#F44336'));
		}
	}

	function closeModal() {
		document.getElementById('modal').classList.remove('active');
	}

	document.getElementById('submitBtn').addEventListener('click', submitAnswer);

	createIslands();
</script>
</body>
</html>