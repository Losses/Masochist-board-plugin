<?php
/**
 * Created by PhpStorm.
 * User: Don
 * Date: 3/19/2015
 * Time: 10:13 PM
 */

global $finish_notice;
global $database;

$statistic_sheet = [];
$mark_group = [];
$count_group = [12, 10, 9, 13, 10, 6, 7, 6, 10, 7];
$final_mark = 0;

for ($i = 0; $i < count($query_insert); $i++) {
    $answer_key = (int)explode('-', $query_insert[$i]['key'])[1];

    if (!isset($sheet_content['sheet'][$answer_key]))
        continue;

    $answer_group_id = $sheet_content['sheet'][$answer_key]['group'];

    if (!isset($mark_group[$answer_group_id]))
        $mark_group[$answer_group_id] = 0;

    $mark_group[$answer_group_id] += ((int)$query_insert[$i]['value'] + 1);
}

$finish_notice = '';

for ($i = 0; $i < count($mark_group); $i++) {
    $group_name = $sheet_content['info']['countGroup'][$i];
    $group_mark = $mark_group[$i] / $count_group[$i];
    $final_mark += $mark_group[$i];

    $query_insert[] = [
        'user' => $_SESSION['LimeSurvey']['id'],
        'sheet' => $_POST['sheet'],
        'type' => 'mark',
        'key' => 'g' . str_pad($i, 2, "0", STR_PAD_LEFT),
        'value' => $group_mark
    ];

    $finish_notice .= $group_name . '：' . $group_mark . '；';
}

$query_insert[] = [
    'user' => $_SESSION['LimeSurvey']['id'],
    'sheet' => $_POST['sheet'],
    'type' => 'mark',
    'key' => 'final_mark',
    'value' => $final_mark
];

$final_query = $database->insert('limesurvey', $query_insert);

response_message(200, $finish_notice);
exit();