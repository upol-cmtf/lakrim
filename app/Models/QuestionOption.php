<?php
namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $weight
 * @property string $evaluation
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class QuestionOption extends Model
{
    protected $table = 'questions_options';
}
