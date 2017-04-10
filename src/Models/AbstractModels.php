<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models;

use Elasticsearch\ClientBuilder;

/**
 * Description of AbstractModels
 *
 * @author fsvxavier
 */
class AbstractModels {

    public $db;
    public $es;

    public function getConnection() {
        $this->db = \Cityware\Db\Factory::factory();
        $this->getConnectionEs();
        return $this;
    }

    private function getConnectionEs() {
        $config = \Zend\Config\Factory::fromFile(GLOBAL_CONFIG_PATH . 'global.php');

        $hosts = [];

        if (isset($config['elastic']['servers']) and ! empty($config['elastic']['servers'])) {
            foreach ($config['elastic']['servers'] as $valueServers) {
                $hosts[] = $valueServers['host'] . ':' . $valueServers['port'];
            }
        } else {
            throw new Exception('Não foi encontrada as configurações do ElasticSearch!');
        }

        $clientBuilder = ClientBuilder::create();   // Instantiate a new ClientBuilder
        $clientBuilder->setHosts($hosts);           // Set the hosts
        $this->es = $clientBuilder->build();          // Build the client object
    }

}
