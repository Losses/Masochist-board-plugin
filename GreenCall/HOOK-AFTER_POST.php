<?php

global $data;

$regex = [
    [
        'pattern' => '/&gt;&gt;(\S*)(\s*)/i',
        'replacement' => '<span class="green_call">&gt;&gt;${1}</span>${2}'
    ],
    [
        'pattern' => '/(^|\b)+#(\S*)/i',
        'replacement' => '<a class="green_call_tag" href="#/search/${1}">#${2}</a>'
    ]
];


for ($i = 0; $i < count($data); $i++) {
    for ($j = 0; $j < count($regex); $j++) {
        $data[$i]['content'] = preg_replace($regex[$j]['pattern'], $regex[$j]['replacement'], $data[$i]['content']);
    }
}
