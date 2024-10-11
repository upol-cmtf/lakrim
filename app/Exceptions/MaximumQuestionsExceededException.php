<?php
namespace App\Exceptions;

use Exception;

class MaximumQuestionsExceededException extends Exception
{
    /** @var string */
    protected $message = 'questions.maximum_questions_exceeded';
}
