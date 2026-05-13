<?php
namespace App\Services\Respondent;

use App\Models\Island;
use App\Models\Respondent;
use Illuminate\Database\Eloquent\Relations\Relation;

class SituationsResolver
{
    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     image: string,
     *     situations: list<array{
     *         id: int,
     *         position: int,
     *         title: string|null,
     *         completed: bool,
     *     }>,
     * }>
     */
    public function resolve(Respondent $respondent): array
    {
        /** @var int[] $completedSituationIds */
        $completedSituationIds = $respondent->situations()
            ->whereNotNull('completed_at')
            ->pluck('situation_id')
            ->all();

        $islands = Island::query()
            ->with(['situations' => fn(Relation $q) => $q->orderBy('position')])
            ->orderBy('id')
            ->get();

        $result = [];
        foreach ($islands as $island) {
            $situations = [];
            foreach ($island->situations as $situation) {
                $situations[] = [
                    'id' => $situation->id,
                    'position' => $situation->position,
                    'title' => $situation->title,
                    'completed' => in_array($situation->id, $completedSituationIds, true),
                ];
            }

            $result[] = [
                'id' => $island->id,
                'name' => $island->name,
                'image' => $island->image,
                'situations' => $situations,
            ];
        }

        return $result;
    }
}
