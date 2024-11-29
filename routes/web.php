<?php

use App\Http\Controllers\Web;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('web.homepage');
});

Route::get('/phishing-template', function () {
    return view('web.phishing');
});

Route::prefix('quiz')->group(function () {
    Route::get('', [Web\Quiz\QuizRunController::class, 'run'])
        ->name('web.quiz.run');

    Route::get('/age-list', [Web\Quiz\AgeListController::class, 'index'])
        ->name('web.quiz.age-list');

    Route::post('/answer', [Web\Quiz\AnswerController::class, 'store'])
        ->name('web.quiz.answer');

    Route::post('/question', [Web\Quiz\QuestionController::class, 'index'])
        ->name('web.quiz.question');

    Route::post('/respondent/identification', [Web\Quiz\RespondentIdentificationController::class, 'store'])
        ->name('web.quiz.respondent.identification');

    Route::post('/respondent/summary', [Web\Quiz\RespondentSummaryController::class, 'index'])
        ->name('web.quiz.respondent.summary');
});
