<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Wmi\Devices;

/**
 * Description of System
 *
 * @author fsvxavier
 */
class System {
    
    /**
     * Return CPU Data
     * @param object $snmpConnection
     * @return array
     */
    public function getSystemData($snmpConnection) {
        
        $return = Array();
        
        $hostFullData = $snmpConnection->useLinux_Host()->returnFullData();

        $hostDataPlatform = new \Cityware\Snmp\Platform($snmpConnection);
        
        $hostDataPlatform->getAllData();

        $return['uptime'] = \Cityware\Format\Date::secondsToTime($hostFullData['system']['uptime']);
        $return['users_connected'] = \Cityware\Format\Number::byteFormat($hostFullData['storage']['memory_size'], 'KB');
        $return['running_process'] = \Cityware\Format\Number::byteFormat($hostFullData['storage']['memory_size'], 'KB');

        return $return;
    }
}
