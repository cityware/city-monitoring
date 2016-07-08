<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs;

use Cityware\Monitoring\Jobs\Snmp AS JobSnmp;
use Cityware\Monitoring\Jobs\Wmi AS JobWmi;
use Cityware\Monitoring\Models AS DbModels;

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
            foreach ($modelStdMonitoring->getStdMonitoring($paramsDevices['cod_device_type'], $paramsDevices['cod_device']) as $valueStdMonitoring) {
                switch ($valueStdMonitoring['des_sign']) {
                    case 'dsk':
                        //Get Snmp Data
                        $disk = new JobSnmp\Disk();
                        $dskData = $disk->getDiskData($connection);
                        $dskIoData = $disk->getIoDiskData($connection);
                        
                        //Insert data Snmp in Database
                        $diskDb = new DbModels\DataDisk();
                        $diskDb->setDataDisk($dskData, $paramsDevices);
                        $diskDb->setIoDataDisk($dskIoData, $paramsDevices);
                        break;
                    case 'sys':

                        break;
                    case 'mem':
                        //Get Snmp Data
                        $memory = new JobSnmp\Memory();
                        $memData = $memory->geMemoryData($connection);
                        
                        //Insert data Snmp in Database
                        $memoryDb = new DbModels\DataMemory();
                        $memoryDb->setDataMemory($memData, $paramsDevices);
                        break;
                    case 'net':
                        //Get Snmp Data
                        $network = new JobSnmp\Network();
                        $netData = $network->getNetworkData($connection);
                        
                        //Insert data Snmp in Database
                        $networkDb = new DbModels\DataNetwork();
                        $networkDb->setDataNetwork($netData, $paramsDevices);
                        break;
                    case 'cpu':
                        //Get Snmp Data
                        $cpu = new JobSnmp\Cpu();
                        $cpuData = $cpu->getCpuData($connection);
                        
                        //Insert data Snmp in Database
                        $cpuDb = new DbModels\DataCpu();
                        $cpuDb->setDataCpu($cpuData, $paramsDevices);
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
