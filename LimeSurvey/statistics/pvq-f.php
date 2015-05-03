<?php
/**
 * Created by PhpStorm.
 * User: Don
 * Date: 5/2/2015
 * Time: 6:51 PM
 */

global $finish_notice;
global $database;

$question_table = $sheet_content['sheet'];

$statistic_sheet = [];
$mark_group = [];
$count_group = [];
$final_mark = 0;

foreach ($question_table as $single_question) {
    if (isset($single_question['group'])) {
        if (!isset ($count_group[$single_question['group']]))
            $count_group[$single_question['group']] = 0;

        $count_group[$single_question['group']]++;
    }
}

for ($i = 0; $i < count($query_insert); $i++) {
    $answer_key = (int)end(explode('-', $query_insert[$i]['key']));

    if (!isset($sheet_content['sheet'][$answer_key]))
        continue;

    $answer_group_id = $sheet_content['sheet'][$answer_key]['group'];

    if (!isset($mark_group[$answer_group_id]))
        $mark_group[$answer_group_id] = 0;

    $mark_group[$answer_group_id] += (int)array_keys($question_table[$answer_key]['selection'])[(int)$query_insert[$i]['value']];
}

$finish_notice = '';

foreach ($mark_group as $group_id => $group_mark) {
    $group_name = $sheet_content['info']['countGroup'][$group_id];
    $group_key = isset($group_id) ? $group_id : 'g' . str_pad($i, 2, "0", STR_PAD_LEFT);
    $final_mark += $group_mark;

    $query_insert[] = [
        'user' => $_SESSION['LimeSurvey']['id'],
        'sheet' => $_POST['sheet'],
        'type' => 'mark',
        'key' => $group_key,
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

//$query_insert = str_replace(['pvq-f', 'pvq-m'], 'pvq', $query_insert);

$final_query = $database->insert('limesurvey', $query_insert);

response_message(200, $finish_notice);
exit();