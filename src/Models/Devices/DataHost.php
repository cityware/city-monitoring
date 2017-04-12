<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models\Devices;

use Cityware\Monitoring\Models\AbstractModels;

/**
 * Description of DataHost
 *
 * @author fsvxavier
 */
class DataHost extends AbstractModels {

    public function setDataHost(array $params, array $paramsDevices) {

        $this->getConnection();
        try {
            $this->db->sequence('gen_data_host', 'nocomdata');
            $id = $this->db->executeSequence();

            $paramsInsert = [
                'index' => 'nocom',
                'type' => 'tab_data_host',
                'id' => $id['0']['nextval'],
                'body' => [
                    "cod_device" => $paramsDevices['cod_device'],
                    "num_uptime" => $params['uptime'],
                    "num_users_connected" => $params['users_connected'],
                    "num_running_process" => $params['running_process'],
                    "dte_register" => date('Y-m-d H:i:s'),
                ],
            ];

            $ret = $this->es->index($paramsInsert);
        } catch (Exception $exc) {
            throw new Exception('Error While Insert Data Host for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

    public function setDataHostDb(array $params, array $paramsDevices) {

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
    
    public function getDataHost(array $params) {
        $this->getConnection();
        $this->db->select("tdh.num_users_connected");
        $this->db->select("tdh.num_uptime");
        $this->db->select("tdh.num_running_process");
        $this->db->from('tab_data_host', 'tdh', 'nocomdata');
        $this->db->where("tdh.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdh.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdh.dte_register <= '{$params['dte_finish']}'");
        
        $this->db->setDebug(false);
        $rsDataHost = $this->db->executeSelectQuery();
        return $rsDataHost;
    }

}
