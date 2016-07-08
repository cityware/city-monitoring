<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Snmp;

/**
 * Description of Memory
 *
 * @author fsvxavier
 */
class Memory {
    /**
     * Return Memory Data
     * @param object $snmpConnection
     * @return array
     */
    public function geMemoryData($snmpConnection) {
        $return = $snmpConnection->useLinux_Memory()->returnFullData();
        return $return;
    }
}
