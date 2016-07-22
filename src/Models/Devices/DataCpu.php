<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models\Devices;
use Cityware\Monitoring\Models\AbstractModels;

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
            $this->db->insert("num_load_percentage", $params['loadPercentage']);
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

    public function getDataCpuLoadCurrentHour(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MINUTE from dte_register) / 5)", 'slot', true);
        $this->db->select("avg(tdc.num_load_one_min)", 'num_load_one_min', true);
        $this->db->select("avg(tdc.num_load_five_min)", 'num_load_five_min', true);
        $this->db->select("avg(tdc.num_load_fifteen_min)", 'num_load_fifteen_min', true);
        $this->db->select("avg(tdc.num_load_percentage)", 'num_load_percentage', true);
        $this->db->from('tab_data_cpu', 'tdc', 'nocomdata');
        $this->db->where("tdc.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdc.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdc.dte_register < '{$params['dte_finish']}'");
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

    public function getDataCpuLoadCurrentDay(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(HOUR from dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdc.num_load_one_min)", 'num_load_one_min', true);
        $this->db->select("avg(tdc.num_load_five_min)", 'num_load_five_min', true);
        $this->db->select("avg(tdc.num_load_fifteen_min)", 'num_load_fifteen_min', true);
        $this->db->select("avg(tdc.num_load_percentage)", 'num_load_percentage', true);
        $this->db->from('tab_data_cpu', 'tdc', 'nocomdata');
        $this->db->where("tdc.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdc.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdc.dte_register < '{$params['dte_finish']}'");
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

    public function getDataCpuLoadCurrentMonth(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(DAY from dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdc.num_load_one_min)", 'num_load_one_min', true);
        $this->db->select("avg(tdc.num_load_five_min)", 'num_load_five_min', true);
        $this->db->select("avg(tdc.num_load_fifteen_min)", 'num_load_fifteen_min', true);
        $this->db->select("avg(tdc.num_load_percentage)", 'num_load_percentage', true);
        $this->db->from('tab_data_cpu', 'tdc', 'nocomdata');
        $this->db->where("tdc.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdc.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdc.dte_register < '{$params['dte_finish']}'");
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

    public function getDataCpuLoadCurrentYear(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MONTH from dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdc.num_load_one_min)", 'num_load_one_min', true);
        $this->db->select("avg(tdc.num_load_five_min)", 'num_load_five_min', true);
        $this->db->select("avg(tdc.num_load_fifteen_min)", 'num_load_fifteen_min', true);
        $this->db->select("avg(tdc.num_load_percentage)", 'num_load_percentage', true);
        $this->db->from('tab_data_cpu', 'tdc', 'nocomdata');
        $this->db->where("tdc.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdc.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdc.dte_register < '{$params['dte_finish']}'");
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
