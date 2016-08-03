<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Services\Snmp;

/**
 * Description of Cpu
 *
 * @author fsvxavier
 */
class PhpFpm {

    /**
     * Return PHP-FPM service Data
     * @param object $snmpConnection
     * @return array
     */
    public function getServiceData($snmpConnection) {
        
        $phpFpmData = $snmpConnection->realWalkToArray('NET-SNMP-EXTEND-MIB::nsExtendOutLine."Php-Fpm"', true);
        
        $return = Array();

        $return['num_listen_queue'] = $phpFpmData[1];
        $return['num_accepted_connections'] = $phpFpmData[2];
        $return['num_idle_processes'] = $phpFpmData[3];
        $return['num_max_listen_queue'] = $phpFpmData[4];
        $return['num_slow_requests'] = $phpFpmData[5];
        $return['num_max_active_processes'] = $phpFpmData[6];
        $return['num_active_processes'] = $phpFpmData[7];
        $return['num_start_since'] = $phpFpmData[8];
        $return['des_start_time'] = "$phpFpmData[9]";
        $return['num_listen_queue_len'] = $phpFpmData[10];
        $return['des_pool'] = "$phpFpmData[11]";
        $return['num_max_children_reached'] = $phpFpmData[12];
        $return['num_total_processes'] = $phpFpmData[13];
        $return['des_process_manager'] = "$phpFpmData[14]";

        return $return;
    }

}
