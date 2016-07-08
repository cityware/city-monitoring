<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models;

use Cityware\Monitoring\Models\AbstractModels;

/**
 * Description of Devices
 *
 * @author fsvxavier
 */
class Devices extends AbstractModels {

    public function getDevices() {
        $this->getConnection();
        $this->db->select("*");
        $this->db->from('tab_device', null, 'nocomsys');
        $rsDevices = $this->db->executeSelectQuery();
        return $rsDevices;
    }

}
