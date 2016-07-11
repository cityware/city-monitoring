<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models;

/**
 * Description of DataMemory
 *
 * @author fsvxavier
 */
class DataNetwork extends AbstractModels {

    public function setDataNetwork(array $params, $paramsDevices) {

        $this->getConnection();
        try {
            $this->db->transaction();
            foreach ($params['index'] as $index) {
                $this->db->insert("cod_device", $paramsDevices['cod_device']);
                $this->db->insert("nam_interface", $params['name'][$index]);
                $this->db->insert("des_type_interface", $params['type_desc'][$index]);
                $this->db->insert("des_phys_address", $params['phys_address'][$index]);
                $this->db->insert("des_ip_address", json_encode($params['ip_address'][$index]));
                $this->db->insert("ind_oper_status", (($params['oper_status'][$index]) ? "U" : "D"));
                $this->db->insert("ind_admin_status", (($params['admin_status'][$index]) ? "U" : "D"));
                $this->db->insert("num_in_octets", $params['in_octets'][$index]);
                $this->db->insert("num_in_unicast_packets", $params['in_unicast_packets'][$index]);
                $this->db->insert("num_out_octets", $params['out_octets'][$index]);
                $this->db->insert("num_out_unicast_packets", $params['out_unicast_packets'][$index]);
                $this->db->insert("dte_register", date('Y-m-d H:i:s'));
                $this->db->from('tab_data_interface', null, 'nocomdata');
                $this->db->setDebug(false);
                $this->db->executeInsertQuery();
            }
            $this->db->commit();
        } catch (Exception $exc) {
            $this->db->rollback();
            throw new Exception('Error While Insert Data Network Interface for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

}
