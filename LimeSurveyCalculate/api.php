<?php
/**
 * Created by PhpStorm.
 * User: Don
 * Date: 4/7/2015
 * Time: 6:43 PM
 */

require_once('r.php');
global $plugin;

$dir_location = $plugin->config['losses.lime.survey.calculate']['dir_location'];
$r = new r();

if (!isset($_POST['r_action']))
    exit();

$file_name_regex = '/[\/\\\\]\S*[\/\\\\](\S*?)\.R/';

switch ($_POST['r_action']) {
    case 'export':
        $file_name = $r->export_tables('healthy', 'scl90');
        $r->run('export_data', $file_name);

        if ($file_name) {
            response_message(200, $file_name);
        }
        break;
    case 'script_list':
        $list = glob("$dir_location/scripts/*.R");
        $options = [];
        foreach ($list as $item) {
            $file_dir = end(explode(DIRECTORY_SEPARATOR, $item));
            preg_match($file_name_regex, $file_dir, $file_name);
            $file_name = $file_name[1];

            $script_content = file_get_contents($item);
            $option_string = explode('#--INTRODUCE END--#', $script_content);

            if (count($option_string) <= 1)
                continue;

            $options_string = explode("\n", $option_string[0]);

            $options[$file_name] = [];

            foreach ($options_string as $single_option) {

                if (count($option_content = explode('#@', $single_option)) > 1) {
                    $option_para = explode(' ', $option_content[1]);

                    switch ($option_para[0]) {
                        case 'NAME':
                            $options[$file_name]['name'] = $option_para[1];
                            break;
                        case 'OPTION':
                            $this_option = [];
                            $this_option['id'] = $option_para[1];
                            $this_option['name'] = $option_para[2];

                            preg_match_all('/\[(\S+?)\]/', $single_option, $option_parameters);

                            foreach ($option_parameters[1] as $single_parameter) {
                                $decode = [];
                                $divided_parameter = explode('|', $single_parameter);
                                $decode['name'] = $divided_parameter[0];
                                if (count($divided_parameter) > 1) {
                                    $decode['options'] = array_slice($divided_parameter, 1);
                                }

                                $options[$file_name]['option'][] = $decode;
                            }
                            break;
                    }
                }
            }

        }
        echo json_encode($options);
        break;
    case 'run_script':

}
