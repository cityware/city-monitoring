<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models;

use Exception;

/**
 * Description of DataDisk
 *
 * @author fsvxavier
 */
class DataDisk extends AbstractModels {

    public function setDataDisk(array $params, array $paramsDevices) {
        $this->getConnection();
        try {
            $this->db->transaction();
            foreach ($params['index'] as $index) {
                if ($params['total_size'][$index] > 0) {
                    $used_bytes = $params['total_size'][$index] * ($params['used_percent'][$index] / 100);
                    $this->db->insert("cod_device", $paramsDevices['cod_device']);
                    $this->db->insert("des_path", $params['path'][$index]);
                    $this->db->insert("num_total_size", $params['total_size'][$index]);
                    $this->db->insert("num_used_percent", $params['used_percent'][$index]);
                    $this->db->insert("num_free_percent", $params['free_percent'][$index]);
                    $this->db->insert("num_total_bytes", $params['total_size'][$index]);
                    $this->db->insert("num_free_bytes", ($params['total_size'][$index] - $used_bytes));
                    $this->db->insert("num_used_bytes", $used_bytes);
                    $this->db->insert("dte_register", date('Y-m-d H:i:s'));
                    $this->db->from('tab_data_disk', null, 'nocomdata');
                    $this->db->executeInsertQuery();
                }
            }
            $this->db->commit();
        } catch (Exception $exc) {
            $this->db->rollback();
            throw new Exception('Error While Insert Data Disk for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

    public function setIoDataDisk(array $params, array $paramsDevices) {

        $this->getConnection();
        try {
            $this->db->transaction();
            foreach ($params['index'] as $index) {
                if (($params['disk_io_read'][$index] > 0) and ( $params['disk_io_write'][$index] > 0)) {
                    $this->db->insert("cod_device", $paramsDevices['cod_device']);
                    $this->db->insert("nam_device_disk", $params['device'][$index]);
                    $this->db->insert("num_disk_io_read", $params['disk_io_read'][$index]);
                    $this->db->insert("num_disk_io_write", $params['disk_io_write'][$index]);
                    $this->db->insert("num_disk_io_read_access", $params['disk_io_read_access'][$index]);
                    $this->db->insert("num_disk_io_write_access", $params['disk_io_write_access'][$index]);
                    $this->db->insert("num_disk_io_load1Min", $params['disk_io_load1Min'][$index]);
                    $this->db->insert("num_disk_io_load5Min", $params['disk_io_load5Min'][$index]);
                    $this->db->insert("num_disk_io_load15Min", $params['disk_io_load15Min'][$index]);
                    $this->db->insert("dte_register", date('Y-m-d H:i:s'));
                    $this->db->from('tab_data_disk_io', null, 'nocomdata');
                    $this->db->executeInsertQuery();
                }
            }
            $this->db->commit();
        } catch (Exception $exc) {
            $this->db->rollback();
            throw new Exception('Error While Insert Data Disk IO for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

}
