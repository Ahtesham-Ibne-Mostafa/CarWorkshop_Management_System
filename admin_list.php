<?php
require_once 'config.php';

$pdo = db();
$q = "
SELECT a.id,
       a.client_name,
       a.phone,
       a.car_license,
       a.appointment_date,
       a.slot,
       a.status,
       m.name AS mechanic_name,
       m.id   AS mechanic_id
FROM appointments a
JOIN mechanics m ON m.id = a.mechanic_id
ORDER BY a.appointment_date DESC, a.created_at DESC
";
$stmt = $pdo->query($q);
json(['appointments' => $stmt->fetchAll()]);
