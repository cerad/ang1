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
     * End up with a nested data array
     * Tempting to just go with dbal for this
     * 
     * Also be nice to handle array of persons
     */
    public function postAction(Request $request)
    {   
        $personData = json_decode($request->getContent(),true);
        
        return new JsonResponse($personData,200);

      //$personData['method']      = $request->getMethod();
      //$personData['content']     = $request->getContent();
      //$personData['contentType'] = $request->getContentType();
        
        $personRepo = $this->personRepo;
        
        $person = $personRepo->createPerson();
        
        // Name is a value object
        $personName = $person->getName();
        $personName->full  = $personData['name']['full'];
        $personName->first = $personData['name']['first'];
        $personName->last  = $personData['name']['last'];
        $person->setName($personName);
        
        $personRepo->save($person);
        $personRepo->commit();
        
        $personData['id']  = $person->getId();
        $personData['xxx'] = 'yyy';
        
        // Even thoug we return a 201, still set the Location header
        // Generates: /cerad2/api/v1/persons/257
        $url = $this->router->generate('cerad_api_v1_persons_get', array('personId' => $person->getId()));
        
        $personData['location'] = $url;
       
        $headers = array('Location' => $url);
        
        return new JsonResponse($personData,201,$headers);
    }
}
?>
