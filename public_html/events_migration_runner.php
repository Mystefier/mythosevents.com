<?php
// Deploy, visit once (with ?key=run), then DELETE this file.
if (!isset($_GET['key']) || $_GET['key'] !== 'run-events-migration-2026') {
    http_response_code(403);
    die('Forbidden');
}

$dbname = "db9dh4gg0yfw3q";
include('join/logintodatabase.php');

$statements = [
    "CREATE TABLE IF NOT EXISTS `events` (
      `id`              INT AUTO_INCREMENT PRIMARY KEY,
      `organizer_id`    INT NOT NULL,
      `title`           VARCHAR(255) NOT NULL,
      `description`     TEXT DEFAULT NULL,
      `event_type`      VARCHAR(64)  DEFAULT NULL,
      `start_date`      DATE NOT NULL,
      `start_time`      TIME DEFAULT NULL,
      `end_date`        DATE DEFAULT NULL,
      `end_time`        TIME DEFAULT NULL,
      `location`        VARCHAR(255) DEFAULT NULL,
      `latitude`        FLOAT DEFAULT NULL,
      `longitude`       FLOAT DEFAULT NULL,
      `website`         VARCHAR(255) DEFAULT NULL,
      `contact_email`   VARCHAR(255) DEFAULT NULL,
      `status`          ENUM('draft', 'pending_approval', 'approved', 'rejected') DEFAULT 'pending_approval',
      `rejection_reason` TEXT DEFAULT NULL,
      `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at`      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (organizer_id) REFERENCES people(id) ON DELETE CASCADE,
      KEY idx_organizer (organizer_id),
      KEY idx_status (status),
      KEY idx_start_date (start_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$results = [];
foreach ($statements as $statement) {
    if (mysqli_query($conn, $statement)) {
        $results[] = ['ok', htmlspecialchars(substr($statement, 0, 80)) . '…'];
    } else {
        $results[] = ['err', htmlspecialchars(mysqli_error($conn)) . ' — ' . htmlspecialchars(substr($statement, 0, 80)) . '…'];
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Migration</title>
<style>body{font-family:monospace;padding:24px;}li{margin:4px 0;}.ok{color:green;}.err{color:red;}</style>
</head><body>
<h2>Events Table Migration</h2>
<ul>
<?php foreach ($results as [$status, $msg]): ?>
  <li class="<?php echo $status; ?>"><?php echo $status === 'ok' ? '✓' : '✗'; ?> <?php echo $msg; ?></li>
<?php endforeach; ?>
</ul>
<p><strong>Done. Delete this file now.</strong></p>
</body></html>
