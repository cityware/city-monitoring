<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Snmp;

/**
 * Description of Cpu
 *
 * @author fsvxavier
 */
class Cpu {
    
    /**
     * Return CPU Data
     * @param object $snmpConnection
     * @return array
     */
    public function getCpuData($snmpConnection) {
        
        $return = Array();
        $return['oneMinute'] = $snmpConnection->useLinux_Cpu()->loadOneMinute();
        $return['fiveMinute'] = $snmpConnection->useLinux_Cpu()->loadFiveMinutes();
        $return['fifteenMinute'] = $snmpConnection->useLinux_Cpu()->loadFifteenMinutes();
        return $return;
    }
}
