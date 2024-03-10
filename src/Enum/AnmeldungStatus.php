<?php
namespace App\Enum;

enum AnmeldungStatus: string {
    case OPEN = 'OPEN';
    case ACTIVE = 'ACTIVE';
    case WAITLIST = 'WAITLIST';
    case CANCEL = 'CANCEL';
}