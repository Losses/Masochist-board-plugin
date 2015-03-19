<?php
/**
 * Created by PhpStorm.
 * User: Don
 * Date: 3/19/2015
 * Time: 10:45 AM
 */

global $plugin;
global $database;

$sheet_content = [];
$sheet_name = 'unnamed_sheet';

if (!isset($_SESSION['LimeSurvey']['id']))
    $_SESSION['LimeSurvey']['id'] = md5(md5(get_ip_address() . date('Y-m-d H:i:s')));

if (isset($_POST['sheet'])) {
    $dir_location = $plugin->config['losses.lime.survey']['dir_location'];
    $sheet_name = $_POST['sheet'];

    if (!is_file($sheet_location = "$dir_location/question_db/$sheet_name.json"))
        response_message(404, "$sheet_location");

    $sheet_content = json_decode(file_get_contents($sheet_location), true);
}

if (isset($_POST['action'])) {
    if ($_POST['action'] == 'get') {
        $return = ['sheet_info' => [], 'question' => []];
        $questions = $sheet_content['sheet'];

        $return['sheet_info'] = [
            'title' => $sheet_content['info']['title'],
            'guidePage' => $sheet_content['info']['guidePage'],
            'questionPerPage' => $sheet_content['info']['questionPerPage']
        ];

        for ($i = 0; $i < count($questions); $i++) {
            $single_question = $questions[$i];
            $pad_id = str_pad($i, 2, "0", STR_PAD_LEFT);
            $id = isset($questions[$i]['id']) ? $questions[$i]['id'] : "$sheet_name-$pad_id";

            $return['question'][$id]['id'] = $id;

            $required_fill = ['question', 'type', 'required'];

            for ($l = 0; $l < count($required_fill); $l++) {
                if (isset($single_question[$required_fill[$l]]))
                    $return['question'][$id][$required_fill[$l]] = $single_question[$required_fill[$l]];
            }

            if (isset($single_question['selection'])) {
                $return['question'][$id]['selection'] = [];
                $j = 0;
                foreach ($single_question['selection'] as $selection) {
                    $pad_selection = str_pad($j, 2, "0", STR_PAD_LEFT);
                    $return['question'][$id]['selection']["s$pad_selection"] = $selection;
                    $j = $j + 1;
                }
            }
        }

        echo json_encode($return);
        exit();
    }

    if ($_POST['action'] == 'submit') {

    }
}