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

    public function getDeviceServices($codDevice) {

        $this->getConnection();
        $this->db->select("td.num_ip");
        $this->db->select("td.cod_device_type");
        $this->db->select("ts.cod_service");
        $this->db->select("ts.nam_service");
        $this->db->select("ts.des_sign");
        $this->db->select("ts.des_icon");
        $this->db->select("tds.des_user");
        $this->db->select("tds.des_password");
        $this->db->select("tds.des_port");
        $this->db->select("tds.nam_database");
        $this->db->from('tab_service', 'ts', 'nocomsys');
        $this->db->join('tab_device_service', 'tds', 'tds.cod_service = ts.cod_service', 'INNERJOIN', 'nocomsys');
        $this->db->join('tab_device', 'td', 'td.cod_device = tds.cod_device', 'INNERJOIN', 'nocomsys');
        //$this->db->where("ts.cod_device_type = '$deviceTypeId'");
        $this->db->where("tds.cod_device = '{$codDevice}'");
        $this->db->where("ts.ind_status = 'A'");
        $this->db->where("tds.ind_status = 'A'");
        $this->db->setdebug(false);
        $rsDeviceServices = $this->db->executeSelectQuery();
        return $rsDeviceServices;
    }

    public function getServicesByDeviceType($codDeviceType, $notPresentDevice = null) {

        $this->getConnection();
        
        
        if (!empty($notPresentDevice)) {
            $this->db->select("tds.cod_service");
            $this->db->from('tab_device_service', 'tds', 'nocomsys');
            $this->db->where("tds.cod_device = '{$notPresentDevice}'");
            $rsSubSelectDeviceService = $this->db->executeSubSelectQuery();

            $this->db->where("ts.cod_service NOT IN ({$rsSubSelectDeviceService})");
        }


        $this->db->select("ts.*");
        $this->db->select("tdts.cod_device_type");
        $this->db->from('tab_service', 'ts', 'nocomsys');
        $this->db->join('tab_device_type_service', 'tdts', 'tdts.cod_service = ts.cod_service', 'INNERJOIN', 'nocomsys');
        $this->db->where("tdts.cod_device_type = '{$codDeviceType}'");
        $this->db->where("ts.ind_status = 'A'");
        $this->db->setdebug(false);
        $rsServicesByDeviceType = $this->db->executeSelectQuery();
        return $rsServicesByDeviceType;
    }

}
