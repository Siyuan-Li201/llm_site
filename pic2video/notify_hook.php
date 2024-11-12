<?php
// notify_hook.php

// 获取回调数据
$raw_post_data = file_get_contents('php://input');
$callback_data = json_decode($raw_post_data, true);

// 获取task_id
$task_id = $callback_data['id'] ?? $callback_data['data']['task_id'] ?? null;

if (!$task_id) {
    // 如果无法获取 task_id，则记录错误日志
    file_put_contents('callback_error_log.txt', date('Y-m-d H:i:s') . ' - 无法获取 task_id - ' . $raw_post_data . PHP_EOL, FILE_APPEND);
    exit;
}

// 将状态保存到文件（或数据库）
$status_file = 'statuses/' . $task_id . '.json';
if (!is_dir('statuses/')) {
    mkdir('statuses/', 0755, true);
}

file_put_contents($status_file, json_encode($callback_data));

// 将回调数据写入日志文件（可选）
file_put_contents('callback_log.txt', date('Y-m-d H:i:s') . ' - ' . $raw_post_data . PHP_EOL, FILE_APPEND);
?>