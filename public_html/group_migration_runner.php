<?php
// Deploy, visit once (with ?key=run), then DELETE this file.
if (!isset($_GET['key']) || $_GET['key'] !== 'run-groups-migration-2026') {
    http_response_code(403);
    die('Forbidden');
}

$dbname = "db9dh4gg0yfw3q";
include('join/logintodatabase.php');

$statements = [
    "CREATE TABLE IF NOT EXISTS `groups` (
      `id`          INT AUTO_INCREMENT PRIMARY KEY,
      `slug`        VARCHAR(64)  NOT NULL UNIQUE,
      `name`        VARCHAR(128) NOT NULL,
      `icon`        VARCHAR(16)  DEFAULT NULL,
      `description` TEXT         DEFAULT NULL,
      `join_url`    VARCHAR(255) DEFAULT NULL,
      `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "INSERT IGNORE INTO `groups` (slug, name, icon, description, join_url) VALUES
      ('sonlight', 'Sonlight Drama Team', '☀️',
       'A weekly drama team exploring faith and theater together — one Bible question, one theater question, every Sunday.',
       '/sonlight/join.php')",

    "CREATE TABLE IF NOT EXISTS `group_memberships` (
      `id`        INT AUTO_INCREMENT PRIMARY KEY,
      `person_id` INT NOT NULL,
      `group_id`  INT NOT NULL,
      `joined_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY `uq_person_group` (`person_id`, `group_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "INSERT IGNORE INTO group_memberships (person_id, group_id)
     SELECT p.id, g.id
     FROM people p
     JOIN `groups` g ON g.slug = 'sonlight'
     WHERE p.roles LIKE '%Sonlight Drama Team%'",
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
<h2>Group Memberships Migration</h2>
<ul>
<?php foreach ($results as [$status, $msg]): ?>
  <li class="<?php echo $status; ?>"><?php echo $status === 'ok' ? '✓' : '✗'; ?> <?php echo $msg; ?></li>
<?php endforeach; ?>
</ul>
<p><strong>Done. Delete this file now.</strong></p>
</body></html>
