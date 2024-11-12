<?php
// delete.php

// 设置响应头
header('Content-Type: application/json');

// 如果需要，允许跨域请求
header('Access-Control-Allow-Origin: *');

// 获取原始的POST数据
$rawData = file_get_contents('php://input');

// 解析JSON数据
$data = json_decode($rawData, true);

// 检查是否提供了文件名
if (isset($data['filename'])) {
    // 使用basename防止目录遍历攻击
    $filename = basename($data['filename']);

    // 设置上传目录，请根据实际情况修改
    $uploadDir = 'uploads/';

    // 构建文件的完整路径
    $filePath = $uploadDir . $filename;

    // 检查文件是否存在
    if (file_exists($filePath)) {
        // 尝试删除文件
        if (unlink($filePath)) {
            // 文件删除成功
            echo json_encode(['status' => 'success', 'message' => '文件已删除']);
        } else {
            // 文件删除失败
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => '删除文件失败']);
        }
    } else {
        // 文件不存在
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => '文件未找到']);
    }
} else {
    // 未提供文件名
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '未提供文件名']);
}
?>