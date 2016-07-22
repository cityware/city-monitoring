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

    public function getDataApacheHttpdCurrentDay(array $params) {
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

    public function getDataApacheHttpdCurrentMonth(array $params) {
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

    public function getDataApacheHttpdCurrentYear(array $params) {
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
