<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Snmp;

/**
 * Description of Disk.
 *
 * @author fsvxavier
 */
class Disk {

    /**
     * 
     * @param array $params
     * @return array
     */
    public function getDiskData($snmpConnection) {
        $return = $snmpConnection->useLinux_Disk()->returnFullData();
        
        echo '<pre>';
        print_r($return);
        exit;
    }

    /**
     * 
     * @param array $params
     * @return array
     */
    public function getDiskIoData($snmpConnection) {
        
    }

}
