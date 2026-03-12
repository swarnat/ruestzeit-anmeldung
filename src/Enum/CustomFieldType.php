<?php

namespace App\Enum;

enum CustomFieldType: string
{
    case INPUT = 'input';
    case TEXTAREA = 'textarea';
    case DATE = 'date';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';
    case READONLY_TEXT = 'readonly_text';
}
