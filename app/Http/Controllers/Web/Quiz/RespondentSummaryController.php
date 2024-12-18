<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Http\Controllers\Web\ApiController;
use App\Http\Resources\Web\ArrayResource;
use App\Models\Respondent;
use App\Services\Respondent\Statistics;
use Illuminate\Http\Request;

class RespondentSummaryController extends ApiController
{
    public function __construct(
        Request $request,
        private readonly Statistics $statistics,
    ) {
        parent::__construct($request);
    }

    public function index(): ArrayResource
    {
        $this->validate($this->request, [
            'respondent_token' => 'required|string|exists:respondents,token',
        ]);

        $respondent = Respondent::where('token', $this->request->input('respondent_token'))->first();
        assert($respondent instanceof Respondent);

        return new ArrayResource([
            'statistics' => $this->statistics->setRespondent($respondent)->getStatistics(),
            'evaluation' => $respondent->getFinalSummary(),
        ]);
    }
}
