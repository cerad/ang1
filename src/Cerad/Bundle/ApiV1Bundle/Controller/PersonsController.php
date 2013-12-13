<?php
namespace Cerad\Bundle\ApiV1Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Stopwatch\Stopwatch;
 
use Symfony\Bundle\FrameworkBundle\Routing\Router;

use Doctrine\DBAL\Connection;

class PersonsController
{    
    protected $router;
    protected $personConn;
    protected $stopwatch;
    
    public function __construct(Connection $personConn)
    {
        $this->personConn = $personConn;
        
        $this->stopwatch = new Stopwatch();
        $this->stopwatch->openSection();
        $this->stopwatch->start('controller');
    }
    protected function getPersonsMinQuery()
    {
        $sql = <<<EOT
SELECT 
    person.id         AS person_root_id,
    person.guid       AS person_root_guid,
    person.name_full  AS person_root_nameFull,
    person.name_last  AS person_root_nameLast,
    person.name_first AS person_root_nameFirst,
    person.dob        AS person_root_dob
FROM   
    persons AS person

EOT;
        return $sql;
    }
    protected function getPersonsMaxQuery()
    {
        $sql = <<<EOT
SELECT 
    person.id         AS person_root_id,
    person.guid       AS person_root_guid,
    person.name_full  AS person_root_nameFull,
    person.name_last  AS person_root_nameLast,
    person.name_first AS person_root_nameFirst,
    person.email      AS person_root_email,
    person.phone      AS person_root_phone,
    person.gender     AS person_root_gender,
    person.dob        AS person_root_dob,
    
    person_fed.id           AS person_fed_root_id,
    person_fed.fed_role_id  AS person_fed_root_role,
                
    person_fed_cert.id      AS person_fed_cert_root_id,
    person_fed_cert.role    AS person_fed_cert_root_role,
    person_fed_cert.badge   AS person_fed_cert_root_badge,
    person_fed_cert.badgex  AS person_fed_cert_root_badgex,
                
    person_fed_org.id       AS person_fed_org_root_id,
    person_fed_org.role     AS person_fed_org_root_role,
    person_fed_org.org_id   AS person_fed_org_root_org,
    person_fed_org.mem_year AS person_fed_org_root_memYear,
                
    person_plan.id           AS person_plan_root_id,
    person_plan.project_id   AS person_plan_root_project,
    person_plan.basic        AS person_plan_root_basic,
                
    person_person.id          AS person_person_root_id,
    person_person.role        AS person_person_root_role,
    person_person.child_id    AS person_person_root_childId,
                
    person_person_child.name_full AS person_person_root_childNameFull,
                
    person_user.id           AS person_user_root_id,
    person_user.roles        AS person_user_root_roles,
    person_user.username     AS person_user_root_username,
    person_user.email        AS person_user_root_email,
    person_user.account_name AS person_user_root_accountName
                
FROM      persons          AS person
LEFT JOIN person_feds      AS person_fed          ON person_fed.person_id    = person.id
LEFT JOIN person_fed_certs AS person_fed_cert     ON person_fed_cert.fed_id  = person_fed.id
LEFT JOIN person_fed_orgs  AS person_fed_org      ON person_fed_org.fed_id   = person_fed.id
LEFT JOIN person_plans     AS person_plan         ON person_plan.person_id   = person.id
LEFT JOIN person_persons   AS person_person       ON person_person.parent_id = person.id
LEFT JOIN persons          AS person_person_child ON person_person_child.id  = person_person.child_id
LEFT JOIN users            AS person_user         ON person_user.person_guid = person.guid
                
EOT;
        return $sql;
    }
    /* =========================================================================
     * Go crazy for now and just use the connection parameter
     */
    protected function findPersons($params = array())
    {   
        $this->stopwatch->start('query');
        
        $conn = $this->personConn;
        
        $sql = $this->getPersonsMaxQuery();

        $queryParams = array();
        
        // Filter
        $whereFlag = false;
        if ($params['personId'])
        {
            if (!$whereFlag)
            {
                $whereFlag = true;
                $sql .= "WHERE ";
            }
            else $sql .= "  AND ";
          //$sql .= "person.id = :personId\n";
          //$queryParams['personId'] = (integer)$params['personId'];
            
            $sql .= "person.id = ?\n";
            $queryParams[] = (integer)$params['personId'];
            
        }
        // Ordering
        $sql .= "ORDER BY person.name_full\n";
        
        // LIMIT
        $page     = (integer)$params['page'];
        $rowCount = (integer)$params['page_per'];
        
        // In case user clears row count box
        if (!$page)     $page = 1;
        if (!$rowCount) $rowCount = 1;
        
        $offset = ($page - 1) * $rowCount;
        
          //$sql .= sprintf("LIMIT %d, %d\n",$offset,$rowCount); Does not work due one to many relations
        
        // Done building
        $sql .= ";\n";
        
        // Grab everything
        $rows = $conn->fetchAll($sql,$queryParams); // die('Row Count ' . count($rows));
        $this->stopwatch->stop('query');
        
        // Map to items TODO move to own method or processor
        $this->stopwatch->start('mapping');
        $items = array();
        foreach($rows as $row)
        {
            // Person element
            $id = $row['person_root_id'];
            if (isset($items[$id])) $personItem = $items[$id];
            else                    $personItem = $this->extractRowPrefix($row,'person_root_');
      
            // Person Fed Element
            $personFedRow = $this->extractRowPrefix($row,'person_fed_root_');
            if (count($personFedRow) && $personFedRow['role'])
            {
                // Indexed by roles
                $personFedRole = $personFedRow['role'];
                if (isset($personItem['feds'][$personFedRole])) $personFedItem = $personItem['feds'][$personFedRole];
                else                                            $personFedItem = $personFedRow;
                
                // Add certs
                $personFedCertRow = $this->extractRowPrefix($row,'person_fed_cert_root_');
                if (count($personFedCertRow) && $personFedCertRow['role'])
                {
                    // Indexed by roles
                    $personFedCertRole = $personFedCertRow['role'];
                    if (!isset($personFedItem['certs'][$personFedCertRole])) 
                    {
                        $personFedItem['certs'][$personFedCertRole] = $personFedCertRow;
                    }
                }                
                // Add orgs
                $personFedOrgRow = $this->extractRowPrefix($row,'person_fed_org_root_');
                if (count($personFedOrgRow) && $personFedOrgRow['role'])
                {
                    // Indexed by roles
                    $personFedOrgRole = $personFedOrgRow['role'];
                    if (!isset($personFedItem['orgs'][$personFedOrgRole])) 
                    {
                        $personFedItem['orgs'][$personFedOrgRole] = $personFedOrgRow;
                    }
                }                
                 
                // Store aggreate
                $personItem['feds'][$personFedRole] = $personFedItem;
            }
            // Person Plan Element
            $personPlanRow = $this->extractRowPrefix($row,'person_plan_root_');
            if (count($personPlanRow) && $personPlanRow['id'])
            {
                // Indexed by project
                $personPlanProject = $personPlanRow['project'];
                if (!isset($personItem['plans'][$personPlanProject])) 
                {
                    $personPlanRow['basic'] = unserialize($personPlanRow['basic']);
                    $personItem['plans'][$personPlanProject] = $personPlanRow;
                }
            }
            // Person Child Element
            $personPersonRow = $this->extractRowPrefix($row,'person_person_root_');
            if (count($personPersonRow) && $personPersonRow['id'])
            {
                // Index by id to prevent dups
                $personPersonId = $personPersonRow['id'];
                if (!isset($personItem['persons'][$personPersonId])) 
                {
                    $personItem['persons'][$personPersonId] = $personPersonRow;
                }
            }
            // Add user, no user means nothing added?
            $userRow = $this->extractRowPrefix($row,'person_user_root_');
            if (count($userRow) && $userRow['id'] && !isset($personItem['user']))
            {
                $userRoles = unserialize($userRow['roles']);
              //$userRow['roles'] = implode(',',$userRoles); // comma delmited
                $userRow['roles'] = $userRoles;
                $personItem['user'] = $userRow;
            }

            // Need this to handle changes, maybe references would be better?
            $items[$id] = $personItem;
        }
        // Might need to loop through and remove numeric keys
        
        // Done
        $this->stopwatch->stop('mapping');
        
        return array_slice($items,$offset,$rowCount);
    }
    protected function extractRowPrefix($row,$prefix)
    {
        $item = array();
        $prefixLength = strlen($prefix);
        foreach($row as $key => $value)
        {
            if (substr($key,0,$prefixLength) == $prefix)
            {
                $item[substr($key,$prefixLength)] = $value;
            }
        }
        return $item;
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
        
        $stopwatch = $this->stopwatch;
        $stopwatch->stop('controller');
        $stopwatch->stopSection('section');
        $stopwatchEvents = $stopwatch->getSectionEvents('section');
        
        $stopwatchData = array(
            'query'      => $stopwatchEvents['query'     ]->getDuration(),
            'mapping'    => $stopwatchEvents['mapping'   ]->getDuration(),
            'controller' => $stopwatchEvents['controller']->getDuration(),
        );
        $jsonData = array(
            'items'     => $personsData,
            'stopwatch' => $stopwatchData,
        );
        if (!$personId) return new JsonResponse($personsData); // jsonData
    
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
