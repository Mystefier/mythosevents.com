<?php
// Deploy, visit once (with ?key=run), then DELETE this file.
if (!isset($_GET['key']) || $_GET['key'] !== 'run-groups-migration-2026') {
    http_response_code(403);
    die('Forbidden');
}

$dbname = "db9dh4gg0yfw3q";
include('join/logintodatabase.php');

$sql = file_get_contents(__DIR__ . '/../group_memberships_migration.sql');

// Split on semicolons (skip empty/comment-only segments)
$statements = array_filter(array_map('trim', explode(';', $sql)), function($s) {
    return $s !== '' && substr(ltrim($s), 0, 2) !== '--';
});

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
