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
        $hosts = [
            '172.16.20.10:9200',
            '172.16.20.11:9200',
        ];

        $clientBuilder = ClientBuilder::create();   // Instantiate a new ClientBuilder
        $clientBuilder->setHosts($hosts);           // Set the hosts
        $this->es = $clientBuilder->build();          // Build the client object
    }

}
