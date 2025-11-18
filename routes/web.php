<?php

use App\Http\Controllers\Web;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('web.quiz-grid.homepage'));

Route::prefix('kviz')->group(function () {
    Route::get('/dokonceni', fn() => view('web.quiz.finish'))
        ->name('web.quiz.finish');

    Route::get('/podekovani', fn() => view('web.quiz.thank-you'))
        ->name('web.quiz.thank-you');

    Route::get('/age-list', [Web\Quiz\AgeListController::class, 'index'])
        ->name('web.quiz.age-list');

    Route::post('/answer', [Web\Quiz\AnswerController::class, 'store'])
        ->name('web.quiz.answer');

    Route::post('/question', [Web\Quiz\QuestionController::class, 'index'])
        ->name('web.quiz.question');

    Route::post('/respondent/student-id', [Web\Quiz\RespondentIdentificationController::class, 'storeStudentId'])
        ->name('web.quiz.respondent.student-id');

    Route::post('/respondent/identification', [Web\Quiz\RespondentIdentificationController::class, 'store'])
        ->name('web.quiz.respondent.identification');

    Route::post('/respondent/summary', [Web\Quiz\RespondentSummaryController::class, 'index'])
        ->name('web.quiz.respondent.summary');

    Route::get('{quizEvent:hash?}', [Web\Quiz\QuizRunController::class, 'runTiles'])
        ->name('web.quiz-grid.index');
});
