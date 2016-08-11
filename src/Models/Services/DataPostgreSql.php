<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * 
 * 
 */

namespace Cityware\Monitoring\Models\Services;

use Cityware\Monitoring\Models\AbstractModels;
use Zend\Db\Adapter\Adapter AS ZendDbAdapter;
use Zend\Db\ResultSet\ResultSet;

/**
 * Description of DataPostgreSql
 *
 * @author fsvxavier
 */
class DataPostgreSql extends AbstractModels {

    private $dbConnectionDriver = 'Pdo_Pgsql';
    private $dbConnectionHost = 'localhost';
    private $dbConnectionPort = '5432';
    private $dbConnectionUser = 'postgres';
    private $dbConnectionPass = '';
    private $dbConnectionBase = 'postgres';
    private $dbConnectionNewAdapter, $dbResultSet = null;

    public function setDbConnectionHost($dbConnectionHost) {
        $this->dbConnectionHost = $dbConnectionHost;
    }

    public function setDbConnectionPort($dbConnectionPort) {
        $this->dbConnectionPort = $dbConnectionPort;
    }

    public function setDbConnectionUser($dbConnectionUser) {
        $this->dbConnectionUser = $dbConnectionUser;
    }

    public function setDbConnectionPass($dbConnectionPass) {
        $this->dbConnectionPass = $dbConnectionPass;
    }

    function setDbConnectionBase($dbConnectionBase) {
        $this->dbConnectionBase = $dbConnectionBase;
    }

    public function __construct(array $params = null) {
        $this->dbResultSet = new ResultSet();

        if (!empty($params)) {
            if (isset($params['num_ip']) and ! empty($params['num_ip'])) {
                $this->setDbConnectionHost($params['num_ip']);
            }
            if (isset($params['des_user']) and ! empty($params['des_user'])) {
                $this->setDbConnectionUser($params['des_user']);
            }
            if (isset($params['des_password']) and ! empty($params['des_password'])) {
                $this->setDbConnectionPass($params['des_password']);
            }
            if (isset($params['des_port']) and ! empty($params['des_port'])) {
                $this->setDbConnectionPort($params['des_port']);
            }

            if (isset($params['nam_database']) and ! empty($params['nam_database'])) {
                $this->setDbConnectionBase($params['nam_database']);
            }
        }
    }

    public function setDataPostgreSql(array $params, array $paramsDevices) {
        $this->getConnection();
        try {
            $this->db->transaction();

            foreach ($params as $key => $value) {
                $this->db->insert($key, $value);
            }
            $this->db->insert("cod_device", $paramsDevices['cod_device']);
            $this->db->insert("dte_register", date('Y-m-d H:i:s'));
            $this->db->from('tab_data_serv_pgsql', null, 'nocomdata');
            $this->db->setdebug(false);
            $this->db->setReturnInsertId(true);

            $return = $this->db->executeInsertQuery();
            $this->db->commit();

            return $return;
        } catch (\Exception $exc) {
            $this->db->rollback();
            throw new \Exception('Error While Insert Data Service PostgreSQL for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

    public function setDataPostgreSqlDatabase(array $params, array $paramsDevices) {
        $this->getConnection();
        try {
            $this->db->transaction();
            foreach ($params as $valueDatabase) {
                foreach ($valueDatabase as $key => $value) {
                    $this->db->insert($key, $value);
                }

                $this->db->insert("cod_device", $paramsDevices['cod_device']);
                $this->db->insert("seq_data_serv_pgsql", $paramsDevices['seq_data_serv_pgsql']);
                $this->db->insert("dte_register", date('Y-m-d H:i:s'));
                $this->db->from('tab_data_serv_pgsql_database', null, 'nocomdata');
                $this->db->setdebug(false);
                $this->db->executeInsertQuery();
            }

            $this->db->commit();
        } catch (\Exception $exc) {
            $this->db->rollback();
            throw new \Exception('Error While Insert Data Service PostgreSQL - Databases for JOB PARALLEL - ' . $exc->getMessage());
        }
    }
    
    public function setDataPostgreSqlDatabaseConnections(array $params, array $paramsDevices) {
        $this->getConnection();
        try {
            $this->db->transaction();
            foreach ($params as $valueDatabase) {
                foreach ($valueDatabase as $key => $value) {
                    $this->db->insert($key, $value);
                }

                $this->db->insert("cod_device", $paramsDevices['cod_device']);
                $this->db->insert("seq_data_serv_pgsql", $paramsDevices['seq_data_serv_pgsql']);
                $this->db->insert("dte_register", date('Y-m-d H:i:s'));
                $this->db->from('tab_data_serv_pgsql_db_ip', null, 'nocomdata');
                $this->db->setdebug(false);
                $this->db->executeInsertQuery();
            }

            $this->db->commit();
        } catch (\Exception $exc) {
            $this->db->rollback();
            throw new \Exception('Error While Insert Data Service PostgreSQL - Databases for JOB PARALLEL - ' . $exc->getMessage());
        }
    }

    private function monitoringAdapter() {
        $this->dbConnectionNewAdapter = new ZendDbAdapter(Array(
            'driver' => $this->dbConnectionDriver,
            'database' => $this->dbConnectionBase,
            'host' => $this->dbConnectionHost,
            'port' => $this->dbConnectionPort,
            'username' => $this->dbConnectionUser,
            'password' => $this->dbConnectionPass,
        ));
        return $this->dbConnectionNewAdapter;
    }

    private function closeConnection($adapter) {
        $adapter->getDriver()->getConnection()->disconnect();
    }

    public function getDataPgSqlInstanceVersion() {

        $queryVersion = "SELECT current_setting('server_version') as server_version";

        $adapter = $this->monitoringAdapter();
        $results = $adapter->query($queryVersion);

        $rsDataPgSqlInstanceVersion = $this->dbResultSet->initialize($results->execute())->toArray();
        $this->closeConnection($adapter);

        return $rsDataPgSqlInstanceVersion[0];
    }

    public function getDataPgSqlInstanceConnections() {

        $queryConnections = "
        SELECT count(*) as total_connections,
            (SELECT setting FROM pg_settings WHERE name='max_connections') as max_connections, 
            (count(*)*100/(SELECT current_setting('max_connections')::int)) as connection_ratio 
        FROM pg_stat_activity 
        WHERE datname !~ 'postgres|template'
        ";

        $adapter = $this->monitoringAdapter();
        $results = $adapter->query($queryConnections);

        $rsDataPgSqlInstanceConnections = $this->dbResultSet->initialize($results->execute())->toArray();
        $this->closeConnection($adapter);

        return $rsDataPgSqlInstanceConnections[0];
    }

    public function getDataPgSqlInstanceCheckpoint() {

        $queryCheckPoints = "
        SELECT
            total_checkpoints,
            checkpoints_timed,
            checkpoints_req,
            seconds_since_start
        FROM
	(SELECT EXTRACT (EPOCH FROM (now() - pg_postmaster_start_time ())) AS seconds_since_start,
            (checkpoints_timed + checkpoints_req) AS total_checkpoints,
            checkpoints_timed,
            checkpoints_req
        FROM pg_stat_bgwriter) AS sub
        ";

        $adapter = $this->monitoringAdapter();
        $results = $adapter->query($queryCheckPoints);
        

        $rsDataPgSqlInstanceCheckpoint = $this->dbResultSet->initialize($results->execute())->toArray();
        $this->closeConnection($adapter);

        return $rsDataPgSqlInstanceCheckpoint[0];
    }

    public function getDataPgSqlInstanceLocks() {

        $queryLocks = "
        SELECT count(pl.*) AS num_locks,
            ref.mode AS lock_type,
            (current_setting('max_locks_per_transaction')::integer * current_setting('max_connections')::integer) AS max_total_locks,
            ref.granted
        FROM (
            SELECT 'AccessShareLock',                't'::boolean
            UNION SELECT 'RowShareLock',             't'::boolean
            UNION SELECT 'RowExclusiveLock',         't'::boolean
            UNION SELECT 'ShareUpdateExclusiveLock', 't'::boolean
            UNION SELECT 'ShareLock',                't'::boolean
            UNION SELECT 'ShareRowExclusiveLock',    't'::boolean
            UNION SELECT 'ExclusiveLock',            't'::boolean
            UNION SELECT 'AccessExclusiveLock',      't'::boolean
        ) ref (mode, granted) 
        LEFT JOIN pg_locks AS pl ON (ref.mode, ref.granted) = (pl.mode, pl.granted)
        GROUP BY 2,3,4
        ORDER BY ref.granted, ref.mode
        ";

        $adapter = $this->monitoringAdapter();
        $results = $adapter->query($queryLocks);

        $rsDataPgSqlInstanceLocks = $this->dbResultSet->initialize($results->execute())->toArray();

        $return = Array();
        foreach ($rsDataPgSqlInstanceLocks as $valueLocks) {
            $return[$valueLocks['lock_type']] = $valueLocks['num_locks'];
            $return['max_total_locks'] = $valueLocks['max_total_locks'];
        }

        $this->closeConnection($adapter);

        return $return;
    }

    public function getDataPgSqlDatabaseStatus() {

        $dbVersion = $this->getDataPgSqlInstanceVersion();

        if ($dbVersion['server_version'] > "9.1") {
            $extraQuerySelect = ", COALESCE((SELECT SUM(autovacuum_count) FROM pg_stat_user_tables WHERE schemaname IN (SELECT SCHEMA_NAME FROM information_schema.schemata WHERE CATALOG_NAME = s.datname )), 0) AS autovacuum_count,
                                 COALESCE((SELECT SUM(autoanalyze_count) FROM pg_stat_user_tables WHERE schemaname IN (SELECT SCHEMA_NAME FROM information_schema.schemata WHERE CATALOG_NAME = s.datname )), 0) AS autoanalyze_count";
        } else {
            $extraQuerySelect = ", (0) AS autovacuum_count, (0) AS autoanalyze_count";
        }

        $queryDatabaseStatus = "
        SELECT  s.datid,
            s.datname,
            s.xact_commit,
            s.xact_rollback,
            s.blks_read,
            s.blks_hit,
            s.tup_returned,
            s.tup_fetched,
            s.tup_inserted,
            s.tup_updated,
            s.tup_deleted,
            s.numbackends,
            age(d.datfrozenxid) as db_age,
            pg_database_size(s.datname) as db_size,
            (xact_commit + xact_rollback) AS transactions_per_second,
            (select count(*)*100/(select current_setting('max_connections')::int) from pg_stat_activity as psa WHERE psa.datname = s.datname) as connection_ratio,
            (select count(*) from pg_stat_activity as psa WHERE psa.datname = s.datname) as total_connections,
            COALESCE((SELECT SUM(seq_scan) FROM pg_stat_user_tables WHERE schemaname IN (SELECT SCHEMA_NAME FROM information_schema.schemata WHERE CATALOG_NAME = s.datname )), 0) AS seq_scan,
            COALESCE((SELECT SUM(idx_scan) FROM pg_stat_user_tables WHERE schemaname IN (SELECT SCHEMA_NAME FROM information_schema.schemata WHERE CATALOG_NAME = s.datname )), 0) AS idx_scan
            {$extraQuerySelect}
        FROM pg_stat_database as s
        JOIN pg_database as d ON s.datid = d.oid
        WHERE d.datallowconn AND s.datname !~ 'postgres|template'
        ";

        $adapter = $this->monitoringAdapter();
        $results = $adapter->query($queryDatabaseStatus);

        $rsDataPgSqlDatabaseStatus = $this->dbResultSet->initialize($results->execute())->toArray();

        $this->closeConnection($adapter);

        return $rsDataPgSqlDatabaseStatus;
    }
    
    public function getDataPgSqlDatabasesConnections() {
        
        $queryCheckPoints = "
        SELECT count(*) AS total_connections, 
            client_addr,
            datname
        FROM get_pg_stats()
        WHERE datname !~ 'postgres|template' AND client_addr NOT IN('::1','127.0.0.1')
        GROUP BY 2, 3
        ";

        $adapter = $this->monitoringAdapter();
        $results = $adapter->query($queryCheckPoints);
        

        $rsDataPgSqlDatabasesConnections = $this->dbResultSet->initialize($results->execute())->toArray();
        
        $this->closeConnection($adapter);

        return $rsDataPgSqlDatabasesConnections;
    }
    
    public function getDataPostgreSqlDatabases($id) {
        $this->getConnection();

        $this->db->select("nam_database");
        $this->db->select("des_hash");
        $this->db->from('tab_data_serv_pgsql_database', null, 'nocomdata');
        $this->db->where("cod_device = '{$id}'");
        $this->db->where("des_hash IS NOT NULL");
        $this->db->groupBy("1", true);
        $this->db->groupBy("2", true);
        $this->db->setDebug(false);
        $rsDataPostgreSqlById = $this->db->executeSelectQuery();

        return $rsDataPostgreSqlById;
    }

    public function getDataPostgreSqlByDeviceId($id) {
        $this->getConnection();

        $this->db->select("*");
        $this->db->from('tab_data_serv_pgsql', null, 'nocomdata');
        $this->db->where("cod_device = '{$id}'");
        $this->db->orderBy("seq_data_serv_pgsql DESC");
        $this->db->setDebug(false);
        $rsDataPostgreSqlById = $this->db->executeSelectQuery();

        return $rsDataPostgreSqlById;
    }

    public function getDataPostgreSqlTopTenDatabaseConnections(array $params) {
        $this->getConnection();
        $this->db->select("sum(tdspd.num_total_connections)", 'sum_total_connections', true);
        $this->db->select("tdspd.nam_database");
        $this->db->from('tab_data_serv_pgsql_database', 'tdspd', 'nocomdata');
        $this->db->join('tab_data_serv_pgsql', 'tdsp', 'tdspd.seq_data_serv_pgsql = tdsp.seq_data_serv_pgsql AND tdspd.cod_device = tdsp.cod_device', 'INNERJOIN', 'nocomdata');
        $this->db->where("tdspd.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdspd.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdspd.dte_register < '{$params['dte_finish']}'");
        $this->db->where("tdspd.num_total_connections > '0'");
        $this->db->groupBy("2", true);
        $this->db->orderBy("1 DESC", true);
        $this->db->limit(10);
        $this->db->setDebug(false);
        $rsDataPostgreSqlTopTenDatabaseSize = $this->db->executeSelectQuery();

        return $rsDataPostgreSqlTopTenDatabaseSize;
    }

    public function getDataPostgreSqlTopTenDatabaseSize(array $params) {
        $this->getConnection();
        $this->db->select("max(tdspd.num_database_size)", 'max_database_size', true);
        $this->db->select("tdspd.nam_database");
        $this->db->from('tab_data_serv_pgsql_database', 'tdspd', 'nocomdata');
        $this->db->join('tab_data_serv_pgsql', 'tdsp', 'tdspd.seq_data_serv_pgsql = tdsp.seq_data_serv_pgsql AND tdspd.cod_device = tdsp.cod_device', 'INNERJOIN', 'nocomdata');
        $this->db->where("tdspd.cod_device = '{$params['cod_device']}'");
        $this->db->where("tdspd.dte_register >= '{$params['dte_start']}'");
        $this->db->where("tdspd.dte_register < '{$params['dte_finish']}'");
        //$this->db->where("tdsp.seq_data_serv_pgsql = '{$params['seq_data_serv_pgsql']}'");
        $this->db->groupBy("2", true);
        $this->db->orderBy("1 DESC", true);
        $this->db->limit(10);
        $this->db->setDebug(false);
        $rsDataPostgreSqlTopTenDatabaseSize = $this->db->executeSelectQuery();

        return $rsDataPostgreSqlTopTenDatabaseSize;
    }
    
    public function getDataPostgreSqlCommitRollbackInstance(array $params) {
        $this->getConnection();
        $this->db->select("tdspd.dte_register");
        $this->db->select("sum(tdspd.num_commit)", 'sum_commit', true);
        $this->db->select("sum(tdspd.num_rollback)", 'sum_rollback', true);
        $this->db->from('tab_data_serv_pgsql_database', 'tdspd', 'nocomdata');
        $this->db->join('tab_data_serv_pgsql', 'tdsp', 'tdspd.seq_data_serv_pgsql = tdsp.seq_data_serv_pgsql AND tdspd.cod_device = tdsp.cod_device', 'INNERJOIN', 'nocomdata');
        $this->db->where("tdspd.cod_device = '{$params['cod_device']}'");
        $this->db->groupBy("1", true);
        $this->db->orderBy("1 DESC", true);
        $this->db->limit(60);
        $this->db->setDebug(false);
        $rsDataPostgreSqlCommitRollbackInstance = $this->db->executeSelectQuery();

        return $rsDataPostgreSqlCommitRollbackInstance;
    }

    public function getDataPostgreSqlCheckPointsLastHour(array $params) {
        $this->getConnection();
        $queryCheckPoints = "
            SELECT tdsp.cod_device,
                tdsp.seq_data_serv_pgsql,
                tdsp.num_total_checkpoints_req,
                tdsp.num_total_checkpoints_timed,
                tdsp.num_total_checkpoints,
                tdsp.dte_register, 
                COALESCE(lag(tdsp.num_total_checkpoints_req) over client_window, 0) as pre_rate_req,
                COALESCE(lag(tdsp.num_total_checkpoints_timed) over client_window, 0) as pre_rate_timed,
                COALESCE(lag(tdsp.num_total_checkpoints) over client_window, 0) as pre_rate,
                COALESCE((lag(tdsp.num_total_checkpoints_req) over client_window - tdsp.num_total_checkpoints_req), 0) AS  diference_req,
                COALESCE((lag(tdsp.num_total_checkpoints_timed) over client_window) - tdsp.num_total_checkpoints_timed, 0) AS  diference_timed,
                COALESCE((lag(tdsp.num_total_checkpoints) over client_window - tdsp.num_total_checkpoints), 0) AS  diference_total
            FROM nocomdata.tab_data_serv_pgsql AS tdsp
            WHERE tdsp.cod_device = '{$params['cod_device']}'
            WINDOW client_window AS (PARTITION BY tdsp.cod_device ORDER BY tdsp.dte_register DESC)
            ORDER BY tdsp.dte_register DESC
            LIMIT 60";

        
        $this->db->setDebug(false);
        $rsDataPostgreSqlCheckPointsLastHour = $this->db->executeSqlQuery($queryCheckPoints);

        return $rsDataPostgreSqlCheckPointsLastHour;
    }

    public function getDataPostgreSqlLastHour(array $params) {
        $this->getConnection();

        $this->db->select("tdsp.num_total_checkpoints");
        $this->db->select("tdsp.num_total_checkpoints_req");
        $this->db->select("tdsp.num_total_checkpoints_timed");
        $this->db->select("tdsp.num_sec_between_checkpoints");
        $this->db->select("tdsp.num_checkpoint_req_timed_ratio");
        $this->db->select("tdsp.num_total_connections");
        $this->db->select("tdsp.num_max_connections");
        $this->db->select("tdsp.num_connection_ratio");
        $this->db->select("tdsp.num_max_locks_per_transaction");
        $this->db->select("tdsp.num_access_share_lock");
        $this->db->select("tdsp.num_row_share_lock");
        $this->db->select("tdsp.num_row_exclusive_lock");
        $this->db->select("tdsp.num_share_update_exclusive_lock");
        $this->db->select("tdsp.num_share_lock");
        $this->db->select("tdsp.num_share_row_exclusive_lock");
        $this->db->select("tdsp.num_exclusive_lock");
        $this->db->select("tdsp.num_access_exclusive_lock");
        $this->db->select("tdsp.dte_register");
        $this->db->from('tab_data_serv_pgsql', 'tdsp', 'nocomdata');
        $this->db->where("tdsp.cod_device = '{$params['cod_device']}'");
        $this->db->orderBy("tdsp.seq_data_serv_pgsql DESC");
        $this->db->limit(60);
        $this->db->setDebug(false);
        $rsDataPostgreSqlLastHour = $this->db->executeSelectQuery();

        return $rsDataPostgreSqlLastHour;
    }

    public function getDataPostgreSqlCurrentHour(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MINUTE from dte_register) / 5)", 'slot', true);
        $this->db->select("sum(tdsp.num_total_checkpoints)", 'num_total_checkpoints', true);
        
        $this->db->select("sum(tdsp.num_total_checkpoints_req)", 'num_total_checkpoints_req', true);
        $this->db->select("sum(tdsp.num_total_checkpoints_timed)", 'num_total_checkpoints_timed', true);
        
        $this->db->select("avg(tdsp.num_sec_between_checkpoints)", 'num_sec_between_checkpoints', true);
        $this->db->select("avg(tdsp.num_checkpoint_req_timed_ratio)", 'num_checkpoint_req_timed_ratio', true);
        $this->db->select("sum(tdsp.num_total_connections)", 'num_total_connections', true);
        $this->db->select("sum(tdsp.num_max_connections)", 'num_max_connections', true);
        $this->db->select("avg(tdsp.num_connection_ratio)", 'num_connection_ratio', true);

        $this->db->select("avg(tdsp.num_max_locks_per_transaction)", 'num_max_locks_per_transaction', true);

        $this->db->select("sum(tdsp.num_access_share_lock)", 'num_access_share_lock', true);
        $this->db->select("sum(tdsp.num_row_share_lock)", 'num_row_share_lock', true);
        $this->db->select("sum(tdsp.num_row_exclusive_lock)", 'num_row_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_share_update_exclusive_lock)", 'num_share_update_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_share_lock)", 'num_share_lock', true);
        $this->db->select("sum(tdsp.num_share_row_exclusive_lock)", 'num_share_row_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_exclusive_lock)", 'num_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_access_exclusive_lock)", 'num_access_exclusive_lock', true);

        $this->db->from('tab_data_serv_pgsql', 'tdsp', 'nocomdata');
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

    public function getDataPostgreSqlCurrentDay(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(HOUR from dte_register) / 1)", 'slot', true);
        $this->db->select("sum(tdsp.num_total_checkpoints)", 'num_total_checkpoints', true);
        
        $this->db->select("sum(tdsp.num_total_checkpoints_req)", 'num_total_checkpoints_req', true);
        $this->db->select("sum(tdsp.num_total_checkpoints_timed)", 'num_total_checkpoints_timed', true);
        
        $this->db->select("avg(tdsp.num_sec_between_checkpoints)", 'num_sec_between_checkpoints', true);
        $this->db->select("avg(tdsp.num_checkpoint_req_timed_ratio)", 'num_checkpoint_req_timed_ratio', true);
        $this->db->select("sum(tdsp.num_total_connections)", 'num_total_connections', true);
        $this->db->select("sum(tdsp.num_max_connections)", 'num_max_connections', true);
        $this->db->select("avg(tdsp.num_connection_ratio)", 'num_connection_ratio', true);

        $this->db->select("avg(tdsp.num_max_locks_per_transaction)", 'num_max_locks_per_transaction', true);

        $this->db->select("sum(tdsp.num_access_share_lock)", 'num_access_share_lock', true);
        $this->db->select("sum(tdsp.num_row_share_lock)", 'num_row_share_lock', true);
        $this->db->select("sum(tdsp.num_row_exclusive_lock)", 'num_row_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_share_update_exclusive_lock)", 'num_share_update_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_share_lock)", 'num_share_lock', true);
        $this->db->select("sum(tdsp.num_share_row_exclusive_lock)", 'num_share_row_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_exclusive_lock)", 'num_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_access_exclusive_lock)", 'num_access_exclusive_lock', true);

        $this->db->from('tab_data_serv_pgsql', 'tdsp', 'nocomdata');
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

    public function getDataPostgreSqlCurrentMonth(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(DAY from dte_register) / 1)", 'slot', true);
        $this->db->select("sum(tdsp.num_total_checkpoints)", 'num_total_checkpoints', true);
        
        $this->db->select("sum(tdsp.num_total_checkpoints_req)", 'num_total_checkpoints_req', true);
        $this->db->select("sum(tdsp.num_total_checkpoints_timed)", 'num_total_checkpoints_timed', true);
        
        $this->db->select("avg(tdsp.num_sec_between_checkpoints)", 'num_sec_between_checkpoints', true);
        $this->db->select("avg(tdsp.num_checkpoint_req_timed_ratio)", 'num_checkpoint_req_timed_ratio', true);
        $this->db->select("sum(tdsp.num_total_connections)", 'num_total_connections', true);
        $this->db->select("sum(tdsp.num_max_connections)", 'num_max_connections', true);
        $this->db->select("avg(tdsp.num_connection_ratio)", 'num_connection_ratio', true);

        $this->db->select("avg(tdsp.num_max_locks_per_transaction)", 'num_max_locks_per_transaction', true);

        $this->db->select("sum(tdsp.num_access_share_lock)", 'num_access_share_lock', true);
        $this->db->select("sum(tdsp.num_row_share_lock)", 'num_row_share_lock', true);
        $this->db->select("sum(tdsp.num_row_exclusive_lock)", 'num_row_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_share_update_exclusive_lock)", 'num_share_update_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_share_lock)", 'num_share_lock', true);
        $this->db->select("sum(tdsp.num_share_row_exclusive_lock)", 'num_share_row_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_exclusive_lock)", 'num_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_access_exclusive_lock)", 'num_access_exclusive_lock', true);

        $this->db->from('tab_data_serv_pgsql', 'tdsp', 'nocomdata');
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

    public function getDataPostgreSqlCurrentYear(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MONTH from dte_register) / 1)", 'slot', true);
        $this->db->select("sum(tdsp.num_total_checkpoints)", 'num_total_checkpoints', true);
        
        $this->db->select("sum(tdsp.num_total_checkpoints_req)", 'num_total_checkpoints_req', true);
        $this->db->select("sum(tdsp.num_total_checkpoints_timed)", 'num_total_checkpoints_timed', true);
        
        $this->db->select("avg(tdsp.num_sec_between_checkpoints)", 'num_sec_between_checkpoints', true);
        $this->db->select("avg(tdsp.num_checkpoint_req_timed_ratio)", 'num_checkpoint_req_timed_ratio', true);
        $this->db->select("sum(tdsp.num_total_connections)", 'num_total_connections', true);
        $this->db->select("sum(tdsp.num_max_connections)", 'num_max_connections', true);
        $this->db->select("avg(tdsp.num_connection_ratio)", 'num_connection_ratio', true);

        $this->db->select("avg(tdsp.num_max_locks_per_transaction)", 'num_max_locks_per_transaction', true);

        $this->db->select("sum(tdsp.num_access_share_lock)", 'num_access_share_lock', true);
        $this->db->select("sum(tdsp.num_row_share_lock)", 'num_row_share_lock', true);
        $this->db->select("sum(tdsp.num_row_exclusive_lock)", 'num_row_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_share_update_exclusive_lock)", 'num_share_update_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_share_lock)", 'num_share_lock', true);
        $this->db->select("sum(tdsp.num_share_row_exclusive_lock)", 'num_share_row_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_exclusive_lock)", 'num_exclusive_lock', true);
        $this->db->select("sum(tdsp.num_access_exclusive_lock)", 'num_access_exclusive_lock', true);

        $this->db->from('tab_data_serv_pgsql', 'tdsp', 'nocomdata');
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
