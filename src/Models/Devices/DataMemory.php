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
            $this->db->sequence('gen_data_memory', 'nocomdata');
            $id = $this->db->executeSequence();

            $paramsInsert = [
                'index' => 'nocom',
                'type' => 'tab_data_memory',
                'id' => $id['0']['nextval'],
                'body' => [
                    "cod_device" => $paramsDevices['cod_device'],
                    "num_perc_ram_used" => $params['perc_memory_used'],
                    "num_perc_swap_used" => $params['perc_swap_used'],
                    "num_total_ram_used" => $params['total_memory_used'],
                    "num_total_swap_used" => $params['total_swap_used'],
                    "num_total_ram_avaliable" => $params['avaliable_ram_real'],
                    "num_total_swap_avaliable" => $params['avaliable_swap_size'],
                    "num_total_ram" => $params['total_ram_machine'],
                    "num_total_swap" => $params['total_swap_size'],
                    "dte_register" => date('Y-m-d H:i:s'),
                ],
            ];

            $ret = $this->es->index($paramsInsert);
        } catch (Exception $exc) {
            throw new Exception('Error While Insert Data Memory for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

    public function setDataMemoryDb(array $params, array $paramsDevices) {
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
