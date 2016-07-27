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
        
        /*
        $objPing = new \Cityware\Utility\Ping($snmpConnection->getHost());
        $objPing->setTypeCon('udp');
        $objPing->setPort(160);
        
        echo '<pre>';
        var_dump($objPing->ping());
        exit;
         * 
         */

        $nginxData = $snmpConnection->realWalkToArray('NET-SNMP-EXTEND-MIB::nsExtendOutLine."Nginx"', true);
        
        $return = Array();

        $return['num_active_connections'] = (isset($nginxData[1])) ? $nginxData[1] : 0;
        $return['num_accepted_connections'] = (isset($nginxData[2])) ? $nginxData[2] : 0;
        $return['num_handled_connections'] = (isset($nginxData[3])) ? $nginxData[3] : 0;
        $return['num_handled_requests'] = (isset($nginxData[4])) ? $nginxData[4] : 0;
        $return['num_reading'] = (isset($nginxData[5])) ? $nginxData[5] : 0;
        $return['num_writing'] = (isset($nginxData[6])) ? $nginxData[6] : 0;
        $return['num_waiting'] = (isset($nginxData[7])) ? $nginxData[7] : 0;
        $return['num_requests_connections'] = ($return['num_handled_connections'] > 0) ? $return['num_handled_requests'] / $return['num_handled_connections'] : 0;
        $return['num_keep_alive_connections'] = $return['num_active_connections'] - ($return['num_reading'] + $return['num_writing']);

        return $return;
    }

}
