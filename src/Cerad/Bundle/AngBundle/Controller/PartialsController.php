<?php

namespace Cerad\Bundle\AngBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PartialsController
{    
    protected $partialsDir;
    
    public function __construct($resourcesDir)
    {
        $this->partialsDir = $resourcesDir . '/partials/';
    }
    public function getAction(Request $request, $partial)
    {   
        $path = $this->partialsDir . $partial;
        
        if (file_exists($path)) return new Response(file_get_contents($path));
        
        
        $html = sprintf("<h1>Partial not found: %s</h1>",$partial);
                
        return new Response($html,404);
    }
}
?>
