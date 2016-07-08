<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Snmp;

/**
 * Description of Network
 *
 * @author fsvxavier
 */
class Network {
    
    /**
     * Return Network Data
     * @param object $snmpConnection
     * @return array
     */
    public function getNetworkData($snmpConnection) {
        $return = $snmpConnection->useIface()->returnFullData();
        return $return;
    }
}
