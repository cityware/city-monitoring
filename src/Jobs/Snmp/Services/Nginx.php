<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Snmp\Services;

/**
 * Description of Cpu
 *
 * @author fsvxavier
 */
class Nginx {

    /**
     * Return Nginx service Data
     * @param object $snmpConnection
     * @return array
     */
    public function getServiceData($snmpConnection) {

        $nginxData = $snmpConnection->realWalkToArray('NET-SNMP-EXTEND-MIB::nsExtendOutLine."Nginx"', true);
        
        $return = Array();

        $return['num_active_connections'] = $nginxData[1];
        $return['num_accepted_connections'] = $nginxData[2];
        $return['num_handled_connections'] = $nginxData[3];
        $return['num_handled_requests'] = $nginxData[4];
        $return['num_reading'] = $nginxData[5];
        $return['num_writing'] = $nginxData[6];
        $return['num_waiting'] = $nginxData[7];
        $return['num_requests_connections'] = $return['num_handled_requests'] / $return['num_handled_connections'];
        $return['num_keep_alive_connections'] = $return['num_active_connections'] - ($return['num_reading'] + $return['num_writing']);

        return $return;
    }

}
