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
class PhpFpm {

    /**
     * Return CPU Data
     * @param object $snmpConnection
     * @return array
     */
    public function getServiceData($snmpConnection) {

        $apacheHttpdData = $snmpConnection->realWalkToArray('NET-SNMP-EXTEND-MIB::nsExtendOutLine."Apache-HTTPd"', true);

        $return = Array();

        $return['num_total_accesses'] = $apacheHttpdData[1];
        $return['num_total_bytes'] = $apacheHttpdData[2];
        $return['num_cpu_load'] = $apacheHttpdData[3];
        $return['num_uptime'] = $apacheHttpdData[4];
        $return['num_requests_seconds'] = $apacheHttpdData[5];
        $return['num_bytes_seconds'] = $apacheHttpdData[6];
        $return['num_bytes_requests'] = $apacheHttpdData[7];
        $return['num_busy_workers'] = $apacheHttpdData[8];
        $return['num_idle_workers'] = $apacheHttpdData[9];
        $return['num_waiting_connection'] = $apacheHttpdData[10];
        $return['num_starting_up'] = $apacheHttpdData[11];
        $return['num_reading_request'] = $apacheHttpdData[12];
        $return['num_sending_reply'] = $apacheHttpdData[13];
        $return['num_keepalive_read'] = $apacheHttpdData[14];
        $return['num_dns_lookup'] = $apacheHttpdData[15];
        $return['num_closing_connection'] = $apacheHttpdData[16];
        $return['num_logging'] = $apacheHttpdData[17];
        $return['num_gracefully_finishing'] = $apacheHttpdData[18];
        $return['num_idle_cleanup_worker'] = $apacheHttpdData[19];
        $return['num_open_slot_no_current_process'] = $apacheHttpdData[20];

        return $return;
    }

}
