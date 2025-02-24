<?php

if(is_file(dirname(__DIR__) . DIRECTORY_SEPARATOR . ".maintenance")) {
    
    if(!function_exists("getClientIPMaintenance")) {
        function getClientIPMaintenance() {
            $headers = [
                'HTTP_X_FORWARDED_FOR',
                'HTTP_CLIENT_IP',
                'HTTP_X_REAL_IP',
                'REMOTE_ADDR'
            ];
        
            foreach ($headers as $header) {
                if (!empty($_SERVER[$header])) {
                    $ipList = explode(',', $_SERVER[$header]);
                    return trim($ipList[0]); // Erste IP ist die ursprüngliche Client-IP
                }
            }
        
            return 'Unbekannte IP';
        }
    }
    
    $clientIP = getClientIPMaintenance();

    $content = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . ".maintenance");
    
    if(strpos($content, $clientIP) === false && empty($_COOKIE["maintenance_bypass"])) {

        readfile(dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "maintenance.html");
        exit();

    }
}

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// date_default_timezone_set( 'Europe/Berlin' );

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
