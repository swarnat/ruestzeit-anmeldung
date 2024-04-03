<?php

namespace App\Controller;

use App\Entity\Anmeldung;
use App\Enum\AnmeldungStatus;
use App\Form\AnmeldungType;
use App\Repository\RuestzeitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class PostalcodeController extends AbstractController
{
    public function __construct(
        private KernelInterface $appKernel
    ) {
    }

    #[Route('/read-postalcode', name: 'read_postalcode')]
    public function index(Request $request, Environment $twig, RuestzeitRepository $ruestzeitRepository): Response
    {
        $projectRoot = $this->appKernel->getProjectDir();
        $fp = fopen($projectRoot . DIRECTORY_SEPARATOR . 'DE.txt', 'r');
        $country = '';
        $postalcodeData = array();
        while ( !feof($fp) )
        {
            $line = fgets($fp, 2048);
        
            $data = str_getcsv($line, "\t");

            if(empty($data[0]) || empty($data[1])) continue;
            if(empty($country)) $country = $data[0];

            $postalcodeData[$data[1]] = [
                "country" => $data[0],
                "postalcode" => $data[1],
                "city" => $data[2],
                "bundesland" => $data[3],
                "bundesland_code" => $data[4],
                "region" => $data[7],
                "region_core" => $data[8],
                "lat" => $data[9],
                "lng" => $data[10],
            ];
            
        }                              

        $phpContent = '<?php ' . PHP_EOL . 'return '.var_export($postalcodeData, true) . ';' . PHP_EOL;
        file_put_contents($this->appKernel->getProjectDir() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Postalcodes' . DIRECTORY_SEPARATOR . $country . '.php', $phpContent);
        
        fclose($fp);
        exit();
    }

}
