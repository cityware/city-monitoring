<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring;

use Exception;

/**
 * Description of Monitor.
 *
 * @author fsvxavier
 */
class MonitorService {

    public function __construct() {
        $modelDevice = new \Cityware\Monitoring\Models\Devices();
        $this->prepareServices($modelDevice->getDevices());
    }

    private function prepareServices(array $paramsDevices) {
        
        $jobObject = new \Cityware\Monitoring\Jobs\JobService();
        $job = new \Zend\Stdlib\CallbackHandler(array($jobObject, 'jobServiceMonitor'));

        $memorySharedManagerDevice = new \Cityware\MemoryShared\MemorySharedManager();
        $memorySharedManagerDevice->setStorage('file', array('dir' => DATA_PATH . 'sharedmemory' . DS . 'services' . DS));

        $managerDevices = new \Cityware\ParallelJobs\ForkManager();
        $managerDevices->setAutoStart(true);
        $managerDevices->timeout(50);
        $managerDevices->setMemoryManager($memorySharedManagerDevice);
        $managerDevices->setStorage('file');
        $managerDevices->setShareResult(false);

        $indexParallel = 0;
        
        foreach ($paramsDevices as $valueDevices) {
            if ($indexParallel == 0) {
                $managerDevices->doTheJob($job, array($valueDevices));
            } else {
                $managerDevices->doTheJobChild($indexParallel, $job, array($valueDevices));
            }
            $indexParallel++;
        }
        
        $managerDevices->createChildren($indexParallel);
        
        // do multiple tasks
        $managerDevices->wait();

        for ($indexParallelClear = $indexParallel; $indexParallelClear > 0; $indexParallelClear--) {
            $memorySharedManagerDevice->clear($indexParallelClear);
        }
        //$managerDevices->closeChildren();
        //$managerDevices->handler(SIGKILL);
    }

}
