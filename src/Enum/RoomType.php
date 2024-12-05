<?php
namespace App\Enum;

use App\Traits\EnumsToArray;

enum RoomType: string {
    use EnumsToArray;
    
    case ROOM_SINGLE = 'SINGLE';
    case ROOM_DOUBLE = 'DOUBLE';
    case ROOM_FAMILY = 'FAMILY';
}