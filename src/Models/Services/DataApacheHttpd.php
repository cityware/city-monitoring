<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models\Services;

use Cityware\Monitoring\Models\AbstractModels;

/**
 * Description of DataCpu
 *
 * @author fsvxavier
 */
class DataApacheHttpd extends AbstractModels {

    public function setDataApacheHttpd(array $params, array $paramsDevices) {
        $this->getConnection();
        try {
            $this->db->transaction();
           
            foreach ($params as $key => $value) {
                $this->db->insert($key, (float) $value);
            }
            $this->db->insert("cod_device", $paramsDevices['cod_device']);
            $this->db->insert("dte_register", date('Y-m-d H:i:s'));
            $this->db->from('tab_data_serv_apache', null, 'nocomdata');
            $this->db->setdebug(false);
            $this->db->executeInsertQuery();

            $this->db->commit();
        } catch (\Exception $exc) {
            $this->db->rollback();
            throw new \Exception('Error While Insert Data Service Apache Httpd for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

    public function getDataApacheHttpdCurrentHour(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MINUTE from dte_register) / 5)", 'slot', true);
        $this->db->select("avg(tdsa.num_total_accesses)", 'num_total_accesses', true);
        $this->db->select("avg(tdsa.num_total_bytes)", 'num_total_bytes', true);
        $this->db->select("avg(tdsa.num_cpu_load)", 'num_cpu_load', true);
        $this->db->select("avg(tdsa.num_requests_seconds)", 'num_requests_seconds', true);
        $this->db->select("avg(tdsa.num_bytes_seconds)", 'num_bytes_seconds', true);
        $this->db->select("avg(tdsa.num_bytes_requests)", 'num_bytes_requests', true);
        $this->db->select("avg(tdsa.num_busy_workers)", 'num_busy_workers', true);
        $this->db->select("avg(tdsa.num_idle_workers)", 'num_idle_workers', true);
        $this->db->select("avg(tdsa.num_waiting_connection)", 'num_waiting_connection', true);
        $this->db->select("avg(tdsa.num_starting_up)", 'num_starting_up', true);
        $this->db->select("avg(tdsa.num_reading_request)", 'num_reading_request', true);
        $this->db->select("avg(tdsa.num_sending_reply)", 'num_sending_reply', true);
        $this->db->select("avg(tdsa.num_keepalive_read)", 'num_keepalive_read', true);
        $this->db->select("avg(tdsa.num_dns_lookup)", 'num_dns_lookup', true);
        $this->db->select("avg(tdsa.num_closing_connection)", 'num_closing_connection', true);
        $this->db->select("avg(tdsa.num_logging)", 'num_logging', true);
        $this->db->select("avg(tdsa.num_gracefully_finishing)", 'num_gracefully_finishing', true);
        $this->db->select("avg(tdsa.num_idle_cleanup_worker)", 'num_idle_cleanup_worker', true);
        $this->db->select("avg(tdsa.num_open_slot_no_current_process)", 'num_open_slot_no_current_process', true);
        $this->db->from('tab_data_serv_apache', 'tdsa', 'nocomdata');
        $this->db->where("tdsa.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdsa.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdsa.dte_register < '{$params['dte_finish']}'");
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

    public function getDataApacheHttpdCurrentDay(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(HOUR from dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdsa.num_total_accesses)", 'num_total_accesses', true);
        $this->db->select("avg(tdsa.num_total_bytes)", 'num_total_bytes', true);
        $this->db->select("avg(tdsa.num_cpu_load)", 'num_cpu_load', true);
        $this->db->select("avg(tdsa.num_requests_seconds)", 'num_requests_seconds', true);
        $this->db->select("avg(tdsa.num_bytes_seconds)", 'num_bytes_seconds', true);
        $this->db->select("avg(tdsa.num_bytes_requests)", 'num_bytes_requests', true);
        $this->db->select("avg(tdsa.num_busy_workers)", 'num_busy_workers', true);
        $this->db->select("avg(tdsa.num_idle_workers)", 'num_idle_workers', true);
        $this->db->select("avg(tdsa.num_waiting_connection)", 'num_waiting_connection', true);
        $this->db->select("avg(tdsa.num_starting_up)", 'num_starting_up', true);
        $this->db->select("avg(tdsa.num_reading_request)", 'num_reading_request', true);
        $this->db->select("avg(tdsa.num_sending_reply)", 'num_sending_reply', true);
        $this->db->select("avg(tdsa.num_keepalive_read)", 'num_keepalive_read', true);
        $this->db->select("avg(tdsa.num_dns_lookup)", 'num_dns_lookup', true);
        $this->db->select("avg(tdsa.num_closing_connection)", 'num_closing_connection', true);
        $this->db->select("avg(tdsa.num_logging)", 'num_logging', true);
        $this->db->select("avg(tdsa.num_gracefully_finishing)", 'num_gracefully_finishing', true);
        $this->db->select("avg(tdsa.num_idle_cleanup_worker)", 'num_idle_cleanup_worker', true);
        $this->db->select("avg(tdsa.num_open_slot_no_current_process)", 'num_open_slot_no_current_process', true);
        $this->db->from('tab_data_serv_apache', 'tdsa', 'nocomdata');
        $this->db->where("tdsa.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdsa.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdsa.dte_register < '{$params['dte_finish']}'");
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

    public function getDataApacheHttpdCurrentMonth(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(DAY from dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdsa.num_total_accesses)", 'num_total_accesses', true);
        $this->db->select("avg(tdsa.num_total_bytes)", 'num_total_bytes', true);
        $this->db->select("avg(tdsa.num_cpu_load)", 'num_cpu_load', true);
        $this->db->select("avg(tdsa.num_requests_seconds)", 'num_requests_seconds', true);
        $this->db->select("avg(tdsa.num_bytes_seconds)", 'num_bytes_seconds', true);
        $this->db->select("avg(tdsa.num_bytes_requests)", 'num_bytes_requests', true);
        $this->db->select("avg(tdsa.num_busy_workers)", 'num_busy_workers', true);
        $this->db->select("avg(tdsa.num_idle_workers)", 'num_idle_workers', true);
        $this->db->select("avg(tdsa.num_waiting_connection)", 'num_waiting_connection', true);
        $this->db->select("avg(tdsa.num_starting_up)", 'num_starting_up', true);
        $this->db->select("avg(tdsa.num_reading_request)", 'num_reading_request', true);
        $this->db->select("avg(tdsa.num_sending_reply)", 'num_sending_reply', true);
        $this->db->select("avg(tdsa.num_keepalive_read)", 'num_keepalive_read', true);
        $this->db->select("avg(tdsa.num_dns_lookup)", 'num_dns_lookup', true);
        $this->db->select("avg(tdsa.num_closing_connection)", 'num_closing_connection', true);
        $this->db->select("avg(tdsa.num_logging)", 'num_logging', true);
        $this->db->select("avg(tdsa.num_gracefully_finishing)", 'num_gracefully_finishing', true);
        $this->db->select("avg(tdsa.num_idle_cleanup_worker)", 'num_idle_cleanup_worker', true);
        $this->db->select("avg(tdsa.num_open_slot_no_current_process)", 'num_open_slot_no_current_process', true);
        $this->db->from('tab_data_serv_apache', 'tdsa', 'nocomdata');
        $this->db->where("tdsa.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdsa.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdsa.dte_register < '{$params['dte_finish']}'");
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

    public function getDataApacheHttpdCurrentYear(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MONTH from dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdsa.num_total_accesses)", 'num_total_accesses', true);
        $this->db->select("avg(tdsa.num_total_bytes)", 'num_total_bytes', true);
        $this->db->select("avg(tdsa.num_cpu_load)", 'num_cpu_load', true);
        $this->db->select("avg(tdsa.num_requests_seconds)", 'num_requests_seconds', true);
        $this->db->select("avg(tdsa.num_bytes_seconds)", 'num_bytes_seconds', true);
        $this->db->select("avg(tdsa.num_bytes_requests)", 'num_bytes_requests', true);
        $this->db->select("avg(tdsa.num_busy_workers)", 'num_busy_workers', true);
        $this->db->select("avg(tdsa.num_idle_workers)", 'num_idle_workers', true);
        $this->db->select("avg(tdsa.num_waiting_connection)", 'num_waiting_connection', true);
        $this->db->select("avg(tdsa.num_starting_up)", 'num_starting_up', true);
        $this->db->select("avg(tdsa.num_reading_request)", 'num_reading_request', true);
        $this->db->select("avg(tdsa.num_sending_reply)", 'num_sending_reply', true);
        $this->db->select("avg(tdsa.num_keepalive_read)", 'num_keepalive_read', true);
        $this->db->select("avg(tdsa.num_dns_lookup)", 'num_dns_lookup', true);
        $this->db->select("avg(tdsa.num_closing_connection)", 'num_closing_connection', true);
        $this->db->select("avg(tdsa.num_logging)", 'num_logging', true);
        $this->db->select("avg(tdsa.num_gracefully_finishing)", 'num_gracefully_finishing', true);
        $this->db->select("avg(tdsa.num_idle_cleanup_worker)", 'num_idle_cleanup_worker', true);
        $this->db->select("avg(tdsa.num_open_slot_no_current_process)", 'num_open_slot_no_current_process', true);
        $this->db->from('tab_data_serv_apache', 'tdsa', 'nocomdata');
        $this->db->where("tdsa.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdsa.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdsa.dte_register < '{$params['dte_finish']}'");
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
