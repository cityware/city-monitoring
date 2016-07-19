<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Wmi;

/**
 * Description of Disk.
 *
 * @author fsvxavier
 */
class Disk {

    /**
     * Return Disk Data
     * @param object $wmiConnection
     * @return array
     */
    public function getDiskData($wmiConnection) {

        $LogicalDisk = $wmiConnection->query("SELECT Size, FreeSpace, DeviceID FROM Win32_LogicalDisk");

        $return = Array();

        $index = 0;
        foreach ($LogicalDisk as $wmi_LogicalDisk) {
            if (isset($wmi_LogicalDisk->Size) and $wmi_LogicalDisk->Size > 0) {
                $return['index'][$index] = $index;
                $return['total_size'][$index] = $wmi_LogicalDisk->Size;
                $return['free_size'][$index] = $wmi_LogicalDisk->FreeSpace;
                $return['used_size'][$index] = $return['total_size'][$index] - $return['free_size'][$index];
                $return['used_percent'][$index] = round((($return['free_size'][$index] / $return['total_size'][$index]) * 100), 2);
                $return['free_percent'][$index] = round((($return['used_size'][$index] / $return['total_size'][$index]) * 100), 2);
                $return['path'][$index] = $wmi_LogicalDisk->DeviceID;
                $index++;
            }
        }

        return $return;
    }

    /**
     * Return IO Disk Data
     * @param object $wmiConnection
     * @return array
     */
    public function getIoDiskData($wmiConnection) {
        $return = $snmpConnection->useLinux_Disk()->returnFullDataIo();
        return $return;
    }

}
