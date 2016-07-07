<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models;

/**
 * Description of Devices
 *
 * @author fsvxavier
 */
class Devices {

    private $db;
    
    public function __construct() {
        $this->db = \Cityware\Db\Factory::factory();
    }

    public function getDevices() {
        
    }

}
