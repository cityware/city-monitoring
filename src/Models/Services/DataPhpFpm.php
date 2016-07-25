<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models\Services;

use Cityware\Monitoring\Models\AbstractModels;
/**
 * Description of DataPhpFpm
 *
 * @author fsvxavier
 */
class DataPhpFpm extends AbstractModels {
    
    public function setDataPhpFpm(array $params, array $paramsDevices) {
        $this->getConnection();
        try {
            $this->db->transaction();
           
            foreach ($params as $key => $value) {
                $this->db->insert($key, $value);
            }
            $this->db->insert("cod_device", $paramsDevices['cod_device']);
            $this->db->insert("dte_register", date('Y-m-d H:i:s'));
            $this->db->from('tab_data_serv_phpfpm', null, 'nocomdata');
            $this->db->setdebug(false);
            $this->db->executeInsertQuery();

            $this->db->commit();
        } catch (\Exception $exc) {
            $this->db->rollback();
            throw new \Exception('Error While Insert Data Service Nginx for JOB PARALLEL - ' . $exc->getMessage());
        }
    }
}
