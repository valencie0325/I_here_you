<?php
// --- PHP Setup (TOP of file) ---
$host = 'sql212.infinityfree.com';
$user = 'if0_40286528';
$pass = 'NhUmtR6rjQ';
$db = 'if0_40286528_hear';

// Define variables with defaults
$start = $_GET['start'] ?? date('Y-m-d');  // Default: today
$end = $_GET['end'] ?? '';             // Default: blank (latest)
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>I Hear You</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<style>
		.table-fixed {
			table-layout: fixed;
			width: 100%;
		}

		.col-id {
			width: 10%;
		}

		.col-msg {
			width: 75%;
		}

		.col-time {
			width: 15%;
		}
	</style>
</head>

<body class="bg-light">
	<div class="container py-4">
		<h2 class="mb-4">I Hear You Message Query</h2>

		<form method="GET" class="row g-3 mb-4">
			<div class="col-md-4">
				<label class="form-label">Start Date</label>
				<input type="date" name="start" class="form-control" value="<?= htmlspecialchars($start) ?>" required>
			</div>
			<div class="col-md-4">
				<label class="form-label">End Date (leave blank = latest)</label>
				<input type="date" name="end" class="form-control" value="<?= htmlspecialchars($end) ?>">
			</div>
			<div class="col-md-2 d-flex align-items-end">
				<button type="submit" class="btn btn-primary w-100">Search</button>
			</div>
			<div class="col-md-2 d-flex align-items-end">
				<a href="download.php?<?php echo http_build_query($_GET); ?>" class="btn btn-success w-100">Download</a>
			</div>
		</form>

		<?php
		// --- PHP Query Logic ---
		

		try {
			$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			]);

			$where = 'WHERE DATE(created_at) >= ?';
			$params = [$start];
			if ($end) {
				$where .= ' AND DATE(created_at) <= ?';
				$params[] = $end;
			}

			// Count total
			$countStmt = $pdo->prepare("SELECT COUNT(*) FROM messages $where");
			$countStmt->execute($params);
			$total = $countStmt->fetchColumn();
			$totalPages = ceil($total / $limit);

			// Query with proper integer binding
			$sql = "SELECT id, message, created_at FROM messages $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
			$stmt = $pdo->prepare($sql);

			// Bind date params first
			foreach ($params as $i => $param) {
				$stmt->bindValue($i + 1, $param);
			}
			// Bind LIMIT and OFFSET as integers
			$stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
			$stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);

			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
			$rows = [];
			$totalPages = 1;
		}
		?>

		<div class="card">
			<div class="card-body">
				<p class="text-muted">Total: <?= $total ?> records</p>
				<div class="table-responsive">
					<table class="table table-striped table-hover table-fixed">
						<thead class="table-dark">
							<tr>
								<th class="col-id">ID</th>
								<th class="col-msg">Message</th>
								<th class="col-time">Time</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($rows as $row): ?>
								<tr>
									<td><?= $row['id'] ?></td>
									<td><?= htmlspecialchars($row['message']) ?></td>
									<td><?= $row['created_at'] ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<!-- Pagination -->
				<nav aria-label="Pagination">
					<ul class="pagination justify-content-center">
						<li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
							<a class="page-link"
								href="?page=<?= $page - 1 ?>&start=<?= urlencode($start) ?>&end=<?= urlencode($end) ?>">Previous</a>
						</li>
						<?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
							<li class="page-item <?= $i == $page ? 'active' : '' ?>">
								<a class="page-link"
									href="?page=<?= $i ?>&start=<?= urlencode($start) ?>&end=<?= urlencode($end) ?>"><?= $i ?></a>
							</li>
						<?php endfor; ?>
						<li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
							<a class="page-link"
								href="?page=<?= $page + 1 ?>&start=<?= urlencode($start) ?>&end=<?= urlencode($end) ?>">Next</a>
						</li>
					</ul>
				</nav>
			</div>
		</div>
		<footer class="bg-dark text-white mt-5 py-4">
			<div class="container text-center">
				<p class="mb-1">&copy; <?= date('Y') ?> I Hear You Messages. All rights reserved.</p>
				<p class="mb-1">Made by <strong>Junxia Wang</strong></p>
				<p class="mb-0">Released on <strong>October 30, 2025</strong></p>
			</div>
		</footer>
	</div>
</body>

</html>