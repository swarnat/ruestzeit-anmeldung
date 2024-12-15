<?php

namespace App\Service;

use App\Generator\CurrentRuestzeitGenerator;
use Symfony\Contracts\Translation\TranslatorInterface;

class DynamicTranslationService implements TranslatorInterface
{
    private TranslatorInterface $translator;

    private $termCache = null;

    public function __construct(
        TranslatorInterface $translator,
        private CurrentRuestzeitGenerator $currentRuestzeitGenerator,
        )
    {
        $this->translator = $translator;

    }

    public function getLocale(): string { 
        return "de";
    }

    public function translate(string $key, array $parameters = [], string $locale = null, string $default = null): string
    {
        if($this->termCache === null) {
            $currentRuestzeit = $this->currentRuestzeitGenerator->get();
            $terms = $currentRuestzeit->getLanguageOverwrites();
            $this->termCache = [];
            foreach($terms as $term) {
                $this->termCache[$term->getTerm()] = $term->getValue();
            }
        }
        
        if(array_key_exists($key, $this->termCache)) {
            return $this->termCache[$key];
        }

        if($default !== null) {
            return (string)$default;
        }

        return $this->translator->trans($key, $parameters, 'messages', $locale);
    }

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        // Falls du den Übersetzer als Proxy nutzt
        return $this->translate($id, $parameters, $locale);
    }
}
