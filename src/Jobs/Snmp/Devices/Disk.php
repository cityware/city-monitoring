<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Snmp\Devices;

/**
 * Description of Disk.
 *
 * @author fsvxavier
 */
class Disk {
    
    /**
     * Return Disk Data
     * @param object $snmpConnection
     * @return array
     */
    public function getDiskData($snmpConnection) {
        $return = $snmpConnection->useLinux_Disk()->returnFullData();
        return $return;
    }

    /**
     * Return IO Disk Data
     * @param object $snmpConnection
     * @return array
     */
    public function getIoDiskData($snmpConnection) {
        $return = $snmpConnection->useLinux_Disk()->returnFullDataIo();
        return $return;
    }

}
