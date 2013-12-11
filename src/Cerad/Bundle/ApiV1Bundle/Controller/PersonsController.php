<?php
namespace Cerad\Bundle\ApiV1Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

use Doctrine\DBAL\Connection;

class PersonsController
{    
    protected $router;
    protected $personConn;
    
    public function __construct(Connection $personConn)
    {
        $this->personConn = $personConn;
    }
    protected function getPersonsMinQuery()
    {
        $sql = <<<EOT
SELECT 
    person.id         AS person_id,
    person.guid       AS person_guid,
    person.name_full  AS person_name_full,
    person.name_last  AS person_name_last,
    person.name_first AS person_name_first,
    person.dob        AS person_dob
FROM   
    persons AS person

EOT;
        return $sql;
    }
    /* =========================================================================
     * Go crazy for now and just use the connection parameter
     */
    protected function findPersons($params = array())
    {   
        $conn = $this->personConn;
        
        $sql = $this->getPersonsMinQuery();

        $queryParams = array();
        
        // Filter
        $whereFlag = false;
        if ($params['personId'])
        {
            if (!$whereFlag)
            {
                $whereFlag = true;
                $sql .= "WHERE\n";
            }
            $sql .= "person.id = :personId\n";
            $queryParams['personId'] = (integer)$params['personId'];
            
        }
        // Ordering
        $sql .= "ORDER BY person.name_full\n";
        
        // LIMIT
        $page     = (integer)$params['page'];
        $rowCount = (integer)$params['page_per'];
        
        if ($rowCount)
        {
            if ($page) $page--;
            $offset = $page * $rowCount;
            $sql .= sprintf("LIMIT %d, %d\n",$offset,$rowCount);
        }
        // Done building
        $sql .= ";\n";
        
        // Grab everything
        $rows = $conn->fetchAll($sql,$queryParams);

        return $rows;
        
        // Optimize a bit by returning an array
        $qb = $this->personRepo->createQueryBuilder('person');
           
        $qb->addSelect('fed,cert,org');

        $qb->leftJoin ('person.feds','fed');
        $qb->leftJoin ('fed.certs',  'cert');
        $qb->leftJoin ('fed.orgs',   'org');
        
        if ($params['personId'])
        {
            $qb->andWhere('person.id = :personId');
            $qb->setParameter('personId',$params['personId']);
        }
        $qb->orderBy('person.nameFull');
        
        if ($params['per_page']) $qb->setMaxResults  ($params['per_page']);
        if ($params['page'])     $qb->setFirstResult(($params['page'] - 1) * $params['per_page']);
        
        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
        
    }
    protected function extractPersonData($params,$person)
    {
        $personData = array(
            'id'         => $person['id'],
            'guid'       => $person['guid'],
            'name_full'  => $person['nameFull'],
            'name_first' => $person['nameFirst'],
            'name_last'  => $person['nameLast'],
            'dob'        => $person['dob'] ? $person['dob']->format('Y-m-d') : null,
        );
        if ($params['rep'] != 'min') $personData['feds'] = $person['feds'];
        
        return $personData;
    }
    public function getAction(Request $request, $personId = null)
    {   
        // Defaults (could be injected?)
       $paramsDefault = array(
            'rep'      => null,
            'page'     => null,
            'page_per' => 5,
            'personId' => $personId,
        );
        $params = array_merge($paramsDefault,$request->query->all());
        
        $personsData = $this->findPersons($params);
        
        if (!$personId) return new JsonResponse($personsData);
    
        // One and only one
        if (count($personsData) == 1) return new JsonResponse($personsData[0]);
        
        $error = array('id' => $personId, 'message' => 'Not Found');
        return new JsonResponse($error,404);
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
