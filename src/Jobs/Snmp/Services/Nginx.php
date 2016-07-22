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
     * Return CPU Data
     * @param object $snmpConnection
     * @return array
     */
    public function getServiceData($snmpConnection) {

        $nginxData = $snmpConnection->realWalkToArray('NET-SNMP-EXTEND-MIB::nsExtendOutLine."Nginx"', true);

        $return = Array();

        $return['num_total_accesses'] = $nginxData[1];
        $return['num_total_bytes'] = $nginxData[2];
        $return['num_cpu_load'] = $nginxData[3];
        $return['num_uptime'] = $nginxData[4];
        $return['num_requests_seconds'] = $nginxData[5];
        $return['num_bytes_seconds'] = $nginxData[6];
        $return['num_bytes_requests'] = $nginxData[7];
        $return['num_busy_workers'] = $nginxData[8];
        $return['num_idle_workers'] = $nginxData[9];
        $return['num_waiting_connection'] = $nginxData[10];
        $return['num_starting_up'] = $nginxData[11];
        $return['num_reading_request'] = $nginxData[12];
        $return['num_sending_reply'] = $nginxData[13];
        $return['num_keepalive_read'] = $nginxData[14];
        $return['num_dns_lookup'] = $nginxData[15];
        $return['num_closing_connection'] = $nginxData[16];
        $return['num_logging'] = $nginxData[17];
        $return['num_gracefully_finishing'] = $nginxData[18];
        $return['num_idle_cleanup_worker'] = $nginxData[19];
        $return['num_open_slot_no_current_process'] = $nginxData[20];

        return $return;
    }

}
