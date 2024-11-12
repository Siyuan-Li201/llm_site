<?php
// check_status.php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];

    // 从服务器端读取保存的 API Key 和模型
    $api_info = get_api_info($task_id);

    if (!$api_info) {
        echo json_encode(['status' => 'failed', 'message' => '无法获取 API Key 和模型信息']);
        exit;
    }

    $api_key = $api_info['api_key'];
    $model = $api_info['model'];

    if ($model == 'luma') {
        $status = luma_get_video_status($api_key, $task_id);
    } else if ($model == 'keling') {
        $status = keling_get_video_status($api_key, $task_id);
    } else if ($model == 'runway') {
        $status = runway_get_video_status($api_key, $task_id);
    } else {
        echo json_encode(['status' => 'failed', 'message' => '无效的模型选择']);
        exit;
    }

    echo json_encode($status);
} else {
    echo json_encode(['status' => 'failed', 'message' => '未提供 task_id']);
}

function luma_get_video_status($api_key, $task_id) {
    $url = "https://api.openai-hub.com/luma/generations/$task_id";

    $headers = [
        "Authorization: Bearer $api_key"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if (isset($response_data['state'])) {
        $state = $response_data['state'];

        if ($state === 'completed' && isset($response_data['video']['url'])) {
            return [
                'status' => 'completed',
                'video_url' => $response_data['video']['url']
            ];
        } else {
            return ['status' => $state];
        }
    } else {
        return [
            'status' => 'unknown',
            'debug_info' => $response_data ?? $curl_error
        ];
    }
}

function keling_get_video_status($api_key, $task_id) {
    $url = "https://api.openai-hub.com/kling/v1/videos/image2video/$task_id";

    $headers = [
        "Authorization: Bearer $api_key",
        'Content-Type: application/json; charset=utf-8'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); // 使用自定义请求方法
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if (isset($response_data['data']['task_status'])) {
        $state = $response_data['data']['task_status'];

        if ($state === 'succeed' && isset($response_data['data']['task_result']['videos'][0]['url'])) {
            return [
                'status' => 'completed',
                'video_url' => $response_data['data']['task_result']['videos'][0]['url']
            ];
        } else if ($state === 'failed') {
            return [
                'status' => 'failed',
                'message' => $response_data['data']['task_status_msg'] ?? '任务失败'
            ];
        } else {
            return ['status' => $state];
        }
    } else {
        return [
            'status' => 'unknown',
            'debug_info' => $response_data ?? $curl_error
        ];
    }
}


// 新增：获取视频状态函数 - Runway
function runway_get_video_status($api_key, $task_id) {
    $url = 'https://api.openai-hub.com/runway/feed';

    $payload = json_encode([
        'task_id' => $task_id
    ], JSON_UNESCAPED_UNICODE);

    $headers = [
        "Authorization: Bearer $api_key",
        'Content-Type: application/json; charset=utf-8'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true); // POST 请求
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    // 根据您提供的 runway API 响应格式进行处理
    if (isset($response_data['data']['state'])) {
        $state = $response_data['data']['state'];

        if ($state === 'succeeded' && isset($response_data['data']['video_url'])) {
            return [
                'status' => 'completed',
                'video_url' => $response_data['data']['video_url']
            ];
        } else if ($state === 'failed') {
            return [
                'status' => 'failed',
                'message' => $response_data['data']['msg'] ?? '任务失败'
            ];
        } else {
            return ['status' => $state];
        }
    } else {
        return [
            'status' => 'unknown',
            'debug_info' => $response_data ?? $curl_error
        ];
    }
}


// 根据任务 ID 获取保存的 API Key 和模型
function get_api_info($task_id) {
    $status_file = 'statuses/' . $task_id . '_key.json';
    if (file_exists($status_file)) {
        $data = json_decode(file_get_contents($status_file), true);
        return $data ?? false;
    }
    return false;
}
?>