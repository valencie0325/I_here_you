<?php
// download.php - 下载 CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="messages.csv"');

$host = 'sql212.infinityfree.com';
$user = 'if0_40286528';
$pass = 'NhUmtR6rjQ';
$db = 'if0_40286528_hear';

try {
	$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
	]);

	$stmt = $pdo->query("SELECT id, message, created_at FROM messages ORDER BY created_at DESC");
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$output = fopen('php://output', 'w');
	fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM
	fputcsv($output, ['ID', 'Message', 'Created At']);

	foreach ($rows as $row) {
		fputcsv($output, [$row['id'], $row['message'], $row['created_at']]);
	}
	fclose($output);
} catch (Exception $e) {
	echo "错误: " . $e->getMessage();
}
?>