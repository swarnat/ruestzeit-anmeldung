<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Events;
use App\Entity\QRCode;
use App\Enum\EventType;
use App\Enum\QRCodeType;
use App\Repository\QRCodeRepository;
use App\Service\ClientIdentifierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageNotFoundController extends AbstractController
{
    
    public function not_found_redirect(): Response
    {
        return new RedirectResponse("/");
    }
   

}
