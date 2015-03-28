<?php

    require_once ('ColorfulMonkey.php');
    
    $colorful_monkey = new ColorfulMonkey();
    
    global $data;
    
    $data = $colorful_monkey->color_change($data);
