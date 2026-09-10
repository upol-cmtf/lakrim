<?php
namespace App\Http\Controllers\Admin;

use App\Http\XlsxHeaders;
use App\Models\QuizEvent;
use App\Services\Export\QuestionsResultsExporter;
use App\Services\XlsxWriterFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportQuestionsResultsController
{
    use XlsxHeaders;

    public function __construct(
        private readonly QuestionsResultsExporter $exporter,
        private readonly XlsxWriterFactory $xlsxWriterFactory,
    ) {
    }

    /**
     * Export všech výsledků, nebo jen respondentů z událostí (kurzů) daného názvu,
     * je-li v query předán parametr `event` (např. ?event=Studenti+2026/2027).
     * Události se stejným názvem (různé hashe) se exportují dohromady.
     *
     * Pro velké objemy dat (riziko timeoutu webového serveru) slouží konzolový
     * příkaz `php artisan export:questions-results`, který soubor uloží na disk.
     */
    public function index(Request $request): StreamedResponse
    {
        $request->validate([
            'event' => ['nullable', 'string', 'exists:' . QuizEvent::class . ',name'],
        ]);

        $eventName = $request->filled('event') ? $request->string('event')->toString() : null;

        $writer = $this->xlsxWriterFactory->create($this->exporter->build($eventName));

        return response()->stream(
            fn() => $writer->save('php://output'),
            Response::HTTP_OK,
            $this->getXlsxHeaders($this->exporter->fileName($eventName)),
        );
    }
}
