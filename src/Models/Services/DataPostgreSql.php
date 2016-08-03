<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
            (seconds_since_start::float / total_checkpoints::float) AS seconds_between_checkpoints,
            ((checkpoints_req::float / checkpoints_timed::float) * 100) AS checkpoint_req_timed_ratio
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
            (select count(*)*100/(select current_setting('max_connections')::int) from pg_stat_activity as psa WHERE psa.datname = s.datname) as connection_ratio
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

    public function getDataPostgreSqlCurrentHour(array $params) {
        $this->getConnection();

        $this->db->select("trunc(EXTRACT(MINUTE from dte_register) / 5)", 'slot', true);
        $this->db->select("sum(tdsp.num_total_checkpoints)", 'num_total_checkpoints', true);
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
