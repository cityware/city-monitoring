<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models;

/**
 * Description of DataCpu
 *
 * @author fsvxavier
 */
class DataCpu extends AbstractModels {
    
    public function setDataCpu(array $params, array $paramsDevices) {
        $this->getConnection();
        try {
            $this->db->transaction();
            
            $this->db->insert("cod_device", $paramsDevices['cod_device']);
            $this->db->insert("num_load_one_min", $params['oneMinute']);
            $this->db->insert("num_load_five_min", $params['fiveMinute']);
            $this->db->insert("num_load_fifteen_min", $params['fifteenMinute']);
            $this->db->insert("num_threshoud_cpu_slots", $paramsDevices['num_slot_processors']);
            $this->db->insert("num_threshoud_cpu_cores", $paramsDevices['num_core_processors']);
            $this->db->insert("num_threshoud_cpu_ht", $paramsDevices['ind_hyper_threading']);
            $this->db->insert("dte_register", date('Y-m-d H:i:s'));
            $this->db->from('tab_data_cpu', null, 'nocomdata');
            $this->db->executeInsertQuery();

            $this->db->commit();
        } catch (Exception $exc) {
            $this->db->rollback();
            throw new Exception('Error While Insert Data CPU for JOB PARALLEL - ' . $exc->getMessage());
        }
    }
}
