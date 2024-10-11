<?php
namespace App\Models;

use Database\Factories\AgeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 */
class Age extends Model
{
    /** @use HasFactory<AgeFactory> */
    use HasFactory;

    protected $table = 'ages';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
    ];
}
