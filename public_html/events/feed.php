<?php
// Small JSON feed of upcoming events, used by the homepage's dynamic "More Events" section.
include(__DIR__ . '/../join/logintodatabase.php');
header('Content-Type: application/json');

$limit = 6;
$stmt = $conn->prepare("
    SELECT o.id AS occurrence_id, o.start_date, o.start_time, o.location,
           e.title, e.event_type, e.cover_image_url
    FROM event_occurrences o
    JOIN events e ON o.event_id = e.id
    WHERE e.status = 'approved' AND o.status = 'scheduled' AND o.start_date >= CURDATE()
    ORDER BY o.start_date ASC
    LIMIT ?
");
$stmt->bind_param("i", $limit);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode($events);
