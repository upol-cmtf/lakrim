<?php
namespace App\Http\Resources\Web;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $position
 * @property string|null $title
 * @property Question $question
 */
class SituationResource extends JsonResource
{
    /**
     * @return array{
     *     id: int,
     *     position: int,
     *     title: string|null,
     *     question: QuestionResource,
     * }
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'title' => $this->title,
            'question' => new QuestionResource($this->question),
        ];
    }
}
