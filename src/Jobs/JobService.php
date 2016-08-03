<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs;

use Cityware\Monitoring\Jobs\Services\Snmp AS JobSnmp;
use Cityware\Monitoring\Jobs\Services\Wmi AS JobWmi;
use Cityware\Monitoring\Jobs\Services\Database AS JobDatabase;
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
                        $apacheHttpd = new JobSnmp\ApacheHttpd();
                        $apacheHttpdData = $apacheHttpd->getServiceData($connection);
                        
                        $apacheHttpdDb = new DbModels\Services\DataApacheHttpd();
                        $apacheHttpdDb->setDataApacheHttpd($apacheHttpdData, $paramsDevices);
                        break;
                    
                    case 'ngx':
                        $nginx = new JobSnmp\Nginx();
                        $nginxData = $nginx->getServiceData($connection);
                        
                        $nginxDb = new DbModels\Services\DataNginx();
                        $nginxDb->setDataNginx($nginxData, $paramsDevices);
                        break;
                    
                    case 'phpfpm':
                        $phpFpm = new JobSnmp\PhpFpm();
                        $phpFpmData = $phpFpm->getServiceData($connection);
                        
                        $phpFpmDb = new DbModels\Services\DataPhpFpm();
                        $phpFpmDb->setDataPhpFpm($phpFpmData, $paramsDevices);
                        break;
                    
                    
                    case 'pgsql':
                        $postgreSql = new JobDatabase\PostgreSql();
                        $postgreSqlDb = new DbModels\Services\DataPostgreSql();
                        
                        $postgreSqlDataInstance = $postgreSql->getServiceDataInstance($valueServices);
                        $instanceId = $postgreSqlDb->setDataPostgreSql($postgreSqlDataInstance, $paramsDevices);

                        $postgreSqlDataDatabase = $postgreSql->getServiceDataDatabase($valueServices);
                        $paramsDevices['seq_data_serv_pgsql'] = $instanceId;
                        $postgreSqlDb->setDataPostgreSqlDatabase($postgreSqlDataDatabase, $paramsDevices);
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
