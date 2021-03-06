<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring;

/**
 * Description of ConnectionJobs
 *
 * @author fsvxavier
 */
class Connection {

    private $snmp = null;
    private $wmi = null;

    /**
     * Return connection base type device
     * @param array $params
     * @param string $type
     * @return object
     */
    public function getConnection(array $params, $type) {
        if ($type == 'S') {
            $host = (!empty($params['num_ip'])) ? $params['num_ip'] : '127.0.0.1';
            $community = (!empty($params['des_snmp_community'])) ? $params['des_snmp_community'] : 'public';
            $version = (!empty($params['des_snmp_version'])) ? $params['des_snmp_version'] : '2c';
            $seclevel = (!empty($params['des_snmp_sec_level'])) ? $params['des_snmp_sec_level'] : 'noAuthNoPriv';
            $authprotocol = (!empty($params['des_snmp_auth_protocol'])) ? $params['des_snmp_auth_protocol'] : 'MD5';
            $authpassphrase = (!empty($params['des_snmp_auth_passphrase'])) ? $params['des_snmp_auth_passphrase'] : 'None';
            $privprotocol = (!empty($params['des_snmp_priv_protocol'])) ? $params['des_snmp_priv_protocol'] : 'DES';
            $privpassphrase = (!empty($params['des_snmp_priv_passphrase'])) ? $params['des_snmp_priv_passphrase'] : 'None';

            $connection = new \Cityware\Snmp\SNMP($host, $community, $version, $seclevel, $authprotocol, $authpassphrase, $privprotocol, $privpassphrase);
            $connection->setSecLevel(3);
            $connection->disableCache();

            $this->setSnmpCon($connection);
        } else if ($type == 'W') {
            $host = (!empty($params['num_ip'])) ? $params['num_ip'] : '127.0.0.1';
            $username = (!empty($params['des_wmi_user'])) ? $params['des_wmi_user'] : null;
            $password = (!empty($params['des_wmi_password'])) ? $params['des_wmi_password'] : null;
            $domain = (!empty($params['des_wmi_domain'])) ? $params['des_wmi_domain'] : null;
            $wmi = new \Cityware\Wmi\Wmi($host, $username, $password, $domain);
            $connection = $wmi->connect('root\\cimv2');
            $this->setWmiCon($connection);
        }

        return $connection;
    }

    public function setSnmpCon($snmp) {
        $this->snmp = $snmp;
    }

    public function getSnmpCon() {
        return $this->snmp;
    }
    
    public function setWmiCon($wmi) {
        $this->wmi = $wmi;
    }

    public function getWmiCon() {
        return $this->wmi;
    }

}
