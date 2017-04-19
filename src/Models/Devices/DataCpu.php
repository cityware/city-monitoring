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

    public function setDataCpuDb(array $params, array $paramsDevices) {
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

    public function setDataCpu(array $params, array $paramsDevices) {
        $this->getConnection();
        try {

            $this->db->sequence('gen_data_cpu', 'nocomdata');
            $id = $this->db->executeSequence();

            $paramsInsert = [
                'index' => 'nocom',
                'type' => 'tab_data_cpu',
                'id' => $id['0']['nextval'],
                'body' => [
                    "cod_device" => $paramsDevices['cod_device'],
                    "num_load_one_min" => $params['oneMinute'],
                    "num_load_five_min" => $params['fiveMinute'],
                    "num_load_fifteen_min" => $params['fifteenMinute'],
                    "num_load_percentage" => $params['loadPercentage'],
                    "num_threshoud_cpu_slots" => $paramsDevices['num_slot_processors'],
                    "num_threshoud_cpu_cores" => $paramsDevices['num_core_processors'],
                    "num_threshoud_cpu_ht" => $paramsDevices['ind_hyper_threading'],
                    "dte_register" => date('Y-m-d H:i:s'),
                ],
            ];

            $ret = $this->es->index($paramsInsert);
        } catch (Exception $exc) {
            throw new Exception('Error While Insert Data CPU for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

    public function getDataCpuLoadCurrentHourDb(array $params) {
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

    public function getDataCpuLoadCurrentHour(array $params) {
        $this->getConnection();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_cpu',
            'size' => '0',
            'body' => [
                'query' => [
                    "bool" => [
                        'must' => [
                            'term' => [
                                'cod_device' => $params['cod_device'],
                            ],
                        ],
                        'filter' => [
                            "range" => [
                                "dte_register" => [
                                    "gte" => $params['dte_start'],
                                    "lte" => $params['dte_finish'],
                                ],
                            ],
                        ],
                    ],
                ],
                //"sort" => [["dte_register" =>["order" => "desc"]]],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "5m"
                        ],
                        "aggs" => [
                            "num_load_one_min_avg" => [
                                "avg" => [
                                    "field" => "num_load_one_min"
                                ],
                            ],
                            "num_load_five_min_avg" => [
                                "avg" => [
                                    "field" => "num_load_five_min"
                                ],
                            ],
                            "num_load_fifteen_min_avg" => [
                                "avg" => [
                                    "field" => "num_load_fifteen_min"
                                ],
                            ],
                            "num_load_percentage_avg" => [
                                "avg" => [
                                    "field" => "num_load_percentage"
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $resultEs = $this->es->search($paramsEs);

        $return = [];

        foreach ($resultEs['aggregations']['peer5Minutes']['buckets'] as $key => $value) {
            $return[$key]['slot'] = $key;
            $return[$key]['key'] = $value['key_as_string'];
            $return[$key]['num_load_one_min'] = $value['num_load_one_min_avg']['value'];
            $return[$key]['num_load_five_min'] = $value['num_load_five_min_avg']['value'];
            $return[$key]['num_load_fifteen_min'] = $value['num_load_fifteen_min_avg']['value'];
            $return[$key]['num_load_percentage'] = $value['num_load_percentage_avg']['value'];
        }

        return $return;
    }

    public function getDataCpuLoadCurrentDayDb(array $params) {
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

    public function getDataCpuLoadCurrentDay(array $params) {
        $this->getConnection();
        $dateOperations = new \Cityware\Format\DateOperations();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_cpu',
            'size' => '0',
            'body' => [
                'query' => [
                    "bool" => [
                        'must' => [
                            'term' => [
                                'cod_device' => $params['cod_device'],
                            ],
                        ],
                        'filter' => [
                            "range" => [
                                "dte_register" => [
                                    "gte" => $params['dte_start'],
                                    "lte" => $params['dte_finish'],
                                ],
                            ],
                        ],
                    ],
                ],
                //"sort" => [["dte_register" =>["order" => "desc"]]],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "hour"
                        ],
                        "aggs" => [
                            "num_load_one_min_avg" => [
                                "avg" => [
                                    "field" => "num_load_one_min"
                                ],
                            ],
                            "num_load_five_min_avg" => [
                                "avg" => [
                                    "field" => "num_load_five_min"
                                ],
                            ],
                            "num_load_fifteen_min_avg" => [
                                "avg" => [
                                    "field" => "num_load_fifteen_min"
                                ],
                            ],
                            "num_load_percentage_avg" => [
                                "avg" => [
                                    "field" => "num_load_percentage"
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $resultEs = $this->es->search($paramsEs);

        $return = [];

        foreach ($resultEs['aggregations']['peer5Minutes']['buckets'] as $key => $value) {

            $keyDate = (int) $dateOperations->setDateTime($value['key_as_string'])->format('H');

            $return[$keyDate]['slot'] = $keyDate;
            $return[$keyDate]['key'] = $value['key_as_string'];
            $return[$keyDate]['num_load_one_min'] = $value['num_load_one_min_avg']['value'];
            $return[$keyDate]['num_load_five_min'] = $value['num_load_five_min_avg']['value'];
            $return[$keyDate]['num_load_fifteen_min'] = $value['num_load_fifteen_min_avg']['value'];
            $return[$keyDate]['num_load_percentage'] = $value['num_load_percentage_avg']['value'];
        }

        return $return;
    }

    public function getDataCpuLoadCurrentMonthDb(array $params) {
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

    public function getDataCpuLoadCurrentMonth(array $params) {
        $this->getConnection();
        $dateOperations = new \Cityware\Format\DateOperations();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_cpu',
            'size' => '0',
            'body' => [
                'query' => [
                    "bool" => [
                        'must' => [
                            'term' => [
                                'cod_device' => $params['cod_device'],
                            ],
                        ],
                        'filter' => [
                            "range" => [
                                "dte_register" => [
                                    "gte" => $params['dte_start'],
                                    "lte" => $params['dte_finish'],
                                ],
                            ],
                        ],
                    ],
                ],
                //"sort" => [["dte_register" =>["order" => "desc"]]],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "day"
                        ],
                        "aggs" => [
                            "num_load_one_min_avg" => [
                                "avg" => [
                                    "field" => "num_load_one_min"
                                ],
                            ],
                            "num_load_five_min_avg" => [
                                "avg" => [
                                    "field" => "num_load_five_min"
                                ],
                            ],
                            "num_load_fifteen_min_avg" => [
                                "avg" => [
                                    "field" => "num_load_fifteen_min"
                                ],
                            ],
                            "num_load_percentage_avg" => [
                                "avg" => [
                                    "field" => "num_load_percentage"
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $resultEs = $this->es->search($paramsEs);




        $return = [];

        foreach ($resultEs['aggregations']['peer5Minutes']['buckets'] as $key => $value) {

            $keyDate = (int) $dateOperations->setDateTime($value['key_as_string'])->format('d');

            $return[$keyDate]['slot'] = $keyDate;
            $return[$keyDate]['key'] = $value['key_as_string'];
            $return[$keyDate]['num_load_one_min'] = $value['num_load_one_min_avg']['value'];
            $return[$keyDate]['num_load_five_min'] = $value['num_load_five_min_avg']['value'];
            $return[$keyDate]['num_load_fifteen_min'] = $value['num_load_fifteen_min_avg']['value'];
            $return[$keyDate]['num_load_percentage'] = $value['num_load_percentage_avg']['value'];
        }

        return $return;
    }

    public function getDataCpuLoadCurrentYearDb(array $params) {
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

    public function getDataCpuLoadCurrentYear(array $params) {
        $this->getConnection();
        $dateOperations = new \Cityware\Format\DateOperations();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_cpu',
            'size' => '0',
            'body' => [
                'query' => [
                    "bool" => [
                        'must' => [
                            'term' => [
                                'cod_device' => $params['cod_device'],
                            ],
                        ],
                        'filter' => [
                            "range" => [
                                "dte_register" => [
                                    "gte" => $params['dte_start'],
                                    "lte" => $params['dte_finish'],
                                ],
                            ],
                        ],
                    ],
                ],
                //"sort" => [["dte_register" =>["order" => "desc"]]],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "month"
                        ],
                        "aggs" => [
                            "num_load_one_min_avg" => [
                                "avg" => [
                                    "field" => "num_load_one_min"
                                ],
                            ],
                            "num_load_five_min_avg" => [
                                "avg" => [
                                    "field" => "num_load_five_min"
                                ],
                            ],
                            "num_load_fifteen_min_avg" => [
                                "avg" => [
                                    "field" => "num_load_fifteen_min"
                                ],
                            ],
                            "num_load_percentage_avg" => [
                                "avg" => [
                                    "field" => "num_load_percentage"
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $resultEs = $this->es->search($paramsEs);

        $return = [];

        foreach ($resultEs['aggregations']['peer5Minutes']['buckets'] as $key => $value) {

            $keyDate = (int) $dateOperations->setDateTime($value['key_as_string'])->format('m');

            $return[$keyDate]['slot'] = $keyDate;
            $return[$keyDate]['key'] = $value['key_as_string'];
            $return[$keyDate]['num_load_one_min'] = $value['num_load_one_min_avg']['value'];
            $return[$keyDate]['num_load_five_min'] = $value['num_load_five_min_avg']['value'];
            $return[$keyDate]['num_load_fifteen_min'] = $value['num_load_fifteen_min_avg']['value'];
            $return[$keyDate]['num_load_percentage'] = $value['num_load_percentage_avg']['value'];
        }

        return $return;
    }

}
