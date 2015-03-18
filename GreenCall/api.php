<?php
global $plugin;

if (isset($_POST['target_id'])) {
    require_once('../libs/parsedown.php');

    global $database;
    global $emotion;
    $Parsedown = new Parsedown();

    $data_sql =
        [
            'id',
            'author',
            'content'
        ];
    $where_sql =
        [
            'id[=]' => $_POST['target_id']
        ];
    $data = $database->select('content', $data_sql, $where_sql);

    for ($i = 0; $i < count($data); $i++) {
        $data[$i]['content'] =
            $emotion->phrase(RemoveXSS($Parsedown->text($data[$i]['content'])));
    }

    echo json_encode($data[0]);
    exit();
}
