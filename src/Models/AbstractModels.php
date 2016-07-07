<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models;

/**
 * Description of AbstractModels
 *
 * @author fsvxavier
 */
class AbstractModels {

    public $db;

    public function getConnection() {
         $this->db = \Cityware\Db\Factory::factory();
         return $this;
    }

}
