<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models;

/**
 * Description of StandardMonitoring
 *
 * @author fsvxavier
 */
class StandardMonitoring extends AbstractModels {

    public function __construct() {
        $this->getConnection();
    }

    public function getStdMonitoring($deviceTypeId, $codDevice = null) {
        $this->db->select("tsm.cod_std_monitoring");
        $this->db->select("tsm.nam_std_monitoring");
        $this->db->select("tsm.des_sign");
        $this->db->from('tab_std_monitoring', 'tsm', 'nocomsys');
        $this->db->join('tab_std_monitoring_device_type', 'tsmdt', 'tsmdt.cod_std_monitoring = tsm.cod_std_monitoring', 'INNERJOIN', 'nocomsys');
        $this->db->where("tsm.ind_status = 'A'");
        $this->db->where("tsmdt.cod_device_type = '$deviceTypeId'");

        if (!empty($codDevice)) {
            $this->db->join('tab_device', 'td', 'td.cod_device_type = tsmdt.cod_device_type', 'INNERJOIN', 'nocomsys');
            $this->db->where("td.ind_status = 'A'");
            $this->db->where("td.cod_device = '$codDevice'");
        }
        $this->db->setDebug(false);
        $rsStdMonitoring = $this->db->executeSelectQuery();
        return $rsStdMonitoring;
    }

}
