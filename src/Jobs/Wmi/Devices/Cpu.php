<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Wmi\Devices;

/**
 * Description of Cpu
 *
 * @author fsvxavier
 */
class Cpu {

    /**
     * Return CPU Data
     * @param object $wmiConnection
     * @return array
     */
    public function getCpuData($wmiConnection) {

        $Processor = $wmiConnection->query("SELECT * FROM Win32_Processor");

        $loadPercentage = $countProcessors = 0;
        foreach ($Processor as $wmi_processor) {
            $loadPercentage += (int) $wmi_processor->LoadPercentage;
        }

        $return = Array();
        $return['loadPercentage'] = ($countProcessors > 0) ? ($loadPercentage / $countProcessors) : 0;
        $return['oneMinute'] = 0;
        $return['fiveMinute'] = 0;
        $return['fifteenMinute'] = 0;

        return $return;
    }

}
