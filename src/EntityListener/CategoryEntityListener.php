<?php

namespace App\EntityListener;

use App\Entity\Anmeldung;
use App\Entity\Category;
use App\Enum\AnmeldungStatus;
use App\Service\PostalcodeService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Category::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Category::class)]
class CategoryEntityListener
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    private function getContrastColor($hexColor)
    {
            // hexColor RGB
            $R1 = hexdec(substr($hexColor, 1, 2));
            $G1 = hexdec(substr($hexColor, 3, 2));
            $B1 = hexdec(substr($hexColor, 5, 2));
    
            // Black RGB
            $blackColor = "#000000";
            $R2BlackColor = hexdec(substr($blackColor, 1, 2));
            $G2BlackColor = hexdec(substr($blackColor, 3, 2));
            $B2BlackColor = hexdec(substr($blackColor, 5, 2));
    
             // Calc contrast ratio
             $L1 = 0.2126 * pow($R1 / 255, 2.2) +
                   0.7152 * pow($G1 / 255, 2.2) +
                   0.0722 * pow($B1 / 255, 2.2);
    
            $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
                  0.7152 * pow($G2BlackColor / 255, 2.2) +
                  0.0722 * pow($B2BlackColor / 255, 2.2);
    
            $contrastRatio = 0;
            if ($L1 > $L2) {
                $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
            } else {
                $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
            }
    
            // If contrast is more than 5, return black color
            if ($contrastRatio > 5) {
                return '#000000';
            } else { 
                // if not, return white color.
                return '#FFFFFF';
            }
    }

    public function prePersist(Category $category, LifecycleEventArgs $event)
    {
        $textColor = $this->getContrastColor($category->getColor());
        $category->setTextcolor($textColor);
    }

    public function preUpdate(Category $category, LifecycleEventArgs $event)
    {
        $textColor = $this->getContrastColor($category->getColor());
        $category->setTextcolor($textColor);
    }

}