<?php
namespace App\Http\Controllers\Web\IslandGame;

use App\Http\Controllers\Web\ApiController;
use App\Http\Resources\Web\ArrayResource;
use App\Http\Resources\Web\SituationResource;
use App\Models\Island;
use App\Models\Respondent;
use App\Services\IslandGame\SituationSelector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class SituationController extends ApiController
{
    public function __construct(
        Request $request,
        private readonly SituationSelector $situationSelector,
    ) {
        parent::__construct($request);
    }

    public function show(): SituationResource|JsonResponse
    {
        $this->validate($this->request, [
            'respondent_token' => 'required|string|exists:respondents,token',
            'island_id' => 'required|integer|exists:islands,id',
            'button' => 'required|integer|min:1|max:5',
        ]);

        $respondent = Respondent::where('token', $this->request->input('respondent_token'))->first();
        assert($respondent instanceof Respondent);

        $island = Island::findOrFail($this->request->integer('island_id'));

        $situation = $this->situationSelector->select(
            $respondent,
            $island,
            $this->request->integer('button'),
        );

        if ($situation === null) {
            return (new ArrayResource(['code' => 'no_situation_available']))
                ->response()
                ->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        return new SituationResource($situation);
    }
}
