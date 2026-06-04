<?php
namespace App\Http\Resources\Web;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @method string renderedDescription()
 * @method string|null renderedEvaluation()
 */
class EasterEggResource extends JsonResource
{
    /**
     * @return array{
     *     id: int,
     *     description: string,
     *     evaluation: string|null,
     * }
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->renderedDescription(),
            'evaluation' => $this->renderedEvaluation(),
        ];
    }
}
