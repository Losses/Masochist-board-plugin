<?php
/**
 * Created by PhpStorm.
 * User: Don
 * Date: 5/3/2015
 * Time: 11:43 AM
 */

global $finish_notice;
global $database;

$statistic_sheet = [];
$mark_group = [];
$final_mark = 0;

$count_group_name = array_keys($sheet_content['info']['countGroup']);

//分组积分
foreach ($query_insert as $single_query) {
    $answer_key = $single_query['key'];
    if (!isset($question_table[$answer_key]) || !isset($question_table[$answer_key]['group']))
        continue;

    $answer_group_id = $question_table[$answer_key]['group'];

    if (in_array($answer_group_id, $count_group_name)) {
        if (!isset($mark_group[$answer_group_id]))
            $mark_group[$answer_group_id] = 0;

        $mark_group[$answer_group_id] += (int)array_keys($question_table[$answer_key]['selection'])[(int)$single_query['value']];
    }
}

$finish_notice = '';

$group_count = 1;
foreach ($mark_group as $group_id => $group_mark) {
    $group_name = $sheet_content['info']['countGroup'][$group_id];
    $group_key = isset($group_id) ? $group_id : 'g' . str_pad($group_count, 2, "0", STR_PAD_LEFT);
    $final_mark += $group_mark;

    $query_insert[] = [
        'user' => $_SESSION['LimeSurvey']['id'],
        'sheet' => $_POST['sheet'],
        'type' => 'mark',
        'key' => $group_key,
        'value' => $group_mark
    ];

    $group_count++;
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