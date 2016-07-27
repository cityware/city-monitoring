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
class Services extends AbstractModels {

    public function getServices() {
        $this->getConnection();
        $this->db->select("*");
        $this->db->from('tab_service', null, 'nocomsys');
        $rsServices = $this->db->executeSelectQuery();
        return $rsServices;
    }

    public function getServiceById($id) {
        
        $this->getConnection();
        $this->db->select("*");
        $this->db->from('tab_service', null, 'nocomsys');
        $this->db->where("cod_service = '{$id}'");
        $this->db->where("ind_status = 'A'");
        $rsService = $this->db->executeSelectQuery();
        return $rsService;
    }
    
    public function getDeviceServices($deviceTypeId, $codDevice) {
        
        $this->getConnection();
        $this->db->select("ts.cod_device_type");
        $this->db->select("ts.cod_service");
        $this->db->select("ts.nam_service");
        $this->db->select("ts.des_sign");
        $this->db->select("ts.des_icon");
        $this->db->select("tds.des_user");
        $this->db->select("tds.des_password");
        $this->db->select("tds.des_port");
        $this->db->from('tab_service', 'ts', 'nocomsys');
        $this->db->join('tab_device_service', 'tds', 'tds.cod_service = ts.cod_service', 'INNERJOIN', 'nocomsys');
        $this->db->where("ts.cod_device_type = '$deviceTypeId'");
        $this->db->where("tds.cod_device = '{$codDevice}'");
        $this->db->where("ts.ind_status = 'A'");
        $this->db->where("tds.ind_status = 'A'");
        $this->db->setdebug(false);
        $rsDeviceServices = $this->db->executeSelectQuery();
        return $rsDeviceServices;
    }

}
