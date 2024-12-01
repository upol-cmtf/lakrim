<?php
namespace App\Http\Resources\Web;

use App\Models\QuestionGroup;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $perex
 * @property string $description
 * @property QuestionGroup $questionGroup
 * @method Collection<QuestionOption> getOptions()
 */
class QuestionResource extends JsonResource
{
    /**
     * @return array{
     *     id: int,
     *     perex: string,
     *     description: string,
     * }
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'perex' => $this->perex,
            'description' => $this->description,
            'options' => QuestionOptionsResource::collection($this->getOptions()),
            'group' => [
                'id' => $this->questionGroup->id,
                'name' => $this->questionGroup->name,
            ],
        ];
    }
}
