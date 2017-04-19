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

    public function setDataPhpFpmDb(array $params, array $paramsDevices) {
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

    public function setDataPhpFpm(array $params, array $paramsDevices) {
        $this->getConnection();
        try {
            $this->db->sequence('gen_data_serv_phpfpm', 'nocomdata');
            $id = $this->db->executeSequence();

            $paramsInsert = [
                'index' => 'nocom',
                'type' => 'tab_data_serv_phpfpm',
                'id' => $id['0']['nextval'],
                'body' => [
                    "cod_device" => $paramsDevices['cod_device'],
                    "dte_register" => date('Y-m-d H:i:s'),
                ],
            ];

            $paramsInsert['body'] = array_merge($paramsInsert['body'], $params);

            $ret = $this->es->index($paramsInsert);
        } catch (\Exception $exc) {
            throw new \Exception('Error While Insert Data Service PhpFpm for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

    public function getDataPhpFpmCurrentHourDb(array $params) {
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

    public function getDataPhpFpmCurrentHour(array $params) {
        $this->getConnection();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_serv_phpfpm',
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
                //"sort" => [["dte_register" => ["order" => "desc"]]],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "5m"
                        ],
                        "aggs" => [
                            "num_accepted_connections_avg" => [
                                "sum" => [
                                    "field" => "num_accepted_connections"
                                ],
                            ],
                            "num_listen_queue_avg" => [
                                "avg" => [
                                    "field" => "num_listen_queue"
                                ],
                            ],
                            "num_max_listen_queue_avg" => [
                                "avg" => [
                                    "field" => "num_max_listen_queue"
                                ],
                            ],
                            "num_listen_queue_len_avg" => [
                                "avg" => [
                                    "field" => "num_listen_queue_len"
                                ],
                            ],
                            "num_active_processes_avg" => [
                                "avg" => [
                                    "field" => "num_active_processes"
                                ],
                            ],
                            "num_idle_processes_avg" => [
                                "avg" => [
                                    "field" => "num_idle_processes"
                                ],
                            ],
                            "num_max_active_processes_avg" => [
                                "avg" => [
                                    "field" => "num_max_active_processes"
                                ],
                            ],
                            "num_slow_requests_avg" => [
                                "avg" => [
                                    "field" => "num_slow_requests"
                                ],
                            ],
                            "num_max_children_reached_avg" => [
                                "avg" => [
                                    "field" => "num_max_children_reached"
                                ],
                            ],
                            "num_total_processes_avg" => [
                                "avg" => [
                                    "field" => "num_total_processes"
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
            $return[$key]['num_accepted_connections'] = $value['num_accepted_connections_avg']['value'];
            $return[$key]['num_listen_queue'] = $value['num_listen_queue_avg']['value'];
            $return[$key]['num_max_listen_queue'] = $value['num_max_listen_queue_avg']['value'];
            $return[$key]['num_listen_queue_len'] = $value['num_listen_queue_len_avg']['value'];
            $return[$key]['num_active_processes'] = $value['num_active_processes_avg']['value'];
            $return[$key]['num_idle_processes'] = $value['num_idle_processes_avg']['value'];
            $return[$key]['num_max_active_processes'] = $value['num_max_active_processes_avg']['value'];
            $return[$key]['num_slow_requests'] = $value['num_slow_requests_avg']['value'];
            $return[$key]['num_max_children_reached'] = $value['num_max_children_reached_avg']['value'];
            $return[$key]['num_total_processes'] = $value['num_total_processes_avg']['value'];
        }

        return $return;
    }

    public function getDataPhpFpmCurrentDayDb(array $params) {
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

    public function getDataPhpFpmCurrentDay(array $params) {
        $this->getConnection();
        $dateOperations = new \Cityware\Format\DateOperations();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_serv_phpfpm',
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
                //"sort" => [["dte_register" => ["order" => "desc"]]],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "hour"
                        ],
                        "aggs" => [
                            "num_accepted_connections_avg" => [
                                "sum" => [
                                    "field" => "num_accepted_connections"
                                ],
                            ],
                            "num_listen_queue_avg" => [
                                "avg" => [
                                    "field" => "num_listen_queue"
                                ],
                            ],
                            "num_max_listen_queue_avg" => [
                                "avg" => [
                                    "field" => "num_max_listen_queue"
                                ],
                            ],
                            "num_listen_queue_len_avg" => [
                                "avg" => [
                                    "field" => "num_listen_queue_len"
                                ],
                            ],
                            "num_active_processes_avg" => [
                                "avg" => [
                                    "field" => "num_active_processes"
                                ],
                            ],
                            "num_idle_processes_avg" => [
                                "avg" => [
                                    "field" => "num_idle_processes"
                                ],
                            ],
                            "num_max_active_processes_avg" => [
                                "avg" => [
                                    "field" => "num_max_active_processes"
                                ],
                            ],
                            "num_slow_requests_avg" => [
                                "avg" => [
                                    "field" => "num_slow_requests"
                                ],
                            ],
                            "num_max_children_reached_avg" => [
                                "avg" => [
                                    "field" => "num_max_children_reached"
                                ],
                            ],
                            "num_total_processes_avg" => [
                                "avg" => [
                                    "field" => "num_total_processes"
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
            $return[$keyDate]['num_accepted_connections'] = $value['num_accepted_connections_avg']['value'];
            $return[$keyDate]['num_listen_queue'] = $value['num_listen_queue_avg']['value'];
            $return[$keyDate]['num_max_listen_queue'] = $value['num_max_listen_queue_avg']['value'];
            $return[$keyDate]['num_listen_queue_len'] = $value['num_listen_queue_len_avg']['value'];
            $return[$keyDate]['num_active_processes'] = $value['num_active_processes_avg']['value'];
            $return[$keyDate]['num_idle_processes'] = $value['num_idle_processes_avg']['value'];
            $return[$keyDate]['num_max_active_processes'] = $value['num_max_active_processes_avg']['value'];
            $return[$keyDate]['num_slow_requests'] = $value['num_slow_requests_avg']['value'];
            $return[$keyDate]['num_max_children_reached'] = $value['num_max_children_reached_avg']['value'];
            $return[$keyDate]['num_total_processes'] = $value['num_total_processes_avg']['value'];
        }

        return $return;
    }

    public function getDataPhpFpmCurrentMonthDb(array $params) {
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

    public function getDataPhpFpmCurrentMonth(array $params) {
        $this->getConnection();
        $dateOperations = new \Cityware\Format\DateOperations();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_serv_phpfpm',
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
                //"sort" => [["dte_register" => ["order" => "desc"]]],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "day"
                        ],
                        "aggs" => [
                            "num_accepted_connections_avg" => [
                                "sum" => [
                                    "field" => "num_accepted_connections"
                                ],
                            ],
                            "num_listen_queue_avg" => [
                                "avg" => [
                                    "field" => "num_listen_queue"
                                ],
                            ],
                            "num_max_listen_queue_avg" => [
                                "avg" => [
                                    "field" => "num_max_listen_queue"
                                ],
                            ],
                            "num_listen_queue_len_avg" => [
                                "avg" => [
                                    "field" => "num_listen_queue_len"
                                ],
                            ],
                            "num_active_processes_avg" => [
                                "avg" => [
                                    "field" => "num_active_processes"
                                ],
                            ],
                            "num_idle_processes_avg" => [
                                "avg" => [
                                    "field" => "num_idle_processes"
                                ],
                            ],
                            "num_max_active_processes_avg" => [
                                "avg" => [
                                    "field" => "num_max_active_processes"
                                ],
                            ],
                            "num_slow_requests_avg" => [
                                "avg" => [
                                    "field" => "num_slow_requests"
                                ],
                            ],
                            "num_max_children_reached_avg" => [
                                "avg" => [
                                    "field" => "num_max_children_reached"
                                ],
                            ],
                            "num_total_processes_avg" => [
                                "avg" => [
                                    "field" => "num_total_processes"
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
            $return[$keyDate]['num_accepted_connections'] = $value['num_accepted_connections_avg']['value'];
            $return[$keyDate]['num_listen_queue'] = $value['num_listen_queue_avg']['value'];
            $return[$keyDate]['num_max_listen_queue'] = $value['num_max_listen_queue_avg']['value'];
            $return[$keyDate]['num_listen_queue_len'] = $value['num_listen_queue_len_avg']['value'];
            $return[$keyDate]['num_active_processes'] = $value['num_active_processes_avg']['value'];
            $return[$keyDate]['num_idle_processes'] = $value['num_idle_processes_avg']['value'];
            $return[$keyDate]['num_max_active_processes'] = $value['num_max_active_processes_avg']['value'];
            $return[$keyDate]['num_slow_requests'] = $value['num_slow_requests_avg']['value'];
            $return[$keyDate]['num_max_children_reached'] = $value['num_max_children_reached_avg']['value'];
            $return[$keyDate]['num_total_processes'] = $value['num_total_processes_avg']['value'];
        }

        return $return;
    }

    public function getDataPhpFpmCurrentYearDb(array $params) {
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

    public function getDataPhpFpmCurrentYear(array $params) {
        $this->getConnection();
        $dateOperations = new \Cityware\Format\DateOperations();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_serv_phpfpm',
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
                //"sort" => [["dte_register" => ["order" => "desc"]]],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "month"
                        ],
                        "aggs" => [
                            "num_accepted_connections_avg" => [
                                "sum" => [
                                    "field" => "num_accepted_connections"
                                ],
                            ],
                            "num_listen_queue_avg" => [
                                "avg" => [
                                    "field" => "num_listen_queue"
                                ],
                            ],
                            "num_max_listen_queue_avg" => [
                                "avg" => [
                                    "field" => "num_max_listen_queue"
                                ],
                            ],
                            "num_listen_queue_len_avg" => [
                                "avg" => [
                                    "field" => "num_listen_queue_len"
                                ],
                            ],
                            "num_active_processes_avg" => [
                                "avg" => [
                                    "field" => "num_active_processes"
                                ],
                            ],
                            "num_idle_processes_avg" => [
                                "avg" => [
                                    "field" => "num_idle_processes"
                                ],
                            ],
                            "num_max_active_processes_avg" => [
                                "avg" => [
                                    "field" => "num_max_active_processes"
                                ],
                            ],
                            "num_slow_requests_avg" => [
                                "avg" => [
                                    "field" => "num_slow_requests"
                                ],
                            ],
                            "num_max_children_reached_avg" => [
                                "avg" => [
                                    "field" => "num_max_children_reached"
                                ],
                            ],
                            "num_total_processes_avg" => [
                                "avg" => [
                                    "field" => "num_total_processes"
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
            $return[$keyDate]['num_accepted_connections'] = $value['num_accepted_connections_avg']['value'];
            $return[$keyDate]['num_listen_queue'] = $value['num_listen_queue_avg']['value'];
            $return[$keyDate]['num_max_listen_queue'] = $value['num_max_listen_queue_avg']['value'];
            $return[$keyDate]['num_listen_queue_len'] = $value['num_listen_queue_len_avg']['value'];
            $return[$keyDate]['num_active_processes'] = $value['num_active_processes_avg']['value'];
            $return[$keyDate]['num_idle_processes'] = $value['num_idle_processes_avg']['value'];
            $return[$keyDate]['num_max_active_processes'] = $value['num_max_active_processes_avg']['value'];
            $return[$keyDate]['num_slow_requests'] = $value['num_slow_requests_avg']['value'];
            $return[$keyDate]['num_max_children_reached'] = $value['num_max_children_reached_avg']['value'];
            $return[$keyDate]['num_total_processes'] = $value['num_total_processes_avg']['value'];
        }

        return $return;
    }

}
