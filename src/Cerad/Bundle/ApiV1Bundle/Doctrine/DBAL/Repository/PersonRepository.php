<?php
/* ===============================================================
 * DBAL connection based repository
 */
namespace Cerad\Bundle\ApiV1Bundle\Doctrine\DBAL\Repository;

use Doctrine\DBAL\Connection;

class PersonRepository
{
    protected $conn;
    
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }  
    /* ===============================================
     * Mostly for testing
     * All property names shouldmatch their sql column names
     */
    protected function getPersonBasicSelect()
    {
        $sql = <<<EOT
SELECT 
    person.id         AS person__id,
    person.guid       AS person__guid,
    person.name_full  AS person__name_full,
    person.name_last  AS person__name_last,
    person.name_first AS person__name_first,
    person.dob        AS person__dob
FROM   
    persons AS person

EOT;
        return $sql;
    }
    /* ==============================================
     * Full blown admin query
     */
    protected function getPersonAdminSelect()
    {
        $sql = <<<EOT
SELECT 
    person.id         AS person__id,
    person.guid       AS person__guid,
    person.name_full  AS person__name_full,
    person.name_last  AS person__name_last,
    person.name_first AS person__name_first,
    person.email      AS person__email,
    person.phone      AS person__phone,
    person.gender     AS person__gender,
    person.dob        AS person__dob,
    
    person_fed.id           AS person_fed__id,
    person_fed.fed_role_id  AS person_fed__role,
                
    person_fed_cert.id      AS person_fed_cert__id,
    person_fed_cert.role    AS person_fed_cert__role,
    person_fed_cert.badge   AS person_fed_cert__badge,
    person_fed_cert.badgex  AS person_fed_cert__badgex,
                
    person_fed_org.id       AS person_fed_org__id,
    person_fed_org.role     AS person_fed_org__role,
    person_fed_org.org_id   AS person_fed_org__org_id,
    person_fed_org.mem_year AS person_fed_org__mem_year,
                
    person_plan.id           AS person_plan__id,
    person_plan.project_id   AS person_plan__project_id,
    person_plan.basic        AS person_plan__basic,
                
    person_person.id          AS person_person__id,
    person_person.role        AS person_person__role,
    person_person.child_id    AS person_person__child_id,
                
    person_person_child.name_full AS person_person__child_name_full,
                
    person_user.id           AS person_user__id,
    person_user.roles        AS person_user__roles,
    person_user.username     AS person_user__username,
    person_user.email        AS person_user__email,
    person_user.account_name AS person_user__account_name
                
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
    /* ==============================================
     * Select a distinct list of ids
     * Probably should make the joins optional
     */
    protected function getPersonIdsSelect()
    {
        $sql = <<<EOT
SELECT DISTINCT person.id
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
    /* =======================================================
     * Helper to find prefixed properties
     */
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
    /* =======================================================================
     * Does all the mapping majic
     */
    protected function mapRowsToItems($rows)
    {
        $items = array();
        foreach($rows as $row)
        {
            // Person element
            $id = $row['person__id'];
            if (isset($items[$id])) $personItem = $items[$id];
            else                    $personItem = $this->extractRowPrefix($row,'person__');
      
            // Person Fed Element
            $personFedRow = $this->extractRowPrefix($row,'person_fed__');
            if (count($personFedRow) && $personFedRow['role'])
            {
                // Indexed by roles
                $personFedRole = $personFedRow['role'];
                if (isset($personItem['feds'][$personFedRole])) $personFedItem = $personItem['feds'][$personFedRole];
                else                                            $personFedItem = $personFedRow;
                
                // Add certs
                $personFedCertRow = $this->extractRowPrefix($row,'person_fed_cert__');
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
                $personFedOrgRow = $this->extractRowPrefix($row,'person_fed_org__');
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
            $personPlanRow = $this->extractRowPrefix($row,'person_plan__');
            if (count($personPlanRow) && $personPlanRow['id'])
            {
                // Indexed by project
                $personPlanProjectId = $personPlanRow['project_id'];
                if (!isset($personItem['plans'][$personPlanProjectId])) 
                {
                    $personPlanRow['basic'] = unserialize($personPlanRow['basic']);
                    $personItem['plans'][$personPlanProjectId] = $personPlanRow;
                }
            }
            // Person Child Element
            $personPersonRow = $this->extractRowPrefix($row,'person_person__');
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
            $userRow = $this->extractRowPrefix($row,'person_user__');
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
        
        // Done
        return array_values($items);
        
    }
    /* =======================================================================
     * Get array of person ids, filtered, sorted and limited
     * Optional order by should work okay for persons
     */
    public function queryPersonIds($params = array())
    {
        // Basic select
        $sql = $this->getPersonIdsSelect();
        
        // Wheres
        $queryParams = array();
        
        // Sort
        $orderBy = isset($params['order_by']) ? $params['order_by'] : 'person.name_full';
        $sql .= sprintf("ORDER BY %s\n",$orderBy);
        
        // Limit
        $page = isset($params['page']) ? (integer)$params['page'] : 0;
        if ($page) $page --;
        
        $pagePer = isset($params['page_per']) ? (integer)$params['page_per'] : 15;
        
        $sql .= sprintf("LIMIT %d,%d\n",$page * $pagePer, $pagePer);
        
        // Done building
        $sql .= ";\n";
        
        // Do it
        $rows = $this->conn->fetchAll($sql,$queryParams);
        
        // Map (nice if there was an eaiser way
        $ids = array();
        foreach($rows as $row)
        {
            $ids[] = $row['id'];
        }
        return $ids;
    }
    /* ===============================================================
     * Hat to put view stuff here but the sooner the better
     */
    protected function getSelectForView($view)
    {
        switch($view)
        {
            case 'admin': return $this->getPersonAdminSelect();
        }
        return $this->getPersonBasicSelect();
    }
    /* ===============================================================
     * The every popular find
     */
    public function find($id, $view = 'admin')
    {
        if (!$id) return null;
        
        $sql = $this->getSelectForView($view);
        
        $sql .= "WHERE person.id = ?;\n";
        
        $rows = $this->conn->fetchAll($sql,array($id));
        
        $items = $this->mapRowsToItems($rows);
        
        if (count($items) != 1) return null;
        
        return $items[0];
    }
    /* ===============================================================
     * Query based on parameters
     */
    public function query($params = array())
    {
        // Star with ids
        $ids = $this->queryPersonIds($params);
        $idsCount = count($ids);
        if ($idsCount < 1) return array();
        
        // Full query
        $view = isset($params['view']) ? $params['view'] : 'admin';
        $sql = $this->getSelectForView($view);

        // In statement
        // No need to paramertize/sanitize since the ids came from a query
        for($in = $ids[0], $i = 1; $i < $idsCount; $i++) $in .= ',?';
        
        $sql .= sprintf("WHERE person.id IN (%s)\n",implode(',',$ids));
        
        // Sort, stay in sync with queryPersonIds
        $orderBy = isset($params['order_by']) ? $params['order_by'] : 'person.name_full';
        $sql .= sprintf("ORDER BY %s;\n",$orderBy);
        
        // Do it
        $rows = $this->conn->fetchAll($sql);
        
        return $this->mapRowsToItems($rows);
    }
}
