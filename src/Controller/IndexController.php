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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    public function __construct() {}

    
    #[Route('/_health', name: 'app_health')]
    public function health(): Response
    {
        return new JsonResponse("ok");
    }

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('index.html.twig');
    }

}
