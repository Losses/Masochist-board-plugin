<?php
/**
 * Created by PhpStorm.
 * User: Don
 * Date: 4/1/2015
 * Time: 4:31 PM
 */

global $data;
global $plugin;

if ($plugin->config['sakura.losses.kimoji.name']['KM_SCOPE'] != false) {
    function name_shit ($post_id) {
        global $data;
        global $plugin;

        $kimoji_list = $plugin->config['sakura.losses.kimoji.name']['KM_LIST'];

        $data[$post_id]['author'] = $kimoji_list[array_rand($kimoji_list, 1)];
    }

    for ($i = 0; $i < count($data); $i++) {
        if ($plugin->config['sakura.losses.kimoji.name']['KM_SCOPE'] == 'all')
            name_shit($i);
        else if (is_array($plugin->config['sakura.losses.kimoji.name']['KM_SCOPE'])
            && in_array($data[$i]['category'], $plugin->config['sakura.losses.kimoji.name']['KM_SCOPE']))
            name_shit($i);
    }
}