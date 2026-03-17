<?php

use App\Http\Controllers\Web;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('web.homepage'));

Route::get('/kviz/{quizEvent:hash?}', [Web\Quiz\HomepageController::class, 'index'])
    ->name('web.quiz.homepage');

Route::get('/pexeso/{quizEvent:hash?}', [Web\QuizGrid\HomepageController::class, 'index'])
    ->name('web.quiz-grid.homepage');

Route::get('/ostrov/{quizEvent:hash?}', [Web\IslandGame\HomepageController::class, 'index'])
    ->name('web.island-game.homepage');


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
});

### Version 1
Route::prefix('v1')->group(function () {
    Route::prefix('kviz')->group(function () {
        Route::get('/dokonceni', fn() => view('web.quiz.finish'));

        Route::post('/respondent/summary', [Web\Quiz\RespondentSummaryController::class, 'index'])
            ->name('web.quiz.respondent.summary');

        Route::get('{quizEvent:hash?}', [Web\Quiz\QuizRunController::class, 'run'])
            ->name('web.quiz.run');
    });
});
