<?php
/**
 * Created by PhpStorm.
 * User: Don
 * Date: 4/7/2015
 * Time: 6:43 PM
 */

require_once('r.php');

$r = new r();

$r->run('export_data', $r->export_tables('healthy', 'scl90'));