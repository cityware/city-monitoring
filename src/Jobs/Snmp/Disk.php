<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Snmp;

use Cityware\Monitoring\Jobs\AbstractJobs;

/**
 * Description of Disk.
 *
 * @author fsvxavier
 */
class Disk extends AbstractJobs {
    
    public function __contruct(array $params) {
        $this->getConnections($params, 'S');
    }


    /**
     * @param array $params
     */
    private function getSnmp() {
        
    }

}
