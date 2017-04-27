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

    public function setDataNginxDb(array $params, array $paramsDevices) {
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

    public function setDataNginx(array $params, array $paramsDevices) {
        $this->getConnection();

        try {
            $this->db->sequence('gen_data_serv_nginx', 'nocomdata');
            $id = $this->db->executeSequence();

            $paramsInsert = [
                'index' => 'nocom',
                'type' => 'tab_data_serv_nginx',
                'id' => $id['0']['nextval'],
                'body' => [
                    "cod_device" => $paramsDevices['cod_device'],
                    "dte_register" => date('Y-m-d H:i:s'),
                ],
            ];

            $paramsInsert['body'] = array_merge($paramsInsert['body'], $params);

            $ret = $this->es->index($paramsInsert);
        } catch (\Exception $exc) {
            throw new \Exception('Error While Insert Data Service Nginx for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

    public function getDataNginxCurrentHourDb(array $params) {
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

    public function getDataNginxCurrentHour(array $params) {
        $this->getConnection();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_serv_nginx',
            'size' => '0',
            'body' => [
                'query' => [
                    "bool" => [
                        'filter' => [
                            'term' => [
                                'cod_device' => $params['cod_device'],
                            ],
                        ],
                        'must' => [
                            "range" => [
                                "dte_register" => [
                                    "gte" => $params['dte_start'],
                                    "lte" => $params['dte_finish'],
                                ],
                            ],
                        ],
                    ],
                ],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "5m"
                        ],
                        "aggs" => [
                            "num_active_connections_sum" => [
                                "sum" => [
                                    "field" => "num_active_connections"
                                ],
                            ],
                            "num_active_connections_avg" => [
                                "avg" => [
                                    "field" => "num_active_connections"
                                ],
                            ],
                            "num_active_connections_max" => [
                                "max" => [
                                    "field" => "num_active_connections"
                                ],
                            ],
                            "num_requests_connections_avg" => [
                                "avg" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_requests_connections_sum" => [
                                "sum" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_requests_connections_max" => [
                                "max" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_accepted_connections_avg" => [
                                "avg" => [
                                    "field" => "num_accepted_connections"
                                ],
                            ],
                            "num_handled_connections_avg" => [
                                "avg" => [
                                    "field" => "num_handled_connections"
                                ],
                            ],
                            "num_handled_requests_avg" => [
                                "avg" => [
                                    "field" => "num_handled_requests"
                                ],
                            ],
                            "num_reading_avg" => [
                                "avg" => [
                                    "field" => "num_reading"
                                ],
                            ],
                            "num_writing_avg" => [
                                "avg" => [
                                    "field" => "num_writing"
                                ],
                            ],
                            "num_waiting_avg" => [
                                "avg" => [
                                    "field" => "num_waiting"
                                ],
                            ],
                            "num_requests_connections_avg" => [
                                "avg" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_keep_alive_connections_avg" => [
                                "avg" => [
                                    "field" => "num_keep_alive_connections"
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
            $return[$key]['avg_active_connections'] = $value['num_active_connections_avg']['value'];
            $return[$key]['sum_active_connections'] = $value['num_active_connections_sum']['value'];
            $return[$key]['num_active_connections'] = $value['num_active_connections_sum']['value'];
            $return[$key]['max_active_connections'] = $value['num_active_connections_max']['value'];
            $return[$key]['avg_requests_connections'] = $value['num_requests_connections_avg']['value'];
            $return[$key]['sum_requests_connections'] = $value['num_requests_connections_sum']['value'];
            $return[$key]['num_requests_connections'] = $value['num_requests_connections_sum']['value'];
            $return[$key]['max_requests_connections'] = $value['num_requests_connections_max']['value'];
            $return[$key]['num_accepted_connections'] = $value['num_accepted_connections_avg']['value'];
            $return[$key]['num_handled_connections'] = $value['num_handled_connections_avg']['value'];
            $return[$key]['num_handled_requests'] = $value['num_handled_requests_avg']['value'];
            $return[$key]['num_reading'] = $value['num_reading_avg']['value'];
            $return[$key]['num_writing'] = $value['num_writing_avg']['value'];
            $return[$key]['num_waiting'] = $value['num_waiting_avg']['value'];
            $return[$key]['num_keep_alive_connections'] = $value['num_keep_alive_connections_avg']['value'];
        }

        return $return;
    }

    public function getDataNginxCurrentDayDb(array $params) {
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

    public function getDataNginxCurrentDay(array $params) {
        $this->getConnection();
        $dateOperations = new \Cityware\Format\DateOperations();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_serv_nginx',
            'size' => '0',
            'body' => [
                'query' => [
                    "bool" => [
                        'filter' => [
                            'term' => [
                                'cod_device' => $params['cod_device'],
                            ],
                        ],
                        'must' => [
                            "range" => [
                                "dte_register" => [
                                    "gte" => $params['dte_start'],
                                    "lte" => $params['dte_finish'],
                                ],
                            ],
                        ],
                    ],
                ],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "hour"
                        ],
                        "aggs" => [
                            "num_active_connections_sum" => [
                                "sum" => [
                                    "field" => "num_active_connections"
                                ],
                            ],
                            "num_active_connections_avg" => [
                                "avg" => [
                                    "field" => "num_active_connections"
                                ],
                            ],
                            "num_active_connections_max" => [
                                "max" => [
                                    "field" => "num_active_connections"
                                ],
                            ],
                            "num_requests_connections_avg" => [
                                "avg" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_requests_connections_sum" => [
                                "sum" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_requests_connections_max" => [
                                "max" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_accepted_connections_avg" => [
                                "avg" => [
                                    "field" => "num_accepted_connections"
                                ],
                            ],
                            "num_handled_connections_avg" => [
                                "avg" => [
                                    "field" => "num_handled_connections"
                                ],
                            ],
                            "num_handled_requests_avg" => [
                                "avg" => [
                                    "field" => "num_handled_requests"
                                ],
                            ],
                            "num_reading_avg" => [
                                "avg" => [
                                    "field" => "num_reading"
                                ],
                            ],
                            "num_writing_avg" => [
                                "avg" => [
                                    "field" => "num_writing"
                                ],
                            ],
                            "num_waiting_avg" => [
                                "avg" => [
                                    "field" => "num_waiting"
                                ],
                            ],
                            "num_requests_connections_avg" => [
                                "avg" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_keep_alive_connections_avg" => [
                                "avg" => [
                                    "field" => "num_keep_alive_connections"
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
            $return[$keyDate]['avg_active_connections'] = $value['num_active_connections_avg']['value'];
            $return[$keyDate]['sum_active_connections'] = $value['num_active_connections_sum']['value'];
            $return[$keyDate]['num_active_connections'] = $value['num_active_connections_sum']['value'];
            $return[$keyDate]['max_active_connections'] = $value['num_active_connections_max']['value'];
            $return[$keyDate]['avg_requests_connections'] = $value['num_requests_connections_avg']['value'];
            $return[$keyDate]['sum_requests_connections'] = $value['num_requests_connections_sum']['value'];
            $return[$keyDate]['num_requests_connections'] = $value['num_requests_connections_sum']['value'];
            $return[$keyDate]['max_requests_connections'] = $value['num_requests_connections_max']['value'];
            $return[$keyDate]['num_accepted_connections'] = $value['num_accepted_connections_avg']['value'];
            $return[$keyDate]['num_handled_connections'] = $value['num_handled_connections_avg']['value'];
            $return[$keyDate]['num_handled_requests'] = $value['num_handled_requests_avg']['value'];
            $return[$keyDate]['num_reading'] = $value['num_reading_avg']['value'];
            $return[$keyDate]['num_writing'] = $value['num_writing_avg']['value'];
            $return[$keyDate]['num_waiting'] = $value['num_waiting_avg']['value'];
            $return[$keyDate]['num_keep_alive_connections'] = $value['num_keep_alive_connections_avg']['value'];
        }

        return $return;
    }

    public function getDataNginxCurrentMonthDb(array $params) {
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

    public function getDataNginxCurrentMonth(array $params) {
        $this->getConnection();
        $dateOperations = new \Cityware\Format\DateOperations();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_serv_nginx',
            'size' => '0',
            'body' => [
                'query' => [
                    "bool" => [
                        'filter' => [
                            'term' => [
                                'cod_device' => $params['cod_device'],
                            ],
                        ],
                        'must' => [
                            "range" => [
                                "dte_register" => [
                                    "gte" => $params['dte_start'],
                                    "lte" => $params['dte_finish'],
                                ],
                            ],
                        ],
                    ],
                ],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "day"
                        ],
                        "aggs" => [
                            "num_active_connections_sum" => [
                                "sum" => [
                                    "field" => "num_active_connections"
                                ],
                            ],
                            "num_active_connections_avg" => [
                                "avg" => [
                                    "field" => "num_active_connections"
                                ],
                            ],
                            "num_active_connections_max" => [
                                "max" => [
                                    "field" => "num_active_connections"
                                ],
                            ],
                            "num_requests_connections_avg" => [
                                "avg" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_requests_connections_sum" => [
                                "sum" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_requests_connections_max" => [
                                "max" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_accepted_connections_avg" => [
                                "avg" => [
                                    "field" => "num_accepted_connections"
                                ],
                            ],
                            "num_handled_connections_avg" => [
                                "avg" => [
                                    "field" => "num_handled_connections"
                                ],
                            ],
                            "num_handled_requests_avg" => [
                                "avg" => [
                                    "field" => "num_handled_requests"
                                ],
                            ],
                            "num_reading_avg" => [
                                "avg" => [
                                    "field" => "num_reading"
                                ],
                            ],
                            "num_writing_avg" => [
                                "avg" => [
                                    "field" => "num_writing"
                                ],
                            ],
                            "num_waiting_avg" => [
                                "avg" => [
                                    "field" => "num_waiting"
                                ],
                            ],
                            "num_requests_connections_avg" => [
                                "avg" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_keep_alive_connections_avg" => [
                                "avg" => [
                                    "field" => "num_keep_alive_connections"
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
            $return[$keyDate]['avg_active_connections'] = $value['num_active_connections_avg']['value'];
            $return[$keyDate]['sum_active_connections'] = $value['num_active_connections_sum']['value'];
            $return[$keyDate]['num_active_connections'] = $value['num_active_connections_sum']['value'];
            $return[$keyDate]['max_active_connections'] = $value['num_active_connections_max']['value'];
            $return[$keyDate]['avg_requests_connections'] = $value['num_requests_connections_avg']['value'];
            $return[$keyDate]['sum_requests_connections'] = $value['num_requests_connections_sum']['value'];
            $return[$keyDate]['num_requests_connections'] = $value['num_requests_connections_sum']['value'];
            $return[$keyDate]['max_requests_connections'] = $value['num_requests_connections_max']['value'];
            $return[$keyDate]['num_accepted_connections'] = $value['num_accepted_connections_avg']['value'];
            $return[$keyDate]['num_handled_connections'] = $value['num_handled_connections_avg']['value'];
            $return[$keyDate]['num_handled_requests'] = $value['num_handled_requests_avg']['value'];
            $return[$keyDate]['num_reading'] = $value['num_reading_avg']['value'];
            $return[$keyDate]['num_writing'] = $value['num_writing_avg']['value'];
            $return[$keyDate]['num_waiting'] = $value['num_waiting_avg']['value'];
            $return[$keyDate]['num_keep_alive_connections'] = $value['num_keep_alive_connections_avg']['value'];
        }

        return $return;
    }

    public function getDataNginxCurrentYearDb(array $params) {
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

    public function getDataNginxCurrentYear(array $params) {

        $this->getConnection();
        $dateOperations = new \Cityware\Format\DateOperations();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_serv_nginx',
            'size' => '0',
            'body' => [
                'query' => [
                    "bool" => [
                        'filter' => [
                            'term' => [
                                'cod_device' => $params['cod_device'],
                            ],
                        ],
                        'must' => [
                            "range" => [
                                "dte_register" => [
                                    "gte" => $params['dte_start'],
                                    "lte" => $params['dte_finish'],
                                ],
                            ],
                        ],
                    ],
                ],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "month"
                        ],
                        "aggs" => [
                            "num_active_connections_sum" => [
                                "sum" => [
                                    "field" => "num_active_connections"
                                ],
                            ],
                            "num_active_connections_avg" => [
                                "avg" => [
                                    "field" => "num_active_connections"
                                ],
                            ],
                            "num_active_connections_max" => [
                                "max" => [
                                    "field" => "num_active_connections"
                                ],
                            ],
                            "num_requests_connections_avg" => [
                                "avg" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_requests_connections_sum" => [
                                "sum" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_requests_connections_max" => [
                                "max" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_accepted_connections_avg" => [
                                "avg" => [
                                    "field" => "num_accepted_connections"
                                ],
                            ],
                            "num_handled_connections_avg" => [
                                "avg" => [
                                    "field" => "num_handled_connections"
                                ],
                            ],
                            "num_handled_requests_avg" => [
                                "avg" => [
                                    "field" => "num_handled_requests"
                                ],
                            ],
                            "num_reading_avg" => [
                                "avg" => [
                                    "field" => "num_reading"
                                ],
                            ],
                            "num_writing_avg" => [
                                "avg" => [
                                    "field" => "num_writing"
                                ],
                            ],
                            "num_waiting_avg" => [
                                "avg" => [
                                    "field" => "num_waiting"
                                ],
                            ],
                            "num_requests_connections_avg" => [
                                "avg" => [
                                    "field" => "num_requests_connections"
                                ],
                            ],
                            "num_keep_alive_connections_avg" => [
                                "avg" => [
                                    "field" => "num_keep_alive_connections"
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $resultEs = $this->es->search($paramsEs);

        $return = [];

        foreach ($resultEs['aggregations']['peer5Minutes']['buckets'] as $value) {

            $keyDate = (int) $dateOperations->setDateTime($value['key_as_string'])->format('m');

            $return[$keyDate]['slot'] = $keyDate;
            $return[$keyDate]['key'] = $value['key_as_string'];
            $return[$keyDate]['avg_active_connections'] = $value['num_active_connections_avg']['value'];
            $return[$keyDate]['sum_active_connections'] = $value['num_active_connections_sum']['value'];
            $return[$keyDate]['num_active_connections'] = $value['num_active_connections_sum']['value'];
            $return[$keyDate]['max_active_connections'] = $value['num_active_connections_max']['value'];
            $return[$keyDate]['avg_requests_connections'] = $value['num_requests_connections_avg']['value'];
            $return[$keyDate]['sum_requests_connections'] = $value['num_requests_connections_sum']['value'];
            $return[$keyDate]['num_requests_connections'] = $value['num_requests_connections_sum']['value'];
            $return[$keyDate]['max_requests_connections'] = $value['num_requests_connections_max']['value'];
            $return[$keyDate]['num_accepted_connections'] = $value['num_accepted_connections_avg']['value'];
            $return[$keyDate]['num_handled_connections'] = $value['num_handled_connections_avg']['value'];
            $return[$keyDate]['num_handled_requests'] = $value['num_handled_requests_avg']['value'];
            $return[$keyDate]['num_reading'] = $value['num_reading_avg']['value'];
            $return[$keyDate]['num_writing'] = $value['num_writing_avg']['value'];
            $return[$keyDate]['num_waiting'] = $value['num_waiting_avg']['value'];
            $return[$keyDate]['num_keep_alive_connections'] = $value['num_keep_alive_connections_avg']['value'];
        }

        return $return;
    }

}
