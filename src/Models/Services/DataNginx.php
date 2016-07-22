<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Monitoring\Models\Services;

/**
 * Description of DataNginx
 *
 * @author fsvxavier
 */
class DataNginx {

    private function prepareDataNginxStatus($param) {
        $ret = file_get_contents('http://172.16.20.244/nginx_status');
        $convert = explode("\n", $ret); //create array separate by new line

        $nginx = Array();

        $dataReplace = Array('Active connections: ', 'Reading: ', 'Writing: ', 'Waiting: ');
        $serverConnenctions = explode(" ", trim($convert[2]));
        $rwwNginx = trim(str_replace($dataReplace, "", $convert[3]));
        $rwwNginxData = explode(" ", trim($rwwNginx));

        $nginx['activeConnections'] = trim(str_replace($dataReplace, "", $convert[0]));

        $nginx['acceptedConnections'] = $serverConnenctions[0];
        $nginx['handledConnections'] = $serverConnenctions[1];
        $nginx['handledRequests'] = $serverConnenctions[2];

        $nginx['reading'] = $rwwNginxData[0];
        $nginx['writing'] = $rwwNginxData[1];
        $nginx['waiting'] = $rwwNginxData[2];

        $nginx['requestsPerConnections'] = $nginx['handledRequests'] / $nginx['handledConnections'];
        $nginx['keepAliveConnections'] = $nginx['aConnections'] - ($nginx['reading'] + $nginx['writing']);

        echo '<pre>';
        print_r($nginx);
        exit;
    }

}
