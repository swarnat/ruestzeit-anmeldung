<?php

namespace App\Twig;

use App\Service\DynamicTranslationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DynamicTranslationExtension extends AbstractExtension
{
    private DynamicTranslationService $dynamicTranslator;

    public function __construct(DynamicTranslationService $dynamicTranslator)
    {
        $this->dynamicTranslator = $dynamicTranslator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ctrans', [$this->dynamicTranslator, 'translate']),
        ];
    }
}
