<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Jobs;

/**
 * Description of Ping
 *
 * @author fsvxavier
 */
class Ping {
    
    public function executePing(array $params) {
        $ping = new \Cityware\Utility\Ping($params['num_ip']);

        return $ping->ping();
    }

}
