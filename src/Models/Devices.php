<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models;

use Cityware\Monitoring\Models\AbstractModels;

/**
 * Description of Devices
 *
 * @author fsvxavier
 */
class Devices extends AbstractModels {

    public function getDevices() {
        $this->getConnection();
        $this->db->select("*");
        $this->db->from('tab_device', null, 'nocomsys');
        $this->db->where("ind_status = 'A'");
        $rsDevices = $this->db->executeSelectQuery();
        return $rsDevices;
    }

    public function getDeviceById($id) {
        
        $this->getConnection();
        $this->db->select("*");
        $this->db->from('tab_device', null, 'nocomsys');
        $this->db->where("cod_device = '{$id}'");
        $this->db->where("ind_status = 'A'");
        $rsDevice = $this->db->executeSelectQuery();
        return $rsDevice;
    }
    
    public function getDeviceNocOm() {
        
        $this->getConnection();
        $this->db->select("*");
        $this->db->from('tab_device', null, 'nocomsys');
        $this->db->where("ind_server_monitoring = 'S'");
        $this->db->where("ind_status = 'A'");
        $rsDevice = $this->db->executeSelectQuery();
        return $rsDevice;
    }

}
