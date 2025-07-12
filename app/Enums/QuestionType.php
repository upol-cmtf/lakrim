<?php
namespace App\Enums;

enum QuestionType: string
{
    case Select = 'select';
    case MultiSelect = 'multiselect';
}
