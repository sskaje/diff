<?php
define('MAX_INPUT_SIZE', 1*1024*1024);

define('TYPE_SAME',       0);
define('TYPE_DIFFERENT',  1);
define('TYPE_LEFT_ONLY',  2);
define('TYPE_RIGHT_ONLY', 3);

define('DATA_DIR', '/data');
define('DATA_PATH', __DIR__ . '/data');

define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_QUEUE_KEY', 'onlinediff_queue_key');

function compare($type, $left, $right)
{

    switch ($type) {
        case TYPE_DIFFERENT:
            return array_merge(array_diff($left, $right), array_diff($right, $left));
        case TYPE_LEFT_ONLY:
            return array_diff($left, $right);
        case TYPE_RIGHT_ONLY:
            return array_diff($right, $left);
        case TYPE_SAME:
            return array_intersect($left, $right);
    }
}

function process_array($data)
{
    $data = array_map('trim', $data);
    $data = array_unique($data);
    return $data;
}

function build_data_dir($dirname)
{
    return DATA_PATH . '/' . $dirname;
}

function build_data_path($dirname, $key)
{
    return build_data_dir($dirname) . '/' . $key . '.txt';
}

function save_file($data, $key, $is_file=true)
{
    $dir = build_data_dir(SAVE_DIRNAME);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $data_path = build_data_path(SAVE_DIRNAME, $key);

    if ($is_file) {
        move_uploaded_file($data, $data_path);
    } else {
        if (is_array($data)) {
            $data = implode("\r\n", $data);
        }
        file_put_contents($data_path, $data);
    }
}

function save_result($type, $result)
{
    switch ($type) {
        case TYPE_DIFFERENT:
            $key = 'result_different';
            break;

        case TYPE_SAME:
            $key = 'result_same';
            break;

        case TYPE_LEFT_ONLY:
            $key = 'result_leftonly';
            break;

        case TYPE_RIGHT_ONLY:
            $key = 'result_rightonly';
            break;
    }

    save_file($result, $key, false);
}

function read_as_array($data, $is_file=true)
{
    if ($is_file) {
        $data = file_get_contents($data);
    }

    return preg_split('#[\r\n]#', $data, -1, PREG_SPLIT_NO_EMPTY);
}

function get_redis()
{
    $redis = new Redis();
    $redis->connect(REDIS_HOST, REDIS_PORT);
    return $redis;
}

function save_queue($data)
{
    return get_redis()->lpush(REDIS_QUEUE_KEY, $data);
}

function read_queue()
{
    return get_redis()->rpop(REDIS_QUEUE_KEY);
}

function recycle()
{

}


# EOF
