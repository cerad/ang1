<?php
namespace Cerad\Bundle\ApiV1Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/* =======================================================
 * Test the interface
 */
class PersonCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cerad_api_v1:test:person');
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo sprintf("API V1 Test Person\n");
        $repo = $this->getService('cerad_api_v1.person.repository.doctrine_dbal');
        
        $params = array(
            'page'     => 2, 
            'page_per' => 10,
            'order_by' => 'person.name_last',
        );
        
        $ids = $repo->queryPersonIds($params);
        $idsx = implode(',',$ids);
        echo sprintf("Person ID Count: %d %s\n",count($ids),$idsx);
        
        $person = $repo->find(1);
      //print_r($person);
        
        $persons = $repo->query($params);
        
        $i = 0;
        foreach($persons as $person)
        {
            foreach($person['plans'] as $plan)
            {
                echo sprintf("%3d %3d %s %s\n",$i++,$person['id'],$plan['project_id'],$person['name_full']);
            }
        }
      //print_r($persons[5]);
    }
}
?>
