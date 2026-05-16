<?php
namespace App\Http\Resources\Web;

use App\Enums\QuestionType;
use App\Models\QuestionGroup;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $perex
 * @property string $description
 * @property mixed[]|null $settings
 * @property QuestionType $type
 * @property QuestionGroup $questionGroup
 * @method Collection<QuestionOption> getOptions()
 * @method string renderedDescription()
 */
class QuestionResource extends JsonResource
{
    /**
     * @return array{
     *     id: int,
     *     perex: string,
     *     description: string,
     *     type: string,
     *     options: AnonymousResourceCollection<QuestionOption>,
     *     settings: mixed[]|null,
     *     group: array{
     *         id: int,
     *         name: string,
     *     }
     * }
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'perex' => $this->perex,
            'description' => $this->renderedDescription(),
            'type' => $this->type->value,
            'options' => QuestionOptionsResource::collection($this->getOptions()),
            'settings' => $this->settings,
            'group' => [
                'id' => $this->questionGroup->id,
                'name' => $this->questionGroup->name,
            ],
        ];
    }
}
