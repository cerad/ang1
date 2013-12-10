<?php
namespace Cerad\Bundle\AngBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    public function indexAction(Request $request)
    {
        // Good to go
        $tplData = array();
        
        return $this->render('@CeradAng/Index.html.twig', $tplData);
    }
}
