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
            throw new \Exception('Error While Insert Data Service PhpFpm for JOB PARALLEL - ' . $exc->getMessage());
        }
    }
    
    public function getDataPhpFpmCurrentHour(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MINUTE from dte_register) / 5)", 'slot', true);
        $this->db->select("avg(tdsp.num_accepted_connections)", 'num_accepted_connections', true);
        $this->db->select("avg(tdsp.num_listen_queue)", 'num_listen_queue', true);
        $this->db->select("avg(tdsp.num_max_listen_queue)", 'num_max_listen_queue', true);
        $this->db->select("avg(tdsp.num_listen_queue_len)", 'num_listen_queue_len', true);
        $this->db->select("avg(tdsp.num_active_processes)", 'num_active_processes', true);
        $this->db->select("avg(tdsp.num_idle_processes)", 'num_idle_processes', true);
        $this->db->select("avg(tdsp.num_max_active_processes)", 'num_max_active_processes', true);
        $this->db->select("avg(tdsp.num_slow_requests)", 'num_slow_requests', true);
        $this->db->select("avg(tdsp.num_max_children_reached)", 'num_max_children_reached', true);
        $this->db->select("avg(tdsp.num_total_processes)", 'num_total_processes', true);
        $this->db->from('tab_data_serv_phpfpm', 'tdsp', 'nocomdata');
        $this->db->where("tdsp.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdsp.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdsp.dte_register < '{$params['dte_finish']}'");
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

    public function getDataPhpFpmCurrentDay(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(HOUR from dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdsp.num_accepted_connections)", 'num_accepted_connections', true);
        $this->db->select("avg(tdsp.num_listen_queue)", 'num_listen_queue', true);
        $this->db->select("avg(tdsp.num_max_listen_queue)", 'num_max_listen_queue', true);
        $this->db->select("avg(tdsp.num_listen_queue_len)", 'num_listen_queue_len', true);
        $this->db->select("avg(tdsp.num_active_processes)", 'num_active_processes', true);
        $this->db->select("avg(tdsp.num_idle_processes)", 'num_idle_processes', true);
        $this->db->select("avg(tdsp.num_max_active_processes)", 'num_max_active_processes', true);
        $this->db->select("avg(tdsp.num_slow_requests)", 'num_slow_requests', true);
        $this->db->select("avg(tdsp.num_max_children_reached)", 'num_max_children_reached', true);
        $this->db->select("avg(tdsp.num_total_processes)", 'num_total_processes', true);
        $this->db->from('tab_data_serv_phpfpm', 'tdsp', 'nocomdata');
        $this->db->where("tdsp.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdsp.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdsp.dte_register < '{$params['dte_finish']}'");
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

    public function getDataPhpFpmCurrentMonth(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(DAY from dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdsp.num_accepted_connections)", 'num_accepted_connections', true);
        $this->db->select("avg(tdsp.num_listen_queue)", 'num_listen_queue', true);
        $this->db->select("avg(tdsp.num_max_listen_queue)", 'num_max_listen_queue', true);
        $this->db->select("avg(tdsp.num_listen_queue_len)", 'num_listen_queue_len', true);
        $this->db->select("avg(tdsp.num_active_processes)", 'num_active_processes', true);
        $this->db->select("avg(tdsp.num_idle_processes)", 'num_idle_processes', true);
        $this->db->select("avg(tdsp.num_max_active_processes)", 'num_max_active_processes', true);
        $this->db->select("avg(tdsp.num_slow_requests)", 'num_slow_requests', true);
        $this->db->select("avg(tdsp.num_max_children_reached)", 'num_max_children_reached', true);
        $this->db->select("avg(tdsp.num_total_processes)", 'num_total_processes', true);
        $this->db->from('tab_data_serv_phpfpm', 'tdsp', 'nocomdata');
        $this->db->where("tdsp.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdsp.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdsp.dte_register < '{$params['dte_finish']}'");
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

    public function getDataPhpFpmCurrentYear(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MONTH from dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdsp.num_accepted_connections)", 'num_accepted_connections', true);
        $this->db->select("avg(tdsp.num_listen_queue)", 'num_listen_queue', true);
        $this->db->select("avg(tdsp.num_max_listen_queue)", 'num_max_listen_queue', true);
        $this->db->select("avg(tdsp.num_listen_queue_len)", 'num_listen_queue_len', true);
        $this->db->select("avg(tdsp.num_active_processes)", 'num_active_processes', true);
        $this->db->select("avg(tdsp.num_idle_processes)", 'num_idle_processes', true);
        $this->db->select("avg(tdsp.num_max_active_processes)", 'num_max_active_processes', true);
        $this->db->select("avg(tdsp.num_slow_requests)", 'num_slow_requests', true);
        $this->db->select("avg(tdsp.num_max_children_reached)", 'num_max_children_reached', true);
        $this->db->select("avg(tdsp.num_total_processes)", 'num_total_processes', true);
        $this->db->from('tab_data_serv_phpfpm', 'tdsp', 'nocomdata');
        $this->db->where("tdsp.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdsp.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdsp.dte_register < '{$params['dte_finish']}'");
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
