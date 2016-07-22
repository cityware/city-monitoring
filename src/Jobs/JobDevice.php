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
class JobDevice {

    public function jobDeviceMonitor(array $paramsDevices) {

        $modelStdMonitoring = new \Cityware\Monitoring\Models\StandardMonitoring();

        $connectionJobs = new \Cityware\Monitoring\Jobs\ConnectionJobs();
        $connection = $connectionJobs->getConnection($paramsDevices, $paramsDevices['ind_snmp_wmi']);
        
        if ($paramsDevices['ind_snmp_wmi'] == 'S') {
            foreach ($modelStdMonitoring->getStdMonitoring($paramsDevices['cod_device_type'], $paramsDevices['cod_device']) as $valueStdMonitoring) {
                switch ($valueStdMonitoring['des_sign']) {
                    case 'dsk':
                        //Get Snmp Disk Data
                        $disk = new JobSnmp\Devices\Disk();
                        $dskData = $disk->getDiskData($connection);
                        $dskIoData = $disk->getIoDiskData($connection);
                        
                        //Insert disk data Snmp in Database
                        $diskDb = new DbModels\Devices\DataDisk();
                        $diskDb->setDataDisk($dskData, $paramsDevices);
                        $diskDb->setIoDataDisk($dskIoData, $paramsDevices);
                        break;
                    
                    case 'sys':
                        //Get Snmp System Data
                        //$memory = new JobSnmp\DataSystem();
                        //$memData = $memory->getHostData($connection);
                        break;
                    
                    case 'host':
                        //Get Snmp System Data
                        $host = new JobSnmp\Devices\Host();
                        $hostData = $host->getHostData($connection);
                        
                        //Insert system data Snmp in Database
                        $hostDb = new DbModels\Devices\DataHost();
                        $hostDb->setDataHost($hostData, $paramsDevices);
                        break;
                    
                    case 'mem':
                        //Get Snmp Memory Data
                        $memory = new JobSnmp\Devices\Memory();
                        $memData = $memory->getMemoryData($connection);
                        
                        //Insert memory data Snmp in Database
                        $memoryDb = new DbModels\Devices\DataMemory();
                        $memoryDb->setDataMemory($memData, $paramsDevices);
                        break;
                    
                    case 'net':
                        //Get Snmp Network Data
                        $network = new JobSnmp\Devices\Network();
                        $netData = $network->getNetworkData($connection);
                        
                        //Insert network data Snmp in Database
                        $networkDb = new DbModels\Devices\DataNetwork();
                        $networkDb->setDataNetwork($netData, $paramsDevices);
                        break;
                    
                    case 'cpu':
                        //Get Snmp CPU Data
                        $cpu = new JobSnmp\Devices\Cpu();
                        $cpuData = $cpu->getCpuData($connection);
                        
                        //Insert CPU data Snmp in Database
                        $cpuDb = new DbModels\Devices\DataCpu();
                        $cpuDb->setDataCpu($cpuData, $paramsDevices);
                        break;

                    default:
                        break;
                }
            }
        } else if ($paramsDevices['ind_snmp_wmi'] == 'W') {
            foreach ($modelStdMonitoring->getStdMonitoring($paramsDevices['cod_device_type'], $paramsDevices['cod_device']) as $valueStdMonitoring) {
                switch ($valueStdMonitoring['des_sign']) {
                    case 'dsk':
                        //Get Snmp Disk Data
                        $disk = new JobWmi\Devices\Disk();
                        $dskData = $disk->getDiskData($connection);
                        //$dskIoData = $disk->getIoDiskData($connection);
                        
                        //Insert disk data Snmp in Database
                        $diskDb = new DbModels\Devices\DataDisk();
                        $diskDb->setDataDisk($dskData, $paramsDevices);
                        //$diskDb->setIoDataDisk($dskIoData, $paramsDevices);
                        break;
                    
                    case 'sys':
                        //Get Snmp System Data
                        //$memory = new JobWmi\Devices\Host();
                        //$memData = $memory->getHostData($connection);
                        break;
                    
                    case 'host':
                        //Get Snmp System Data
                        $host = new JobWmi\Devices\Host();
                        $hostData = $host->getHostData($connection);
                        
                        //Insert system data Snmp in Database
                        $hostDb = new DbModels\Devices\DataHost();
                        $hostDb->setDataHost($hostData, $paramsDevices);
                        break;
                    
                    case 'mem':
                        //Get Snmp Memory Data
                        $memory = new JobWmi\Devices\Memory();
                        $memData = $memory->getMemoryData($connection);
                        
                        //Insert memory data Snmp in Database
                        $memoryDb = new DbModels\Devices\DataMemory();
                        $memoryDb->setDataMemory($memData, $paramsDevices);
                        break;
                    
                    case 'net':
                        //Get Snmp Network Data
                        $network = new JobWmi\Devices\Network();
                        $netData = $network->getNetworkData($connection);
                        
                        //Insert network data Snmp in Database
                        $networkDb = new DbModels\Devices\DataNetwork();
                        $networkDb->setDataNetwork($netData, $paramsDevices);
                        break;
                    
                    case 'cpu':
                        //Get Snmp CPU Data
                        $cpu = new JobWmi\Devices\Cpu();
                        $cpuData = $cpu->getCpuData($connection);
                        
                        //Insert CPU data Snmp in Database
                        $cpuDb = new DbModels\Devices\DataCpu();
                        $cpuDb->setDataCpu($cpuData, $paramsDevices);
                        break;

                    default:
                        break;
                }
            }
        } else {
            //break;
        }
    }

}
