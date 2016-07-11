<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models;

/**
 * Description of DataHost
 *
 * @author fsvxavier
 */
class DataHost extends AbstractModels {

    public function setDataHost(array $params, array $paramsDevices) {

        $this->getConnection();
        try {
            $this->db->transaction();
            $this->db->insert("cod_device", $paramsDevices['cod_device']);
            $this->db->insert("num_uptime", $params['uptime']);
            $this->db->insert("num_users_connected", $params['users_connected']);
            $this->db->insert("num_running_process", $params['running_process']);
            $this->db->insert("dte_register", date('Y-m-d H:i:s'));
            $this->db->from('tab_data_host', null, 'nocomdata');
            $this->db->setDebug(false);
            $this->db->executeInsertQuery();
            $this->db->commit();
        } catch (Exception $exc) {
            $this->db->rollback();
            throw new Exception('Error While Insert Data Host for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

}
