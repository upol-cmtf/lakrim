<?php
namespace App\Services\IslandGame;

/**
 * Jedna odpověď respondenta na první pokus (všechny zvolené možnosti jedné otázky),
 * jak ji zpětně skládá GameStatistics při přehrávání adaptivního mechanismu.
 */
final class FirstAttemptAnswer
{
    /** @var list<bool> správnost jednotlivých zvolených možností v pořadí uložení */
    public array $rights = [];

    public function __construct(
        public readonly int $respondentId,
        public readonly bool $bonus,
        public readonly int $difficulty,
    ) {
    }

    /**
     * Odpověď je chybná, pokud hráč zvolil alespoň jednu nesprávnou možnost.
     */
    public function isWrong(): bool
    {
        return in_array(false, $this->rights, true);
    }
}
