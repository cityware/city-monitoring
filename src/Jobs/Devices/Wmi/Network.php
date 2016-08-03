<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Devices\Wmi;

/**
 * Description of Network
 *
 * @author fsvxavier
 */
class Network {

    /**
     * Return Network Data
     * @param object $wmiConnection
     * @return array
     */
    public function getNetworkData($wmiConnection) {

        $NetworkAdapter = $wmiConnection->query("SELECT Index, NetEnabled, Name, NetConnectionID, MACAddress, Speed FROM Win32_NetworkAdapter  WHERE PhysicalAdapter = 'TRUE'");
        $NetworkAdapterConfiguration = $wmiConnection->query("SELECT Index, IPEnabled, IPAddress FROM Win32_NetworkAdapterConfiguration");
        $NetworkInterface = $wmiConnection->query("SELECT BytesReceivedPersec, PacketsReceivedUnicastPersec, BytesSentPersec, PacketsSentUnicastPersec FROM Win32_PerfFormattedData_Tcpip_NetworkInterface");

        $packetsSentUnicastPersec = $bytesSentPersec = $packetsReceivedUnicastPersec = $bytesReceivedPersec = 0;
        foreach ($NetworkInterface as $wmi_networkinterface) {
            $bytesReceivedPersec += $wmi_networkinterface->BytesReceivedPersec;
            $packetsReceivedUnicastPersec += $wmi_networkinterface->PacketsReceivedUnicastPersec;
            $bytesSentPersec += $wmi_networkinterface->BytesSentPersec;
            $packetsSentUnicastPersec += $wmi_networkinterface->PacketsSentUnicastPersec;
        }
        
        $networkData = Array();
        $countIpEnable = 0;
        foreach ($NetworkAdapter as $wmi_networkadapter) {
            $index = $wmi_networkadapter->Index;
            $netEnable = $wmi_networkadapter->NetEnabled;
            $networkData['index'][$index] = $wmi_networkadapter->Index;
            $networkData['name'][$index] = utf8_encode($wmi_networkadapter->Name);
            $networkData['oper_status'][$index] = $netEnable;
            $networkData['admin_status'][$index] = $netEnable;
            $networkData['type_desc'][$index] = utf8_encode($wmi_networkadapter->NetConnectionID);
            $networkData['phys_address'][$index] = (isset($wmi_networkadapter->MACAddress) and ! empty($wmi_networkadapter->MACAddress)) ? str_replace(" ", ":", $wmi_networkadapter->MACAddress) : '00:00:00:00:00:00';
            $networkData['speed'][$index] = $wmi_networkadapter->Speed;
            $networkData['high_speed'][$index] = $wmi_networkadapter->Speed;

            if ($wmi_networkadapter->NetEnabled) {
                $countIpEnable++;
            }

            foreach ($NetworkAdapterConfiguration as $wmi_networkadapterconf) {
                if ($wmi_networkadapterconf->Index == $index) {
                    if ($wmi_networkadapterconf->IPEnabled) {
                        $sIPAddress = str_replace(Array('(', ')'), '', $wmi_networkadapterconf->IPAddress);
                        $aIPAddress = explode(",", $sIPAddress);
                        foreach ($aIPAddress as $keyIPAddress => $valueIPAddress) {
                            if ($keyIPAddress == 0) {
                                $networkData['ip_address'][$index] = json_encode(Array($valueIPAddress));
                            }
                        }
                    } else {
                        $networkData['ip_address'][$index] = json_encode(Array('0.0.0.0'));
                    }
                }
            }
        }

        if ($countIpEnable > 0) {
            foreach ($networkData['index'] as $indexNet) {
                if ($networkData['oper_status'][$indexNet]) {
                    $networkData['in_octets'][$indexNet] = $bytesReceivedPersec / $countIpEnable;
                    $networkData['in_unicast_packets'][$indexNet] = $packetsReceivedUnicastPersec / $countIpEnable;
                    $networkData['out_octets'][$indexNet] = $bytesSentPersec / $countIpEnable;
                    $networkData['out_unicast_packets'][$indexNet] = $packetsSentUnicastPersec / $countIpEnable;
                }
            }
        } else {
            $networkData['in_octets'][$indexNet] = 0;
            $networkData['in_unicast_packets'][$indexNet] = 0;
            $networkData['out_octets'][$indexNet] = 0;
            $networkData['out_unicast_packets'][$indexNet] = 0;
        }

        return $networkData;
    }

}
