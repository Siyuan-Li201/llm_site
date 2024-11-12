<?php
header('Content-Type: application/json');

// 设置上传目录
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 获取当前已有文件数量，确定下一个文件编号
$existingFiles = glob($uploadDir . 'file*');
$fileCount = count($existingFiles) + 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];

        // 文件类型和大小验证
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['error' => '不支持的文件类型']);
            exit;
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB限制
            http_response_code(400);
            echo json_encode(['error' => '文件大小超过限制']);
            exit;
        }

        // 生成新的文件名
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = 'file' . $fileCount . '.' . $extension;
        $targetFilePath = $uploadDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            // 获取文件的URL
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                        $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/uploads/" . $newFilename;
            echo json_encode(['url' => $url]);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(['error' => '文件上传失败']);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => '没有文件被上传']);
        exit;
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => '仅支持 POST 方法']);
    exit;
}
?>