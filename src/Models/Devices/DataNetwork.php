<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models\Devices;

use Cityware\Monitoring\Models\AbstractModels;

/**
 * Description of DataNetwork
 *
 * @author fsvxavier
 */
class DataNetwork extends AbstractModels {

    public function setDataNetwork(array $params, array $paramsDevices) {


        $this->getConnection();
        try {
            if (isset($params['index'])) {

                $paramsLastData = Array();

                foreach ($params['index'] as $index) {
                    if (isset($params['phys_address'][$index]) and ! empty($params['phys_address'][$index])) {
                        $paramsLastData[$paramsDevices['cod_device']][$params['phys_address'][$index]] = $this->getLastDataNetworkByDevice($paramsDevices['cod_device'], $params['phys_address'][$index]);
                    }
                }

                $this->db->transaction();
                $dateTimeNow = date('Y-m-d H:i:s');
                foreach ($params['index'] as $index) {

                    $paramsBand = Array(
                        'cod_device' => $paramsDevices['cod_device'],
                        'num_in_octets' => $params['in_octets'][$index],
                        'num_out_octets' => $params['out_octets'][$index],
                        'dte_register' => $dateTimeNow,
                        'num_speed' => $params['speed'][$index],
                        'num_high_speed' => $params['high_speed'][$index],
                    );

                    if (isset($params['phys_address'][$index]) and ! empty($params['phys_address'][$index])) {
                        $bandwidth = $this->bandwidthCalculation($paramsBand, $paramsLastData[$paramsDevices['cod_device']][$params['phys_address'][$index]]);
                    } else {
                        $bandwidth = $this->bandwidthCalculation($paramsBand);
                    }

                    $this->db->sequence('gen_data_interface', 'nocomdata');
                    $id = $this->db->executeSequence();

                    $ips = json_decode($params['ip_address'][$index]);

                    $ip = null;
                    $proxy = null;

                    if (!empty($ips) and count($ips) > 0) {
                        if (count($ips) > 1) {
                            $ip = $ips[0];
                            $proxy = $ips[1];
                        } else {
                            $ip = $ips[0];
                        }
                    }

                    $paramsInsert = [
                        'index' => 'nocom',
                        'type' => 'tab_data_interface',
                        'id' => $id['0']['nextval'],
                        'body' => [
                            "cod_device" => $paramsDevices['cod_device'],
                            "nam_interface" => $params['name'][$index],
                            "des_type_interface" => $params['type_desc'][$index],
                            "des_ip_address" => $ip,
                            "des_ip_proxy" => $proxy,
                            "ind_oper_status" => (($params['oper_status'][$index]) ? "U" : "D"),
                            "ind_admin_status" => (($params['admin_status'][$index]) ? "U" : "D"),
                            "num_in_octets" => $params['in_octets'][$index],
                            "num_in_unicast_packets" => $params['in_unicast_packets'][$index],
                            "num_out_octets" => $params['out_octets'][$index],
                            "num_out_unicast_packets" => $params['out_unicast_packets'][$index],
                            "num_speed" => $params['speed'][$index],
                            "num_high_speed" => $params['high_speed'][$index],
                            "num_in_bit_rate" => $bandwidth['in_bit_rate'],
                            "num_out_bit_rate" => $bandwidth['out_bit_rate'],
                            "dte_register" => $dateTimeNow,
                        ],
                    ];

                    $ret = $this->es->index($paramsInsert);
                }
            }
        } catch (Exception $exc) {
            throw new Exception('Error While Insert Data Network Interface for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

    public function setDataNetworkDb(array $params, array $paramsDevices) {


        $this->getConnection();
        try {
            if (isset($params['index'])) {

                $paramsLastData = Array();

                foreach ($params['index'] as $index) {
                    if (isset($params['phys_address'][$index]) and ! empty($params['phys_address'][$index])) {
                        $paramsLastData[$paramsDevices['cod_device']][$params['phys_address'][$index]] = $this->getLastDataNetworkByDevice($paramsDevices['cod_device'], $params['phys_address'][$index]);
                    }
                }

                $this->db->transaction();
                $dateTimeNow = date('Y-m-d H:i:s');
                foreach ($params['index'] as $index) {

                    $paramsBand = Array(
                        'cod_device' => $paramsDevices['cod_device'],
                        'num_in_octets' => $params['in_octets'][$index],
                        'num_out_octets' => $params['out_octets'][$index],
                        'dte_register' => $dateTimeNow,
                        'num_speed' => $params['speed'][$index],
                        'num_high_speed' => $params['high_speed'][$index],
                    );

                    if (isset($params['phys_address'][$index]) and ! empty($params['phys_address'][$index])) {
                        $bandwidth = $this->bandwidthCalculation($paramsBand, $paramsLastData[$paramsDevices['cod_device']][$params['phys_address'][$index]]);
                    } else {
                        $bandwidth = $this->bandwidthCalculation($paramsBand);
                    }

                    $this->db->insert("cod_device", $paramsDevices['cod_device']);
                    $this->db->insert("nam_interface", $params['name'][$index]);
                    $this->db->insert("des_type_interface", $params['type_desc'][$index]);
                    $this->db->insert("des_phys_address", $params['phys_address'][$index]);
                    $this->db->insert("des_ip_address", $params['ip_address'][$index]);
                    $this->db->insert("ind_oper_status", (($params['oper_status'][$index]) ? "U" : "D"));
                    $this->db->insert("ind_admin_status", (($params['admin_status'][$index]) ? "U" : "D"));
                    $this->db->insert("num_in_octets", $params['in_octets'][$index]);
                    $this->db->insert("num_in_unicast_packets", $params['in_unicast_packets'][$index]);
                    $this->db->insert("num_out_octets", $params['out_octets'][$index]);
                    $this->db->insert("num_out_unicast_packets", $params['out_unicast_packets'][$index]);

                    $this->db->insert("num_speed", $params['speed'][$index]);
                    $this->db->insert("num_high_speed", $params['high_speed'][$index]);

                    $this->db->insert("num_in_bit_rate", $bandwidth['in_bit_rate']);
                    $this->db->insert("num_out_bit_rate", $bandwidth['out_bit_rate']);

                    $this->db->insert("dte_register", $dateTimeNow);
                    $this->db->from('tab_data_interface', null, 'nocomdata');
                    $this->db->setDebug(false);
                    $this->db->executeInsertQuery();
                }
                $this->db->commit();
            }
        } catch (Exception $exc) {
            $this->db->rollback();
            throw new Exception('Error While Insert Data Network Interface for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

    public function bandwidthCalculation(array $params, array $lastData = null) {

        if (!empty($lastData)) {

            $toTime = strtotime($params['dte_register']);
            $fromTime = strtotime($lastData['dte_register']);

            $seconds = abs($toTime - $fromTime);

            $totalInOct = abs($lastData['num_in_octets'] - $params['num_in_octets']);
            $totalOutOct = abs($lastData['num_out_octets'] - $params['num_out_octets']);

            $avgInBitRate = ($seconds > 0) ? (($totalInOct * 8) / $seconds) : 0;
            $avgOutBitRate = ($seconds > 0) ? (($totalOutOct * 8) / $seconds) : 0;

            $returnAvgBitRate = Array(
                'in_bit_rate' => $avgInBitRate,
                'out_bit_rate' => $avgOutBitRate,
                'overall_usage' => (($avgInBitRate + $avgOutBitRate) / 2)
            );
        } else {
            $returnAvgBitRate = Array(
                'in_bit_rate' => 0,
                'out_bit_rate' => 0,
                'overall_usage' => 0
            );
        }

        return $returnAvgBitRate;
    }

    public function getLastDataNetworkByDevice($idDevice, $macAddress = null) {

        if (!empty($idDevice) and ! empty($macAddress)) {
            $this->getConnection();
            $this->db->select("*");
            $this->db->from('tab_data_interface', null, 'nocomdata');
            $this->db->where("cod_device = '{$idDevice}'");
            $this->db->where("des_phys_address = '{$macAddress}'");
            $this->db->limit(1);
            $this->db->orderBy('seq_data_interface DESC');
            $rsLastDataNetworkByDevice = $this->db->executeSelectQuery();
            return (isset($rsLastDataNetworkByDevice[0])) ? $rsLastDataNetworkByDevice[0] : array();
        } else {
            return array();
        }
    }

    public function getDataNetworkCurrentHourDb(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MINUTE from tdi.dte_register) / 5)", 'slot', true);
        $this->db->select("avg(tdi.num_in_bit_rate)", 'num_in_bit_rate', true);
        $this->db->select("avg(tdi.num_out_bit_rate)", 'num_out_bit_rate', true);
        $this->db->from('tab_data_interface', 'tdi', 'nocomdata');
        $this->db->where("tdi.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdi.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdi.dte_register < '{$params['dte_finish']}'");
        $this->db->where("tdi.des_type_interface <> 'softwareLoopback'");
        $this->db->groupBy("1", true);
        $this->db->orderBy("1", true);
        $this->db->setDebug(false);
        $rsDataNetworkLastHour = $this->db->executeSelectQuery();

        $return = Array();

        foreach ($rsDataNetworkLastHour as $value) {
            $return[$value['slot']] = $value;
        }

        return $return;
    }

    public function getDataNetworkCurrentHour(array $params) {
        $this->getConnection();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_interface',
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
                            "num_out_bit_rate_avg" => [
                                "avg" => [
                                    "field" => "num_out_bit_rate"
                                ],
                            ],
                            "num_in_bit_rate_avg" => [
                                "avg" => [
                                    "field" => "num_in_bit_rate"
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
            $return[$key]['num_in_bit_rate'] = $value['num_in_bit_rate_avg']['value'];
            $return[$key]['num_out_bit_rate'] = $value['num_out_bit_rate_avg']['value'];
        }

        return $return;
    }

    public function getDataNetworkCurrentDayDb(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(HOUR from tdi.dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdi.num_in_bit_rate)", 'num_in_bit_rate', true);
        $this->db->select("avg(tdi.num_out_bit_rate)", 'num_out_bit_rate', true);
        $this->db->from('tab_data_interface', 'tdi', 'nocomdata');
        $this->db->where("tdi.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdi.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdi.dte_register < '{$params['dte_finish']}'");
        $this->db->where("tdi.des_type_interface <> 'softwareLoopback'");
        $this->db->groupBy("1", true);
        $this->db->orderBy("1", true);
        $this->db->setDebug(false);
        $rsDataNetworkLastDay = $this->db->executeSelectQuery();

        $return = Array();

        foreach ($rsDataNetworkLastDay as $value) {
            $return[$value['slot']] = $value;
        }

        return $return;
    }

    public function getDataNetworkCurrentDay(array $params) {
        $this->getConnection();
        $dateOperations = new \Cityware\Format\DateOperations();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_interface',
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
                            "num_out_bit_rate_avg" => [
                                "avg" => [
                                    "field" => "num_out_bit_rate"
                                ],
                            ],
                            "num_in_bit_rate_avg" => [
                                "avg" => [
                                    "field" => "num_in_bit_rate"
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
            $return[$keyDate]['num_in_bit_rate'] = $value['num_in_bit_rate_avg']['value'];
            $return[$keyDate]['num_out_bit_rate'] = $value['num_out_bit_rate_avg']['value'];
        }

        return $return;
    }

    public function getDataNetworkCurrentMonthDb(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(DAY from tdi.dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdi.num_in_bit_rate)", 'num_in_bit_rate', true);
        $this->db->select("avg(tdi.num_out_bit_rate)", 'num_out_bit_rate', true);
        $this->db->from('tab_data_interface', 'tdi', 'nocomdata');
        $this->db->where("tdi.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdi.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdi.dte_register < '{$params['dte_finish']}'");
        $this->db->where("tdi.des_type_interface <> 'softwareLoopback'");
        $this->db->groupBy("1", true);
        $this->db->orderBy("1", true);
        $this->db->setDebug(false);
        $rsDataNetworkLastMonth = $this->db->executeSelectQuery();

        $return = Array();

        foreach ($rsDataNetworkLastMonth as $value) {
            $return[$value['slot']] = $value;
        }

        return $return;
    }

    public function getDataNetworkCurrentMonth(array $params) {
        $this->getConnection();
        $dateOperations = new \Cityware\Format\DateOperations();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_interface',
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
                            "num_out_bit_rate_avg" => [
                                "avg" => [
                                    "field" => "num_out_bit_rate"
                                ],
                            ],
                            "num_in_bit_rate_avg" => [
                                "avg" => [
                                    "field" => "num_in_bit_rate"
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
            $return[$keyDate]['num_in_bit_rate'] = $value['num_in_bit_rate_avg']['value'];
            $return[$keyDate]['num_out_bit_rate'] = $value['num_out_bit_rate_avg']['value'];
        }

        return $return;
    }

    public function getDataNetworkCurrentYearDb(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MONTH from tdi.dte_register) / 1)", 'slot', true);
        $this->db->select("avg(tdi.num_in_bit_rate)", 'num_in_bit_rate', true);
        $this->db->select("avg(tdi.num_out_bit_rate)", 'num_out_bit_rate', true);
        $this->db->from('tab_data_interface', 'tdi', 'nocomdata');
        $this->db->where("tdi.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdi.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdi.dte_register < '{$params['dte_finish']}'");
        $this->db->where("tdi.des_type_interface <> 'softwareLoopback'");
        $this->db->groupBy("1", true);
        $this->db->orderBy("1", true);
        $this->db->setDebug(false);
        $rsDataNetworkLastYear = $this->db->executeSelectQuery();

        $return = Array();

        foreach ($rsDataNetworkLastYear as $value) {
            $return[$value['slot']] = $value;
        }

        return $return;
    }

    public function getDataNetworkCurrentYear(array $params) {
        $this->getConnection();
        $dateOperations = new \Cityware\Format\DateOperations();

        $paramsEs = [
            'index' => 'nocom',
            'type' => 'tab_data_interface',
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
                            "num_out_bit_rate_avg" => [
                                "avg" => [
                                    "field" => "num_out_bit_rate"
                                ],
                            ],
                            "num_in_bit_rate_avg" => [
                                "avg" => [
                                    "field" => "num_in_bit_rate"
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
            $return[$keyDate]['num_in_bit_rate'] = $value['num_in_bit_rate_avg']['value'];
            $return[$keyDate]['num_out_bit_rate'] = $value['num_out_bit_rate_avg']['value'];
        }

        return $return;
    }

}
