<?php
namespace App\Enum;

use App\Traits\EnumsToArray;

enum PersonenTyp: string {
    use EnumsToArray;
    
    case MITARBEITER = 'MITARBEITER';
    case TEILNEHMER = 'TEILNEHMER';
    case REFERENT = 'REFERENT';
}