<?php
namespace App\Console\Commands;

use App\Services\Export\QuestionsResultsExporter;
use Illuminate\Console\Command;

/**
 * Export výsledků dle otázek do XLSX souboru na disku. Stejný výstup jako webový
 * export v administraci, ale bez omezení timeoutem webového serveru; hodí se pro
 * velký počet respondentů.
 */
class ExportQuestionsResultsCommand extends Command
{
    protected $signature = 'export:questions-results
        {--event= : Jen respondenti z událostí (kurzů) tohoto názvu, např. "Studenti 2026/2027"}
        {--dir= : Cílová složka (výchozí storage/app/exports)}';

    protected $description = 'Export výsledků dle otázek do XLSX souboru (volitelně jen pro událost daného názvu)';

    public function __construct(
        private readonly QuestionsResultsExporter $exporter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $eventName = $this->option('event');
        $eventName = is_string($eventName) && $eventName !== '' ? $eventName : null;

        if ($eventName !== null && !$this->exporter->eventNameExists($eventName)) {
            $this->error(sprintf('Událost s názvem „%s“ neexistuje.', $eventName));

            return self::FAILURE;
        }

        $directory = $this->option('dir');
        $directory = is_string($directory) && $directory !== '' ? $directory : storage_path('app/exports');

        $this->info($eventName === null
            ? 'Exportuji výsledky všech respondentů…'
            : sprintf('Exportuji výsledky respondentů události „%s“…', $eventName));

        $path = $this->exporter->saveToDirectory($directory, $eventName);

        $this->info('Hotovo: ' . $path);

        return self::SUCCESS;
    }
}
