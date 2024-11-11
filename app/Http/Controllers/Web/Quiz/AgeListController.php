<?php
namespace App\Http\Controllers\Web\Quiz;

use App\Http\Controllers\Web\ApiController;
use App\Http\Resources\Web\AgeResource;
use App\Models\Age;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AgeListController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return AgeResource::collection(Age::all());
    }
}
