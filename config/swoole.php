<?php
/**
 * Created by PhpStorm.
 * User: GraydonChen
 * Date: 2017/4/26
 * Time: 18:20
 */
return array(
    "Host" => "0.0.0.0",
    "Set" => array(
        'worker_num' => 1,
        'dispatch_mode' => 3,
        'task_worker_num' => 6,
        'max_request'=>0,
        'task_ipc_mode' => 2,
        'task_max_request'=>0,
        'message_queue_key'=>65535 + 9501*10,

        //'open_eof_split' => 1,
        //'package_eof' => "@@",

        //'heartbeat_check_interval'=>10,
        //'heartbeat_idle_time'=>30,
        'discard_timeout_request'=>true,
        'enable_unsafe_event' => true,
        'log_file'=>'/data/wwwroot/crontab/log/hoo.log',
        'log_level'=>1,
    )
);