<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Wmi;

/**
 * Description of Memory
 *
 * @author fsvxavier
 */
class Memory {

    /**
     * Return Memory Data
     * @param object $wmiConnection
     * @return array
     */
    public function getMemoryData($wmiConnection) {


        $Computersystem = $wmiConnection->query("SELECT TotalPhysicalMemory from Win32_ComputerSystem");
        $OperatingSystem = $wmiConnection->query("SELECT FreePhysicalMemory, TotalVisibleMemorySize FROM Win32_OperatingSystem");
        $PageFileUsage = $wmiConnection->query("SELECT AllocatedBaseSize, CurrentUsage FROM Win32_PageFileUsage");

        $return = Array();

        foreach ($Computersystem as $wmi_computersystem) {
            $return['total_ram_machine'] = $wmi_computersystem->TotalPhysicalMemory;
        }
        foreach ($OperatingSystem as $wmi_operatingsystem) {
            $return['avaliable_ram_real'] = \Cityware\Format\Number::convertByteFormat($wmi_operatingsystem->FreePhysicalMemory, 'KB', 'B');
        }

        $return['total_memory_used'] = $return['total_ram_machine'] - $return['avaliable_ram_real'];
        $return['perc_memory_used'] = round((($return['total_memory_used'] / $return['total_ram_machine']) * 100), 2);
        
        foreach ($PageFileUsage as $wmi_pagefileusage) {
            $return['total_swap_size'] = \Cityware\Format\Number::convertByteFormat($wmi_pagefileusage->AllocatedBaseSize, 'MB', 'B');
            $return['total_swap_used'] = \Cityware\Format\Number::convertByteFormat($wmi_pagefileusage->CurrentUsage, 'MB', 'B');
        }
        
        $return['avaliable_swap_size'] = $return['total_swap_size'] - $return['total_swap_used'];
        $return['perc_swap_used'] = round((($return['total_swap_used'] / $return['total_swap_size']) * 100), 2);
        
        return $return;
    }

}
