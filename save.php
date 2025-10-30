<?php
// 1. 设置允许的源
header("Access-Control-Allow-Origin: https://valencie0325.github.io");

// 2. 设置允许的方法（必须包含 OPTIONS 和您实际使用的 POST/GET）
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// 3. 设置允许的头部（假设您使用了 Content-Type）
header("Access-Control-Allow-Headers: Content-Type");

// 4. ***关键：处理 OPTIONS 请求并立即退出***
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(200); // 返回成功的状态码
	exit(); // 停止执行脚本的其余部分
}

// 检查是否是预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	// 预检请求不需要任何内容，只需返回成功状态码
	http_response_code(200);
	exit();
}

// config.php - 数据库配置（UTF-8）
$host = 'sql212.infinityfree.com';
$user = 'if0_40286528';
$pass = 'NhUmtR6rjQ';
$db = 'if0_40286528_hear';

try {
	$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
	]);

	// 创建表（首次运行）
	$pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$input = json_decode(file_get_contents('php://input'), true);
		$message = $input['message'] ?? '';

		if ($message === '') {
			echo json_encode(['error' => 'message 为空'], JSON_UNESCAPED_UNICODE);
			exit;
		}

		$stmt = $pdo->prepare("INSERT INTO messages (message) VALUES (?)");
		$stmt->execute([$message]);

		echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
	} else {
		echo json_encode(['error' => '仅支持 POST'], JSON_UNESCAPED_UNICODE);
	}
} catch (Exception $e) {
	echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>