<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs;

/**
 * Description of AbstractJobs
 *
 * @author fsvxavier
 */
class AbstractJobs {

    private $snmp;
    private $wmi;

    public function __construct(array $params = array()) {
        
    }

    public function connection(array $params = array()) {
        $snmp = new \Cityware\Snmp\SNMP($params['host'], $params['communit'], $params['version']);
        $snmp->setSecLevel(3);
        $snmp->disableCache();

        $this->snmp = $snmp;
    }

}
