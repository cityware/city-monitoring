<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Snmp\Devices;

/**
 * Description of Cpu
 *
 * @author fsvxavier
 */
class Host {
    
    /**
     * Return CPU Data
     * @param object $snmpConnection
     * @return array
     */
    public function getHostData($snmpConnection) {
        
        $return = Array();
        
        $hostFullData = $snmpConnection->useLinux_Host()->returnFullData();

        $return['uptime'] = $hostFullData['system']['uptime'];
        $return['users_connected'] = $hostFullData['system']['num_users'];
        $return['running_process'] = $hostFullData['system']['processes'];

        return $return;
    }
}
