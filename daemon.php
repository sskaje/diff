<?php

require(__DIR__ . '/lib.php');

$c = 0;
do {
    $data = read_queue();
    if ($data) {
        define('SAVE_DIRNAME', $data);

        $lpath = build_data_path($data, 'input_left');
        $rpath = build_data_path($data, 'input_right');

        $left = read_as_array($lpath, true);
        $right = read_as_array($rpath, true);

        $left = process_array($left);
        $right = process_array($right);

        $type_list  = array(TYPE_SAME, TYPE_DIFFERENT, TYPE_LEFT_ONLY, TYPE_RIGHT_ONLY);

        foreach ($type_list as $type) {
            $result = compare($type, $left, $right);
            save_result($type, $result);
        }

    } else {
        usleep(100);
    }

} while(++$c < 10000);