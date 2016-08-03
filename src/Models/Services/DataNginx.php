<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models\Services;

use Cityware\Monitoring\Models\AbstractModels;
/**
 * Description of DataNginx
 *
 * @author fsvxavier
 */
class DataNginx extends AbstractModels {
    
    public function setDataNginx(array $params, array $paramsDevices) {
        $this->getConnection();
        try {
            $this->db->transaction();
           
            foreach ($params as $key => $value) {
                $this->db->insert($key, (float) $value);
            }
            $this->db->insert("cod_device", $paramsDevices['cod_device']);
            $this->db->insert("dte_register", date('Y-m-d H:i:s'));
            $this->db->from('tab_data_serv_nginx', null, 'nocomdata');
            $this->db->setdebug(false);
            $this->db->executeInsertQuery();

            $this->db->commit();
        } catch (\Exception $exc) {
            $this->db->rollback();
            throw new \Exception('Error While Insert Data Service Nginx for JOB PARALLEL - ' . $exc->getMessage());
        }
    }
    
    public function getDataNginxCurrentHour(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MINUTE from dte_register) / 5)", 'slot', true);
        
        $this->db->select("avg(tdsn.num_active_connections)", 'avg_active_connections', true);
        $this->db->select("max(tdsn.num_active_connections)", 'max_active_connections', true);
        $this->db->select("sum(tdsn.num_active_connections)", 'sum_active_connections', true);
        
        $this->db->select("avg(tdsn.num_accepted_connections)", 'num_accepted_connections', true);
        $this->db->select("avg(tdsn.num_handled_connections)", 'num_handled_connections', true);
        $this->db->select("avg(tdsn.num_handled_requests)", 'num_handled_requests', true);
        $this->db->select("avg(tdsn.num_reading)", 'num_reading', true);
        $this->db->select("avg(tdsn.num_writing)", 'num_writing', true);
        $this->db->select("avg(tdsn.num_waiting)", 'num_waiting', true);
        
        $this->db->select("avg(tdsn.num_requests_connections)", 'avg_requests_connections', true);
        $this->db->select("max(tdsn.num_requests_connections)", 'max_requests_connections', true);
        $this->db->select("sum(tdsn.num_requests_connections)", 'sum_requests_connections', true);
        
        $this->db->select("avg(tdsn.num_keep_alive_connections)", 'num_keep_alive_connections', true);
        $this->db->from('tab_data_serv_nginx', 'tdsn', 'nocomdata');
        $this->db->where("tdsn.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdsn.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdsn.dte_register < '{$params['dte_finish']}'");
        $this->db->groupBy("1", true);
        $this->db->orderBy("1", true);
        $this->db->setDebug(false);
        $rsDataCpuLoadLastHour = $this->db->executeSelectQuery();
        
        $return = Array();

        foreach ($rsDataCpuLoadLastHour as $value) {
            $return[$value['slot']] = $value;
        }

        return $return;
    }

    public function getDataNginxCurrentDay(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(HOUR from dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdsn.num_active_connections)", 'num_active_connections', true);
        $this->db->select("avg(tdsn.num_accepted_connections)", 'num_accepted_connections', true);
        $this->db->select("avg(tdsn.num_handled_connections)", 'num_handled_connections', true);
        $this->db->select("avg(tdsn.num_handled_requests)", 'num_handled_requests', true);
        $this->db->select("avg(tdsn.num_reading)", 'num_reading', true);
        $this->db->select("avg(tdsn.num_writing)", 'num_writing', true);
        $this->db->select("avg(tdsn.num_waiting)", 'num_waiting', true);
        $this->db->select("avg(tdsn.num_requests_connections)", 'num_requests_connections', true);
        $this->db->select("avg(tdsn.num_keep_alive_connections)", 'num_keep_alive_connections', true);
        $this->db->from('tab_data_serv_nginx', 'tdsn', 'nocomdata');
        $this->db->where("tdsn.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdsn.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdsn.dte_register < '{$params['dte_finish']}'");
        $this->db->groupBy("1", true);
        $this->db->orderBy("1", true);
        $this->db->setDebug(false);
        $rsDataCpuLoadLastDay = $this->db->executeSelectQuery();

        $return = Array();

        foreach ($rsDataCpuLoadLastDay as $value) {
            $return[$value['slot']] = $value;
        }

        return $return;
    }

    public function getDataNginxCurrentMonth(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(DAY from dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdsn.num_active_connections)", 'num_active_connections', true);
        $this->db->select("avg(tdsn.num_accepted_connections)", 'num_accepted_connections', true);
        $this->db->select("avg(tdsn.num_handled_connections)", 'num_handled_connections', true);
        $this->db->select("avg(tdsn.num_handled_requests)", 'num_handled_requests', true);
        $this->db->select("avg(tdsn.num_reading)", 'num_reading', true);
        $this->db->select("avg(tdsn.num_writing)", 'num_writing', true);
        $this->db->select("avg(tdsn.num_waiting)", 'num_waiting', true);
        $this->db->select("avg(tdsn.num_requests_connections)", 'num_requests_connections', true);
        $this->db->select("avg(tdsn.num_keep_alive_connections)", 'num_keep_alive_connections', true);
        $this->db->from('tab_data_serv_nginx', 'tdsn', 'nocomdata');
        $this->db->where("tdsn.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdsn.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdsn.dte_register < '{$params['dte_finish']}'");
        $this->db->groupBy("1", true);
        $this->db->orderBy("1", true);
        $this->db->setDebug(false);
        $rsDataCpuLoadLastMonth = $this->db->executeSelectQuery();

        $return = Array();

        foreach ($rsDataCpuLoadLastMonth as $value) {
            $return[$value['slot']] = $value;
        }

        return $return;
    }

    public function getDataNginxCurrentYear(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MONTH from dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdsn.num_active_connections)", 'num_active_connections', true);
        $this->db->select("avg(tdsn.num_accepted_connections)", 'num_accepted_connections', true);
        $this->db->select("avg(tdsn.num_handled_connections)", 'num_handled_connections', true);
        $this->db->select("avg(tdsn.num_handled_requests)", 'num_handled_requests', true);
        $this->db->select("avg(tdsn.num_reading)", 'num_reading', true);
        $this->db->select("avg(tdsn.num_writing)", 'num_writing', true);
        $this->db->select("avg(tdsn.num_waiting)", 'num_waiting', true);
        $this->db->select("avg(tdsn.num_requests_connections)", 'num_requests_connections', true);
        $this->db->select("avg(tdsn.num_keep_alive_connections)", 'num_keep_alive_connections', true);
        $this->db->from('tab_data_serv_nginx', 'tdsn', 'nocomdata');
        $this->db->where("tdsn.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdsn.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdsn.dte_register < '{$params['dte_finish']}'");
        $this->db->groupBy("1", true);
        $this->db->orderBy("1", true);
        $this->db->setDebug(false);
        $rsDataCpuLoadLastYear = $this->db->executeSelectQuery();
        
        $return = Array();

        foreach ($rsDataCpuLoadLastYear as $value) {
            $return[$value['slot']] = $value;
        }

        return $return;
    }

}
