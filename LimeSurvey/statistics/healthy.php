<?php
/**
 * Created by PhpStorm.
 * User: Don
 * Date: 3/19/2015
 * Time: 10:13 PM
 */

global $finish_notice;

$statistic_sheet = [];
$sheet_id = [];
$final_mark = 0;

for ($i = 0; $i < count($sheet_content['sheet']); $i++) {
    $sheet_id[] = $sheet_content['sheet'][$i]['id'];
}

for ($i = 0; $i < 20; $i++) {
    $question_id = array_search($query_insert[$i]['key'], $sheet_id);
    $question_mark = (int)array_keys($sheet_content['sheet'][$question_id]['selection'])[(int)$query_insert[$i]['value']];
    $final_mark += $question_mark;
}

for ($i = 19; $i < count($query_insert); $i++) {
    switch (explode('_', $query_insert[$i]['key'])[0]) {
        case 'hg21':
            $final_mark += 2;
            break;
        case 'hg22':
            $final_mark -= 2;
            break;
    }
}

$query_insert[] = [
    'user' => $_SESSION['LimeSurvey']['id'],
    'sheet' => $_POST['sheet'],
    'type' => 'mark',
    'key' => 'g0',
    'value' => $final_mark
];

$finish_notice = "测验完成，您的最终得分为 $final_mark 分满分为 78 分。";


