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

    public function setDataApacheHttpdDb(array $params, array $paramsDevices) {
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
    
    public function setDataApacheHttpd(array $params, array $paramsDevices) {
        $this->getConnection();
        try {
            $this->db->sequence('gen_data_serv_apache', 'nocomdata');
            $id = $this->db->executeSequence();

            $paramsInsert = [
                'index' => 'nocom',
                'type' => 'tab_data_serv_apache',
                'id' => $id['0']['nextval'],
                'body' => [
                    "cod_device" => $paramsDevices['cod_device'],
                    $params,
                    "dte_register" => date('Y-m-d H:i:s'),
                ],
            ];

            $ret = $this->es->index($paramsInsert);
        } catch (\Exception $exc) {
            throw new \Exception('Error While Insert Data Service Apache Httpd for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

    public function getDataApacheHttpdCurrentHourDb(array $params) {
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
    
    public function getDataApacheHttpdCurrentHour(array $params) {
        $this->getConnection();
        
        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_serv_apache',
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
                "sort" => [["dte_register" =>["order" => "desc"]]],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "5m"
                        ],
                        "aggs" => [
                            "num_total_accesses_avg" => [
                                "avg" => [
                                    "field" => "num_total_accesses"
                                ],
                            ],
                            "num_total_bytes_avg" => [
                                "avg" => [
                                    "field" => "num_total_bytes"
                                ],
                            ],
                            "num_cpu_load_avg" => [
                                "avg" => [
                                    "field" => "num_cpu_load"
                                ],
                            ],
                            "num_requests_seconds_avg" => [
                                "avg" => [
                                    "field" => "num_requests_seconds"
                                ],
                            ],
                            "num_bytes_seconds_avg" => [
                                "avg" => [
                                    "field" => "num_bytes_seconds"
                                ],
                            ],
                            "num_bytes_requests_avg" => [
                                "avg" => [
                                    "field" => "num_bytes_requests"
                                ],
                            ],
                            "num_busy_workers_avg" => [
                                "avg" => [
                                    "field" => "num_busy_workers"
                                ],
                            ],
                            "num_idle_workers_avg" => [
                                "avg" => [
                                    "field" => "num_idle_workers"
                                ],
                            ],
                            "num_waiting_connection_avg" => [
                                "avg" => [
                                    "field" => "num_waiting_connection"
                                ],
                            ],
                            "num_starting_up_avg" => [
                                "avg" => [
                                    "field" => "num_starting_up"
                                ],
                            ],
                            "num_reading_request_avg" => [
                                "avg" => [
                                    "field" => "num_reading_request"
                                ],
                            ],
                            "num_sending_reply_avg" => [
                                "avg" => [
                                    "field" => "num_sending_reply"
                                ],
                            ],
                            "num_keepalive_read_avg" => [
                                "avg" => [
                                    "field" => "num_keepalive_read"
                                ],
                            ],
                            "num_dns_lookup_avg" => [
                                "avg" => [
                                    "field" => "num_dns_lookup"
                                ],
                            ],
                            "num_closing_connection_avg" => [
                                "avg" => [
                                    "field" => "num_closing_connection"
                                ],
                            ],
                            "num_logging_avg" => [
                                "avg" => [
                                    "field" => "num_logging"
                                ],
                            ],
                            "num_gracefully_finishing_avg" => [
                                "avg" => [
                                    "field" => "num_gracefully_finishing"
                                ],
                            ],
                            "num_idle_cleanup_worker_avg" => [
                                "avg" => [
                                    "field" => "num_idle_cleanup_worker"
                                ],
                            ],
                            "num_open_slot_no_current_process_avg" => [
                                "avg" => [
                                    "field" => "num_open_slot_no_current_process"
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
            $return[$key]['num_total_accesses'] = $value['num_total_accesses_avg']['value'];
            $return[$key]['num_total_bytes'] = $value['num_total_bytes_avg']['value'];
            $return[$key]['num_cpu_load'] = $value['num_cpu_load_avg']['value'];
            $return[$key]['num_requests_seconds'] = $value['num_requests_seconds_avg']['value'];
            $return[$key]['num_bytes_seconds'] = $value['num_bytes_seconds_avg']['value'];
            $return[$key]['num_bytes_requests'] = $value['num_bytes_requests_avg']['value'];
            $return[$key]['num_busy_workers'] = $value['num_busy_workers_avg']['value'];
            $return[$key]['num_idle_workers'] = $value['num_idle_workers_avg']['value'];
            $return[$key]['num_waiting_connection'] = $value['num_waiting_connection_avg']['value'];
            $return[$key]['num_starting_up'] = $value['num_starting_up_avg']['value'];
            $return[$key]['num_reading_request'] = $value['num_reading_request_avg']['value'];
            $return[$key]['num_sending_reply'] = $value['num_sending_reply_avg']['value'];
            $return[$key]['num_keepalive_read'] = $value['num_keepalive_read_avg']['value'];
            $return[$key]['num_dns_lookup'] = $value['num_dns_lookup_avg']['value'];
            $return[$key]['num_closing_connection'] = $value['num_closing_connection_avg']['value'];
            $return[$key]['num_logging'] = $value['num_logging_avg']['value'];
            $return[$key]['num_gracefully_finishing'] = $value['num_gracefully_finishing_avg']['value'];
            $return[$key]['num_idle_cleanup_worker'] = $value['num_idle_cleanup_worker_avg']['value'];
            $return[$key]['num_open_slot_no_current_process'] = $value['num_open_slot_no_current_process_avg']['value'];
            
        }

        return $return;
    }

    public function getDataApacheHttpdCurrentDayDb(array $params) {
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
    
    public function getDataApacheHttpdCurrentDay(array $params) {
        $this->getConnection();
        
        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_serv_apache',
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
                "sort" => [["dte_register" =>["order" => "desc"]]],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "hour"
                        ],
                        "aggs" => [
                            "num_total_accesses_avg" => [
                                "avg" => [
                                    "field" => "num_total_accesses"
                                ],
                            ],
                            "num_total_bytes_avg" => [
                                "avg" => [
                                    "field" => "num_total_bytes"
                                ],
                            ],
                            "num_cpu_load_avg" => [
                                "avg" => [
                                    "field" => "num_cpu_load"
                                ],
                            ],
                            "num_requests_seconds_avg" => [
                                "avg" => [
                                    "field" => "num_requests_seconds"
                                ],
                            ],
                            "num_bytes_seconds_avg" => [
                                "avg" => [
                                    "field" => "num_bytes_seconds"
                                ],
                            ],
                            "num_bytes_requests_avg" => [
                                "avg" => [
                                    "field" => "num_bytes_requests"
                                ],
                            ],
                            "num_busy_workers_avg" => [
                                "avg" => [
                                    "field" => "num_busy_workers"
                                ],
                            ],
                            "num_idle_workers_avg" => [
                                "avg" => [
                                    "field" => "num_idle_workers"
                                ],
                            ],
                            "num_waiting_connection_avg" => [
                                "avg" => [
                                    "field" => "num_waiting_connection"
                                ],
                            ],
                            "num_starting_up_avg" => [
                                "avg" => [
                                    "field" => "num_starting_up"
                                ],
                            ],
                            "num_reading_request_avg" => [
                                "avg" => [
                                    "field" => "num_reading_request"
                                ],
                            ],
                            "num_sending_reply_avg" => [
                                "avg" => [
                                    "field" => "num_sending_reply"
                                ],
                            ],
                            "num_keepalive_read_avg" => [
                                "avg" => [
                                    "field" => "num_keepalive_read"
                                ],
                            ],
                            "num_dns_lookup_avg" => [
                                "avg" => [
                                    "field" => "num_dns_lookup"
                                ],
                            ],
                            "num_closing_connection_avg" => [
                                "avg" => [
                                    "field" => "num_closing_connection"
                                ],
                            ],
                            "num_logging_avg" => [
                                "avg" => [
                                    "field" => "num_logging"
                                ],
                            ],
                            "num_gracefully_finishing_avg" => [
                                "avg" => [
                                    "field" => "num_gracefully_finishing"
                                ],
                            ],
                            "num_idle_cleanup_worker_avg" => [
                                "avg" => [
                                    "field" => "num_idle_cleanup_worker"
                                ],
                            ],
                            "num_open_slot_no_current_process_avg" => [
                                "avg" => [
                                    "field" => "num_open_slot_no_current_process"
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
            $return[$key]['num_total_accesses'] = $value['num_total_accesses_avg']['value'];
            $return[$key]['num_total_bytes'] = $value['num_total_bytes_avg']['value'];
            $return[$key]['num_cpu_load'] = $value['num_cpu_load_avg']['value'];
            $return[$key]['num_requests_seconds'] = $value['num_requests_seconds_avg']['value'];
            $return[$key]['num_bytes_seconds'] = $value['num_bytes_seconds_avg']['value'];
            $return[$key]['num_bytes_requests'] = $value['num_bytes_requests_avg']['value'];
            $return[$key]['num_busy_workers'] = $value['num_busy_workers_avg']['value'];
            $return[$key]['num_idle_workers'] = $value['num_idle_workers_avg']['value'];
            $return[$key]['num_waiting_connection'] = $value['num_waiting_connection_avg']['value'];
            $return[$key]['num_starting_up'] = $value['num_starting_up_avg']['value'];
            $return[$key]['num_reading_request'] = $value['num_reading_request_avg']['value'];
            $return[$key]['num_sending_reply'] = $value['num_sending_reply_avg']['value'];
            $return[$key]['num_keepalive_read'] = $value['num_keepalive_read_avg']['value'];
            $return[$key]['num_dns_lookup'] = $value['num_dns_lookup_avg']['value'];
            $return[$key]['num_closing_connection'] = $value['num_closing_connection_avg']['value'];
            $return[$key]['num_logging'] = $value['num_logging_avg']['value'];
            $return[$key]['num_gracefully_finishing'] = $value['num_gracefully_finishing_avg']['value'];
            $return[$key]['num_idle_cleanup_worker'] = $value['num_idle_cleanup_worker_avg']['value'];
            $return[$key]['num_open_slot_no_current_process'] = $value['num_open_slot_no_current_process_avg']['value'];
            
        }

        return $return;
    }

    public function getDataApacheHttpdCurrentMonthDb(array $params) {
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
    
    public function getDataApacheHttpdCurrentMonth(array $params) {
        $this->getConnection();
        
        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_serv_apache',
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
                "sort" => [["dte_register" =>["order" => "desc"]]],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "day"
                        ],
                        "aggs" => [
                            "num_total_accesses_avg" => [
                                "avg" => [
                                    "field" => "num_total_accesses"
                                ],
                            ],
                            "num_total_bytes_avg" => [
                                "avg" => [
                                    "field" => "num_total_bytes"
                                ],
                            ],
                            "num_cpu_load_avg" => [
                                "avg" => [
                                    "field" => "num_cpu_load"
                                ],
                            ],
                            "num_requests_seconds_avg" => [
                                "avg" => [
                                    "field" => "num_requests_seconds"
                                ],
                            ],
                            "num_bytes_seconds_avg" => [
                                "avg" => [
                                    "field" => "num_bytes_seconds"
                                ],
                            ],
                            "num_bytes_requests_avg" => [
                                "avg" => [
                                    "field" => "num_bytes_requests"
                                ],
                            ],
                            "num_busy_workers_avg" => [
                                "avg" => [
                                    "field" => "num_busy_workers"
                                ],
                            ],
                            "num_idle_workers_avg" => [
                                "avg" => [
                                    "field" => "num_idle_workers"
                                ],
                            ],
                            "num_waiting_connection_avg" => [
                                "avg" => [
                                    "field" => "num_waiting_connection"
                                ],
                            ],
                            "num_starting_up_avg" => [
                                "avg" => [
                                    "field" => "num_starting_up"
                                ],
                            ],
                            "num_reading_request_avg" => [
                                "avg" => [
                                    "field" => "num_reading_request"
                                ],
                            ],
                            "num_sending_reply_avg" => [
                                "avg" => [
                                    "field" => "num_sending_reply"
                                ],
                            ],
                            "num_keepalive_read_avg" => [
                                "avg" => [
                                    "field" => "num_keepalive_read"
                                ],
                            ],
                            "num_dns_lookup_avg" => [
                                "avg" => [
                                    "field" => "num_dns_lookup"
                                ],
                            ],
                            "num_closing_connection_avg" => [
                                "avg" => [
                                    "field" => "num_closing_connection"
                                ],
                            ],
                            "num_logging_avg" => [
                                "avg" => [
                                    "field" => "num_logging"
                                ],
                            ],
                            "num_gracefully_finishing_avg" => [
                                "avg" => [
                                    "field" => "num_gracefully_finishing"
                                ],
                            ],
                            "num_idle_cleanup_worker_avg" => [
                                "avg" => [
                                    "field" => "num_idle_cleanup_worker"
                                ],
                            ],
                            "num_open_slot_no_current_process_avg" => [
                                "avg" => [
                                    "field" => "num_open_slot_no_current_process"
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
            $return[$key]['num_total_accesses'] = $value['num_total_accesses_avg']['value'];
            $return[$key]['num_total_bytes'] = $value['num_total_bytes_avg']['value'];
            $return[$key]['num_cpu_load'] = $value['num_cpu_load_avg']['value'];
            $return[$key]['num_requests_seconds'] = $value['num_requests_seconds_avg']['value'];
            $return[$key]['num_bytes_seconds'] = $value['num_bytes_seconds_avg']['value'];
            $return[$key]['num_bytes_requests'] = $value['num_bytes_requests_avg']['value'];
            $return[$key]['num_busy_workers'] = $value['num_busy_workers_avg']['value'];
            $return[$key]['num_idle_workers'] = $value['num_idle_workers_avg']['value'];
            $return[$key]['num_waiting_connection'] = $value['num_waiting_connection_avg']['value'];
            $return[$key]['num_starting_up'] = $value['num_starting_up_avg']['value'];
            $return[$key]['num_reading_request'] = $value['num_reading_request_avg']['value'];
            $return[$key]['num_sending_reply'] = $value['num_sending_reply_avg']['value'];
            $return[$key]['num_keepalive_read'] = $value['num_keepalive_read_avg']['value'];
            $return[$key]['num_dns_lookup'] = $value['num_dns_lookup_avg']['value'];
            $return[$key]['num_closing_connection'] = $value['num_closing_connection_avg']['value'];
            $return[$key]['num_logging'] = $value['num_logging_avg']['value'];
            $return[$key]['num_gracefully_finishing'] = $value['num_gracefully_finishing_avg']['value'];
            $return[$key]['num_idle_cleanup_worker'] = $value['num_idle_cleanup_worker_avg']['value'];
            $return[$key]['num_open_slot_no_current_process'] = $value['num_open_slot_no_current_process_avg']['value'];
            
        }

        return $return;
    }

    public function getDataApacheHttpdCurrentYearDb(array $params) {
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

    public function getDataApacheHttpdCurrentYear(array $params) {
        $this->getConnection();
        
        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_serv_apache',
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
                "sort" => [["dte_register" =>["order" => "desc"]]],
                "aggs" => [
                    "peer5Minutes" => [
                        "date_histogram" => [
                            "field" => "dte_register",
                            "interval" => "month"
                        ],
                        "aggs" => [
                            "num_total_accesses_avg" => [
                                "avg" => [
                                    "field" => "num_total_accesses"
                                ],
                            ],
                            "num_total_bytes_avg" => [
                                "avg" => [
                                    "field" => "num_total_bytes"
                                ],
                            ],
                            "num_cpu_load_avg" => [
                                "avg" => [
                                    "field" => "num_cpu_load"
                                ],
                            ],
                            "num_requests_seconds_avg" => [
                                "avg" => [
                                    "field" => "num_requests_seconds"
                                ],
                            ],
                            "num_bytes_seconds_avg" => [
                                "avg" => [
                                    "field" => "num_bytes_seconds"
                                ],
                            ],
                            "num_bytes_requests_avg" => [
                                "avg" => [
                                    "field" => "num_bytes_requests"
                                ],
                            ],
                            "num_busy_workers_avg" => [
                                "avg" => [
                                    "field" => "num_busy_workers"
                                ],
                            ],
                            "num_idle_workers_avg" => [
                                "avg" => [
                                    "field" => "num_idle_workers"
                                ],
                            ],
                            "num_waiting_connection_avg" => [
                                "avg" => [
                                    "field" => "num_waiting_connection"
                                ],
                            ],
                            "num_starting_up_avg" => [
                                "avg" => [
                                    "field" => "num_starting_up"
                                ],
                            ],
                            "num_reading_request_avg" => [
                                "avg" => [
                                    "field" => "num_reading_request"
                                ],
                            ],
                            "num_sending_reply_avg" => [
                                "avg" => [
                                    "field" => "num_sending_reply"
                                ],
                            ],
                            "num_keepalive_read_avg" => [
                                "avg" => [
                                    "field" => "num_keepalive_read"
                                ],
                            ],
                            "num_dns_lookup_avg" => [
                                "avg" => [
                                    "field" => "num_dns_lookup"
                                ],
                            ],
                            "num_closing_connection_avg" => [
                                "avg" => [
                                    "field" => "num_closing_connection"
                                ],
                            ],
                            "num_logging_avg" => [
                                "avg" => [
                                    "field" => "num_logging"
                                ],
                            ],
                            "num_gracefully_finishing_avg" => [
                                "avg" => [
                                    "field" => "num_gracefully_finishing"
                                ],
                            ],
                            "num_idle_cleanup_worker_avg" => [
                                "avg" => [
                                    "field" => "num_idle_cleanup_worker"
                                ],
                            ],
                            "num_open_slot_no_current_process_avg" => [
                                "avg" => [
                                    "field" => "num_open_slot_no_current_process"
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
            $return[$key]['num_total_accesses'] = $value['num_total_accesses_avg']['value'];
            $return[$key]['num_total_bytes'] = $value['num_total_bytes_avg']['value'];
            $return[$key]['num_cpu_load'] = $value['num_cpu_load_avg']['value'];
            $return[$key]['num_requests_seconds'] = $value['num_requests_seconds_avg']['value'];
            $return[$key]['num_bytes_seconds'] = $value['num_bytes_seconds_avg']['value'];
            $return[$key]['num_bytes_requests'] = $value['num_bytes_requests_avg']['value'];
            $return[$key]['num_busy_workers'] = $value['num_busy_workers_avg']['value'];
            $return[$key]['num_idle_workers'] = $value['num_idle_workers_avg']['value'];
            $return[$key]['num_waiting_connection'] = $value['num_waiting_connection_avg']['value'];
            $return[$key]['num_starting_up'] = $value['num_starting_up_avg']['value'];
            $return[$key]['num_reading_request'] = $value['num_reading_request_avg']['value'];
            $return[$key]['num_sending_reply'] = $value['num_sending_reply_avg']['value'];
            $return[$key]['num_keepalive_read'] = $value['num_keepalive_read_avg']['value'];
            $return[$key]['num_dns_lookup'] = $value['num_dns_lookup_avg']['value'];
            $return[$key]['num_closing_connection'] = $value['num_closing_connection_avg']['value'];
            $return[$key]['num_logging'] = $value['num_logging_avg']['value'];
            $return[$key]['num_gracefully_finishing'] = $value['num_gracefully_finishing_avg']['value'];
            $return[$key]['num_idle_cleanup_worker'] = $value['num_idle_cleanup_worker_avg']['value'];
            $return[$key]['num_open_slot_no_current_process'] = $value['num_open_slot_no_current_process_avg']['value'];
            
        }

        return $return;
    }

}
