<?php
/**
 * Created by PhpStorm.
 * User: Don
 * Date: 3/18/2015
 * Time: 5:53 PM
 */

$plugin_info = [
    "IDENTIFICATION" => "losses.lime.survey.calculate"
];

$plugin_injector = [

];

$plugin_config = [
    "LSC_R_LOCATION" => 'F:\R\R-3.1.3\bin\Rscript.exe',
    "LSC_WIN_CMD_LOCATION" => 'C:\\windows\\system32\\cmd.exe /c'
];

$plugin_public_page = [
    'lsc' => [
        'html' => 'lsc.html',
        'script' => 'lsc.js'
    ]
];