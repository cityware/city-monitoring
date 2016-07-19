<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Snmp;

/**
 * Description of Network
 *
 * @author fsvxavier
 */
class Network {
    
    const OID_IF_IP_ADDRESS = '.1.3.6.1.2.1.4.20.1.1';
    const OID_IF_IP_ADDRESS_INDEX = '.1.3.6.1.2.1.4.20.1.2';

    /**
     * Return Network Data
     * @param object $snmpConnection
     * @return array
     */
    public function getNetworkData($snmpConnection) {
        
        /** NETWORK INTERFACE * */
        $interfaceIndexData = $snmpConnection->useIface()->indexes();
        $interfaceName = $snmpConnection->useIface()->names();
        $interfacePhysAddresses = $snmpConnection->useIface()->physAddresses();
        $interfaceOperStatus = $snmpConnection->useIface()->operationStates();
        $interfaceAdminStatus = $snmpConnection->useIface()->adminStates();
        $interfaceInOctets = $snmpConnection->useIface()->inOctets();
        $interfaceInUnicastPackets = $snmpConnection->useIface()->inUnicastPackets();
        $interfaceOutOctets = $snmpConnection->useIface()->outOctets();
        $interfaceOutUnicastPackets = $snmpConnection->useIface()->outUnicastPackets();
        
        $interfaceSpeeds = $snmpConnection->useIface()->speeds();
        $interfaceHighSpeeds = $snmpConnection->useIface()->highSpeeds();
        $interfaceTypes = $snmpConnection->useIface()->types(true);
        
        $aInterfaceIp = $snmpConnection->realWalk1d(self::OID_IF_IP_ADDRESS);
        $aInterfaceIpIndex = $snmpConnection->realWalk1d(self::OID_IF_IP_ADDRESS_INDEX);
 
        $aInterfaceIpAddress = $aInterfaceMaskAddress = Array();
        foreach ($aInterfaceIp as $keyInterfaceIp => $valueInterfaceIp) {
            $keyIndex = str_replace('.1.3.6.1.2.1.4.20.1.1', '.1.3.6.1.2.1.4.20.1.2', $keyInterfaceIp);
            $aKeyIndexReseted = array_values($aInterfaceIpIndex[$keyIndex]);
            $aKeyReseted = array_values($valueInterfaceIp);
            $aInterfaceIpAddress[$aKeyIndexReseted[0]][] = $aKeyReseted[0];
        }
        
        $networkData = Array();

        foreach ($interfaceIndexData as $index) {
            $networkData['index'][$index] = $interfaceIndexData[$index];
            $networkData['name'][$index] = $interfaceName[$index];
            $networkData['oper_status'][$index] = $interfaceOperStatus[$index];
            $networkData['admin_status'][$index] = $interfaceAdminStatus[$index];
            $networkData['type_desc'][$index] = $interfaceTypes[$index];
            $networkData['phys_address'][$index] = (isset($interfacePhysAddresses[$index]) and !empty($interfacePhysAddresses[$index])) ? $interfacePhysAddresses[$index] : '00:00:00:00:00:00';
            $networkData['ip_address'][$index] = (isset($aInterfaceIpAddress[$index]) and !empty($aInterfaceIpAddress[$index])) ? json_encode($aInterfaceIpAddress[$index]) : json_encode(Array('0.0.0.0'));
            $networkData['in_octets'][$index] = $interfaceInOctets[$index];
            $networkData['in_unicast_packets'][$index] = $interfaceInUnicastPackets[$index];
            $networkData['out_octets'][$index] = $interfaceOutOctets[$index];
            $networkData['out_unicast_packets'][$index] = $interfaceOutUnicastPackets[$index];
            $networkData['speed'][$index] = $interfaceSpeeds[$index];
            $networkData['high_speed'][$index] = $interfaceHighSpeeds[$index];
        }
        
        return $networkData;
    }

}
