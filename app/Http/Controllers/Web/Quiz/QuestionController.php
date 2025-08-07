<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Enums\Version;
use App\Exceptions\MaximumQuestionsExceededException;
use App\Exceptions\QuestionNotFoundException;
use App\Http\Controllers\Web\ApiController;
use App\Http\Resources\Web\ArrayResource;
use App\Http\Resources\Web\QuestionResource;
use App\Models\Respondent;
use App\Services\Quiz\QuizQuestionService;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuestionController extends ApiController
{
    public function __construct(
        Request $request,
        private readonly Translator $translator,
        private readonly QuizQuestionService $questionService,
    ) {
        parent::__construct($request);
    }

    public function index(): ArrayResource|QuestionResource|JsonResponse
    {
        $this->validate($this->request, [
            'respondent_token' => 'required|string|exists:respondents,token',
            'version' => 'integer',
        ]);

        $version = $this->request->input('version', '1');
        assert(is_string($version));

        $version = Version::from((int) $version);

        $respondent = Respondent::where('token', $this->request->input('respondent_token'))->first();
        assert($respondent instanceof Respondent);

        try {
            $question = $this->questionService->getQuestionForRespondent($respondent, $version);
        } catch (MaximumQuestionsExceededException $e) {
            return (new ArrayResource([
                'code' => 'maximum_questions_exceeded',
                'error' => $this->translator->get($e->getMessage()),
            ]))
                ->response()
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        } catch (QuestionNotFoundException) {
            return (new ArrayResource([
                'code' => 'question_not_found',
                'error' => $this->translator->get('quiz.question_not_found'),
            ]))
                ->response()
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return new QuestionResource($question);
    }
}
