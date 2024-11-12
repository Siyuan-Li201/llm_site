<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $model = trim($_POST['model']);
    $api_key = $_POST['api_key'];
    $prompt = $_POST['prompt'];

    // 验证 API Key 的格式（可选）
    if (empty($api_key)) {
        echo json_encode([
            'success' => false,
            'message' => 'API Key 不能为空。'
        ]);
        exit;
    }

    // 确保 image 文件存在
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // 文件上传逻辑
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $protocol = (!empty($_SERVER['HTTPS']) ? "https://" : "http://");
            $server_url = $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']);
            $image_url = $server_url . '/' . $target_file;

            if ($model == 'luma') {
                $result = luma_generation_video($api_key, $prompt, $image_url);
            } else if ($model == 'keling') {
                $result = keling_generation_video($api_key, $prompt, $image_url);
            } else if ($model == 'runway') {
                $result = runway_generation_video($api_key, $prompt, $image_url);
            } else {
                error_log('Model selected: ' . $model);
                echo json_encode([
                    'success' => True,
                    'message' => '无效的模型选择'
                ]);
                exit;
            }

            if ($result['task_id']) {
                // 将 API Key 与任务 ID 关联并保存
                save_api_key($result['task_id'], $api_key, $model);

                echo json_encode([
                    'success' => true,
                    'task_id' => $result['task_id'],
                    'prompt' => $prompt
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '任务提交失败。',
                    'debug_info' => $result['debug_info']
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => '文件上传失败。'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => '请上传一张图片。'
        ]);
    }
}

// 生成视频函数 - Luma
function luma_generation_video($api_key, $prompt, $image_url) {
    $url = 'https://api.openai-hub.com/luma/generations';

    $payload = json_encode([
        'user_prompt' => $prompt,
        'aspect_ratio' => '16:9',
        'expand_prompt' => true,
        'loop' => true,
        'image_url' => $image_url,
        'notify_hook' => 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . '/notify_hook.php'
    ], JSON_UNESCAPED_UNICODE);

    $headers = [
        "Authorization: Bearer $api_key",
        'Content-Type: application/json; charset=utf-8'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if (isset($response_data['id'])) {
        return [
            'task_id' => $response_data['id']
        ];
    } else {
        return [
            'task_id' => false,
            'debug_info' => $response_data ?? $curl_error
        ];
    }
}

// 生成视频函数 - 可灵
function keling_generation_video($api_key, $prompt, $image_url) {
    $url = 'https://api.openai-hub.com/kling/v1/videos/image2video';

    $payload = json_encode([
        'image' => $image_url,
        'prompt' => $prompt,
        'callback_url' => 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . '/notify_hook.php'
    ], JSON_UNESCAPED_UNICODE);

    $headers = [
        "Authorization: Bearer $api_key",
        'Content-Type: application/json; charset=utf-8'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    if (isset($response_data['data']['task_id'])) {
        return [
            'task_id' => $response_data['data']['task_id']
        ];
    } else {
        return [
            'task_id' => false,
            'debug_info' => $response_data ?? $curl_error
        ];
    }
}

// 新增：生成视频函数 - Runway
function runway_generation_video($api_key, $prompt, $image_url) {
    $url = 'https://api.openai-hub.com/runway/pro/generate';

    $payload = json_encode([
        "callback_url" => 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . '/notify_hook.php',
        "image" => $image_url,
        "style" => "cinematic", // 电影风格
        "model" => "gen3",
        "prompt" => $prompt,
        "options" => [
            "seconds" => 10,
            "image_as_end_frame" => false,
            "motion_vector" => [
                "x" => -6.2,
                "y" => 0,
                "z" => 0,
                "r" => 0,
                "bg_x_pan" => 0,
                "bg_y_pan" => 0
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);

    $headers = [
        "Authorization: Bearer $api_key",
        'Content-Type: application/json; charset=utf-8'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    $response_data = json_decode($response, true);

    // 根据您提供的 runway API 响应格式进行处理
    if (isset($response_data['data']['task_id'])) {
        return [
            'task_id' => $response_data['data']['task_id']
        ];
    } else {
        return [
            'task_id' => "hello",
            'debug_info' => $response_data ?? $curl_error
        ];
    }
}



// 保存 API Key、模型与任务 ID 的关联
function save_api_key($task_id, $api_key, $model) {
    $data = [
        'api_key' => $api_key,
        'model' => $model
    ];
    $status_dir = 'statuses/';
    if (!is_dir($status_dir)) {
        mkdir($status_dir, 0755, true);
    }
    $status_file = $status_dir . $task_id . '_key.json';
    file_put_contents($status_file, json_encode($data));
}
?>