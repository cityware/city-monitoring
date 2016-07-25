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
class JobService {

    public function jobServiceMonitor(array $paramsDevices) {

        $modelServices = new \Cityware\Monitoring\Models\Services();

        $connectionJobs = new \Cityware\Monitoring\Jobs\ConnectionJobs();
        $connection = $connectionJobs->getConnection($paramsDevices, $paramsDevices['ind_snmp_wmi']);
        
        if ($paramsDevices['ind_snmp_wmi'] == 'S') {
            foreach ($modelServices->getDeviceServices($paramsDevices['cod_device_type'], $paramsDevices['cod_device']) as $valueServices) {
                switch ($valueServices['des_sign']) {
                    case 'aphttp':
                        //Get Snmp Disk Data
                        $apacheHttpd = new JobSnmp\Services\ApacheHttpd();
                        $apacheHttpdData = $apacheHttpd->getServiceData($connection);
                        
                        //Insert disk data Snmp in Database
                        $diskDb = new DbModels\Services\DataApacheHttpd();
                        $diskDb->setDataApacheHttpd($apacheHttpdData, $paramsDevices);
                        break;
                    
                    case 'ngx':
                        //Get Snmp Disk Data
                        $apacheHttpd = new JobSnmp\Services\Nginx();
                        $apacheHttpdData = $apacheHttpd->getServiceData($connection);
                        
                        //Insert disk data Snmp in Database
                        $diskDb = new DbModels\Services\DataNginx();
                        $diskDb->setDataNginx($apacheHttpdData, $paramsDevices);
                        break;
                    
                    case 'phpfpm':
                        //Get Snmp Disk Data
                        $apacheHttpd = new JobSnmp\Services\PhpFpm();
                        $apacheHttpdData = $apacheHttpd->getServiceData($connection);
                        
                        //Insert disk data Snmp in Database
                        $diskDb = new DbModels\Services\DataPhpFpm();
                        $diskDb->setDataPhpFpm($apacheHttpdData, $paramsDevices);
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
