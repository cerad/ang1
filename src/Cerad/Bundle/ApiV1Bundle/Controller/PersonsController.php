<?php
namespace Cerad\Bundle\ApiV1Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class PersonsController
{    
    protected $personRepo;
    
    public function __construct($personRepo)
    {
        $this->personRepo = $personRepo;
        
    }
    public function getAction(Request $request, $personId = null)
    {   
        /* =========================================
         * Need more work here
         * Project and view
         */
        if ($personId)
        {
            $personData = $this->personRepo->find($personId);
            if ($personData) return new JsonResponse($personData);
        
            $error = array('id' => $personId, 'message' => 'Not Found');
            return new JsonResponse($error,404);
        }
        // Defaults (could be injected?)
        $paramsDefault = array(
            'view'     => null,
            'page'     => null,
            'page_per' => 5,
            'personId' => $personId,
        );
        $params = array_merge($paramsDefault,$request->query->all());
        
        $personsData = $this->personRepo->query($params);
        
        return new JsonResponse($personsData); // jsonData
    
    }
    /* ============================================================
     * Post with a json string
     * 
     * Return 200 for success
     * 400? for failure
     * 
     * Currently return changed record but that might change
     */
    public function postAction(Request $request)
    {   
        $personData = json_decode($request->getContent(),true);
        
        $personId = $personData['id'];
        
        $this->personRepo->updatePerson($personData);
        
        $personDatax = $this->personRepo->find($personId);
        
        return new JsonResponse($personDatax,200);

    }
}
?>
