<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs\Services\Database;

/**
 * Description of Cpu
 *
 * @author fsvxavier
 */
class PostgreSql {

    /**
     * Return Nginx service Data
     * @param array $params
     * @return array
     */
    public function getServiceDataInstance(array $params) {

        $dataPostgresql = new \Cityware\Monitoring\Models\Services\DataPostgreSql($params);

        $locksInstance = $dataPostgresql->getDataPgSqlInstanceLocks();
        $serverVersionInstance = $dataPostgresql->getDataPgSqlInstanceVersion();
        $checkPointsInstance = $dataPostgresql->getDataPgSqlInstanceCheckpoint();
        $connectionsInstance = $dataPostgresql->getDataPgSqlInstanceConnections();

        $return = Array();

        $return['des_server_version'] = $serverVersionInstance['server_version'];

        $return['num_total_checkpoints'] = (float) $checkPointsInstance['total_checkpoints'];

        $return['num_total_checkpoints_req'] = (float) $checkPointsInstance['checkpoints_req'];
        $return['num_total_checkpoints_timed'] = (float) $checkPointsInstance['checkpoints_timed'];

        $return['num_sec_between_checkpoints'] = ($return['num_total_checkpoints'] > 0) ? ((float) $checkPointsInstance['seconds_since_start'] / $return['num_total_checkpoints']) : 0;
        $return['num_checkpoint_req_timed_ratio'] = ($return['num_total_checkpoints_timed'] > 0) ? (($return['num_total_checkpoints_req'] / $return['num_total_checkpoints_timed']) * 100) : 0;

        $return['num_total_connections'] = (float) $connectionsInstance['total_connections'];
        $return['num_max_connections'] = (float) $connectionsInstance['max_connections'];
        $return['num_connection_ratio'] = (float) $connectionsInstance['connection_ratio'];

        $return['num_max_locks_per_transaction'] = (float) $locksInstance['max_total_locks'];
        $return['num_access_exclusive_lock'] = $locksInstance['AccessExclusiveLock'];
        $return['num_access_share_lock'] = $locksInstance['AccessShareLock'];
        $return['num_exclusive_lock'] = $locksInstance['ExclusiveLock'];
        $return['num_row_exclusive_lock'] = $locksInstance['RowExclusiveLock'];
        $return['num_row_share_lock'] = $locksInstance['RowShareLock'];
        $return['num_share_lock'] = $locksInstance['ShareLock'];
        $return['num_share_row_exclusive_lock'] = $locksInstance['ShareRowExclusiveLock'];
        $return['num_share_update_exclusive_lock'] = $locksInstance['ShareUpdateExclusiveLock'];


        return $return;
    }

    /**
     * Return Nginx service Data
     * @param array $params
     * @return array
     */
    public function getServiceDataDatabaseConnections(array $params) {

        $dataPostgresql = new \Cityware\Monitoring\Models\Services\DataPostgreSql($params);

        $connectionsDatabases = $dataPostgresql->getDataPgSqlDatabasesConnections();

        $return = Array();

        $geoipV1 = new \Cityware\Utility\GeoIpV1();
        $geoipV1->geoip_open(DATA_PATH . 'GeoIp/GeoIPASNum.dat', GEOIP_STANDARD);

        foreach ($connectionsDatabases as $key => $value) {

            $desAsnIsp = (!empty($value['client_addr'])) ? \Cityware\Format\Text::convertString($geoipV1->geoip_name_by_addr($value['client_addr'])) : '';

            $return[$key]['des_ip'] = $value['client_addr'];
            $return[$key]['des_asn_isp'] = strtoupper(\Cityware\Format\Text::removeAccents($desAsnIsp));
            $return[$key]['des_hash'] = hash('crc32b', $value['datname']);
            $return[$key]['nam_database'] = $value['datname'];
            $return[$key]['num_total_connections'] = $value['total_connections'];
        }

        return $return;
    }

    /**
     * Return Nginx service Data
     * @param array $params
     * @return array
     */
    public function getServiceDataDatabase(array $params) {

        $dataPostgresql = new \Cityware\Monitoring\Models\Services\DataPostgreSql($params);

        $databases = $dataPostgresql->getDataPgSqlDatabaseStatus();

        $return = Array();

        foreach ($databases as $key => $value) {

            $hitRatio = (($value['blks_hit'] + $value['blks_read']) > 0) ? (($value['blks_hit'] * 100) / ($value['blks_hit'] + $value['blks_read'])) : 0;


            $return[$key]['nam_database'] = $value['datname'];
            $return[$key]['des_hash'] = hash('crc32b', $value['datname']);
            $return[$key]['num_datid'] = $value['datid'];
            $return[$key]['num_commit'] = $value['xact_commit'];
            $return[$key]['num_rollback'] = $value['xact_rollback'];
            $return[$key]['num_blks_read'] = $value['blks_read'];
            $return[$key]['num_blks_hit'] = $value['blks_hit'];
            $return[$key]['num_database_size'] = $value['db_size'];
            $return[$key]['num_database_age'] = $value['db_age'];
            $return[$key]['num_connection_ratio'] = $value['connection_ratio'];
            $return[$key]['num_transactions_per_second'] = $value['transactions_per_second'];
            $return[$key]['num_hit_ratio'] = $hitRatio;
            $return[$key]['num_tup_returned'] = $value['tup_returned'];
            $return[$key]['num_tup_fetched'] = $value['tup_fetched'];
            $return[$key]['num_tup_inserted'] = $value['tup_inserted'];
            $return[$key]['num_tup_updated'] = $value['tup_updated'];
            $return[$key]['num_tup_deleted'] = $value['tup_deleted'];
            $return[$key]['num_backends'] = $value['numbackends'];
            $return[$key]['num_total_connections'] = $value['total_connections'];
            $return[$key]['num_total_seq_scan'] = $value['seq_scan'];
            $return[$key]['num_total_idx_scan'] = $value['idx_scan'];
            $return[$key]['num_total_autovacuum'] = $value['autovacuum_count'];
            $return[$key]['num_total_autoanalyze'] = $value['autoanalyze_count'];
        }

        return $return;
    }

}
