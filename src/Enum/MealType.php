<?php
namespace App\Enum;

use App\Traits\EnumsToArray;

enum MealType: string {
    use EnumsToArray;
    
    case ALL = 'ALL';
    case VEGAN = 'VEGAN';
    case VEGETARIAN = 'VEGETARIAN';
}