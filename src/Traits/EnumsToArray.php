<?php

namespace App\Traits;

use Symfony\Component\Translation\TranslatableMessage;

trait EnumsToArray
{
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return array_combine(self::names(), self::translatedValues());
    }

    public static function translatedValues(): array
    {

        return array_combine(
            self::names(),
            array_map(fn($value): TranslatableMessage => \Symfony\Component\Translation\t($value, [], 'messages'), self::values())
        );

    }
    
    public static function stringValues(): array
    {
        return array_map(fn($value): string => (string)$value, self::values());
    }


}
