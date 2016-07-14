<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models;

/**
 * Description of DataPhpFpm
 *
 * @author fsvxavier
 */
class DataPhpFpm {

    private function prepareDataPhpFpmStatus() {
        $php = file_get_contents('http://172.16.20.244/php_status?full&json');
        $dataPhpFpm = json_decode($php);

        echo '<pre>';
        print_r($dataPhpFpm);
        exit;
    }

}
