<?php

/**
 * Created by PhpStorm.
 * User: Don
 * Date: 3/26/2015
 * Time: 4:10 PM
 */
class r
{
    private $location;

    function __construct()
    {
        global $plugin;

        $this->location = $plugin->config["losses.lime.survey.calculate"]["LSC_R_LOCATION"];
    }

    public function run()
    {
        $arguments = func_get_args();
        $file_name = 'scripts/' . $arguments[0] . '.r';
        $r_arguments = '';

        for ($i = 1; $i < count($arguments); $i++) {
            $r_arguments .= $arguments[$i];

            if ($i = count($arguments) - 1)
                break;

            $r_arguments .= ',';
        }

        exec("$this->location $file_name $r_arguments");
    }
}