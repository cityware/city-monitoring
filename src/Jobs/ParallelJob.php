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

    public function parallelJobMonitor($params) {
        $modelStdMonitoring = new \Cityware\Monitoring\Models\StandardMonitoring();

        if ($params['ind_snmp_wmi'] == 'S') {

            foreach ($modelStdMonitoring->getStdMonitoring($params['cod_device_type']) as $keyStdMonitoring => $valueStdMonitoring) {
                switch ($valueStdMonitoring['des_sign']) {
                    case 'dsk':
                        $dataDisk = \Cityware\Monitoring\Jobs\Snmp\Disk($params);
                        break;
                    case 'sys':
                        $dataSystem = \Cityware\Monitoring\Jobs\Snmp\System($params);
                        break;
                    case 'mem':
                        $dataMemory = \Cityware\Monitoring\Jobs\Snmp\Memory($params);
                        break;
                    case 'net':
                        $dataNetwork = \Cityware\Monitoring\Jobs\Snmp\Network($params);
                        break;
                    case 'cpu':
                        $dataCpu = \Cityware\Monitoring\Jobs\Snmp\Cpu($params);
                        break;

                    default:
                        break;
                }
            }

        } elseif ($params['ind_snmp_wmi'] == 'W') {
            
        } else {
            break;
        }
    }

}
