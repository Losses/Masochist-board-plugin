<?php

global $data;

$pattern = '/&gt;&gt;(\S*)\s/i';
$replacement = '<span class="green_call">&gt;&gt;${1}</span>';

for ($i = 0; $i < count($data); $i++) {
    $data[$i]['content'] = preg_replace($pattern, $replacement, $data[$i]['content']);
}
