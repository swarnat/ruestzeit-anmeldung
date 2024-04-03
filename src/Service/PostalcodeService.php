<?php 
namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

class PostalcodeService {
    public function __construct(
        private KernelInterface $appKernel
    ) {
    }
        
    public function getPostalcodeData($country, $postalcode) {

        $data = require($this->appKernel->getProjectDir() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Postalcodes' . DIRECTORY_SEPARATOR . $country . '.php');

        if(isset($data[$postalcode])) {
            return $data[$postalcode];
        }

        return null;
    }
}