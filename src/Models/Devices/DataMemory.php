<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models\Devices;
use Cityware\Monitoring\Models\AbstractModels;

/**
 * Description of DataMemory
 *
 * @author fsvxavier
 */
class DataMemory extends AbstractModels {

    public function setDataMemory(array $params, array $paramsDevices) {
        $this->getConnection();
        try {
            $this->db->transaction();

            $this->db->insert("cod_device", $paramsDevices['cod_device']);
            $this->db->insert("num_perc_ram_used", $params['perc_memory_used']);
            $this->db->insert("num_perc_swap_used", $params['perc_swap_used']);
            $this->db->insert("num_total_ram_used", $params['total_memory_used']);
            $this->db->insert("num_total_swap_used", $params['total_swap_used']);
            $this->db->insert("num_total_ram_avaliable", $params['avaliable_ram_real']);
            $this->db->insert("num_total_swap_avaliable", $params['avaliable_swap_size']);
            $this->db->insert("num_total_ram", $params['total_ram_machine']);
            $this->db->insert("num_total_swap", $params['total_swap_size']);
            $this->db->insert("dte_register", date('Y-m-d H:i:s'));
            $this->db->from('tab_data_memory', null, 'nocomdata');
            $this->db->executeInsertQuery();

            $this->db->commit();
        } catch (Exception $exc) {
            $this->db->rollback();
            throw new Exception('Error While Insert Data Memory for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

}
