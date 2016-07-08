<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs;

/**
 * Description of ParallelJob
 *
 * @author fsvxavier
 */
class ParallelJob {

    public function parallelJobMonitor(array $paramsDevices) {

        $modelStdMonitoring = new \Cityware\Monitoring\Models\StandardMonitoring();

        $connectionJobs = new \Cityware\Monitoring\Jobs\ConnectionJobs();
        $connection = $connectionJobs->getConnection($paramsDevices, $paramsDevices['ind_snmp_wmi']);
        
        if ($paramsDevices['ind_snmp_wmi'] == 'S') {
            foreach ($modelStdMonitoring->getStdMonitoring($paramsDevices['cod_device_type']) as $valueStdMonitoring) {
                switch ($valueStdMonitoring['des_sign']) {
                    case 'dsk':
                        $disk = new \Cityware\Monitoring\Jobs\Snmp\Disk();
                        $disk->getDiskData($connection);
                        break;
                    case 'sys':

                        break;
                    case 'mem':

                        break;
                    case 'net':

                        break;
                    case 'cpu':

                        break;

                    default:
                        break;
                }
            }
        } else if ($paramsDevices['ind_snmp_wmi'] == 'W') {
            
        } else {
            //break;
        }
    }

}
