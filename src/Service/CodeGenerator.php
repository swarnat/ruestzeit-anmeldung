<?php

namespace App\Service;

use Symfony\Component\Uid\Uuid;

class CodeGenerator
{
    public function generate_random($length): string
    {
        $vowels = ['a', 'e', 'i', 'o', 'u'];
        $consonants = ['b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'];
        $numbers = ['2', '3', '4', '5', '6', '7', '8', '9']; // Vermeidung von 0 und 1

        $randomString = "";

        for ($i = 0; $i < $length; $i++) {
            if ($i % 3 == 2) {
                // Ziffer hinzufügen
                $randomString .= $numbers[array_rand($numbers)];
            } elseif ($i % 2 == 0) {
                // Konsonant hinzufügen
                $randomString .= $consonants[array_rand($consonants)];
            } else {
                // Vokal hinzufügen
                $randomString .= $vowels[array_rand($vowels)];
            }
        }

        return $randomString;

    }
    public function generate($codeLength): string
    {
        $syllables = [
            'ba', 'be', 'bi', 'bo', 'bu',
            'da', 'de', 'di', 'do', 'du',
            'ka', 'ke', 'ki', 'ko', 'ku',
            'la', 'le', 'li', 'lo', 'lu',
            'ma', 'me', 'mi', 'mo', 'mu',
            'na', 'ne', 'ni', 'no', 'nu',
            'pa', 'pe', 'pi', 'po', 'pu',
            'ra', 're', 'ri', 'ro', 'ru',
            'sa', 'se', 'si', 'so', 'su',
            'ta', 'te', 'ti', 'to', 'tu',
            'va', 've', 'vi', 'vo', 'vu'
        ];


        $randomString = '';

        if($codeLength > 4 && mt_rand(0,1000) > 600) {
            $randomString .= $syllables[array_rand($syllables)];
            $randomString .= $syllables[array_rand($syllables)];
            $randomString .= $this->generate_random($codeLength - 4);
        } elseif($codeLength > 3 && mt_rand(0,1000) > 600) {
            $randomString .= $this->generate_random(3);
            $randomString .= mt_rand(100,999);
            
        } elseif($codeLength > 4 && mt_rand(0,1000) > 800) {
            $randomString .= $this->generate_random($codeLength - 4);
            $randomString .= mt_rand(1000,9999);
        } else {
            $randomString .= $this->generate_random($codeLength);
        }


        return $randomString;
    }
}
