#!/usr/bin/env python3
"""
Simulace dvou variant adaptivního výběru obtížnosti v Dobrodružné výpravě.

Varianta A – „série“ (prototyp, commit cf7fbd7, 17. 5. 2026):
    cílová obtížnost podle počtu po sobě jdoucích správných odpovědí;
    chyba sérii nuluje (prahy 2 a 4).
Varianta B – „kumulativní skóre“ (finální, commit 22ab993, 18. 6. 2026):
    skóre +1 za správnou, −1 za špatnou odpověď na první pokus, nikdy pod 0
    (prahy 2 a 4).

Model hráče je záměrně jednoduchý: každou situaci zodpoví správně na první pokus
s pevnou pravděpodobností p, nezávisle na obtížnosti. Hra má 20 kamenů. Cílová
obtížnost se pro každý kámen počítá z předchozích odpovědí, stejně jako v kódu
(SituationSelector::targetDifficulty).

Spuštění: python3 docs/simulace/adaptivita.py
"""
import random
from statistics import mean

STONES = 20
RUNS = 20_000
SEED = 2026
THRESHOLD_MEDIUM = 2
THRESHOLD_HARD = 4


def target(value: int) -> int:
    if value >= THRESHOLD_HARD:
        return 3
    if value >= THRESHOLD_MEDIUM:
        return 2
    return 1


def simulate(p: float, variant: str, rng: random.Random) -> dict:
    share = [0, 0, 0]          # podíl kamenů na obtížnosti 1/2/3
    changes = 0                # počet změn cílové obtížnosti během hry
    hard_drops = 0             # propad o 2 úrovně (3 → 1) v jednom kroku
    first_hard = []            # index kamene, kdy hráč poprvé dostal obtížnost 3
    for _ in range(RUNS):
        state = 0
        previous = 1
        reached_hard = None
        for stone in range(STONES):
            level = target(state)
            share[level - 1] += 1
            if level != previous:
                changes += 1
                if previous == 3 and level == 1:
                    hard_drops += 1
            if level == 3 and reached_hard is None:
                reached_hard = stone + 1
            previous = level
            correct = rng.random() < p
            if variant == "A":
                state = state + 1 if correct else 0
            else:
                state = max(0, state + (1 if correct else -1))
        if reached_hard is not None:
            first_hard.append(reached_hard)
    total = RUNS * STONES
    return {
        "share": [round(100 * s / total, 1) for s in share],
        "changes": round(changes / RUNS, 2),
        "hard_drops": round(hard_drops / RUNS, 2),
        "reached_hard_pct": round(100 * len(first_hard) / RUNS, 1),
        "first_hard": round(mean(first_hard), 1) if first_hard else None,
    }


def main() -> None:
    print(f"kamenů: {STONES}, běhů na scénář: {RUNS}, prahy: {THRESHOLD_MEDIUM}/{THRESHOLD_HARD}\n")
    header = ("p", "var.", "podíl obt. 1/2/3 [%]", "změn úrovně/hra", "propadů 3→1/hra", "dosáhlo obt. 3 [%]", "1. kámen s obt. 3")
    print(" | ".join(header))
    for p in (0.5, 0.7, 0.9):
        for variant in ("A", "B"):
            rng = random.Random(SEED)
            r = simulate(p, variant, rng)
            print(" | ".join(str(x) for x in (
                p, variant, "/".join(str(s) for s in r["share"]), r["changes"],
                r["hard_drops"], r["reached_hard_pct"], r["first_hard"],
            )))


if __name__ == "__main__":
    main()
