<?php
namespace App\Http\Controllers\Web;

use App\Http\Resources\Web\ArrayResource;

class SavedGameController extends ApiController
{
    private const COOKIE_NAME = 'lakrim-saved-game';

    public function index(): ArrayResource
    {
        $value = $this->request->cookie(self::COOKIE_NAME);

        return new ArrayResource([
            'has_saved_game' => is_string($value) && $value !== '',
        ]);
    }
}
