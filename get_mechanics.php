<?php
require_once 'config.php';

$date = $_GET['date'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
  json(['error' => 'Invalid or missing date (YYYY-MM-DD).'], 400);
}

$pdo = db();

// get all active mechanics
$mechanics = $pdo->query("SELECT id, name FROM mechanics WHERE is_active = 1 ORDER BY name")->fetchAll();

$slots = ['9-11','11.30-1.30','2-4','4.30-6.30'];

foreach ($mechanics as &$m) {
  $freeSlots = [];
  foreach ($slots as $s) {
    $stmt = $pdo->prepare("
      SELECT COUNT(*) FROM appointments
      WHERE mechanic_id = :mid AND appointment_date = :d AND slot = :slot AND status = 'approved'
    ");
    $stmt->execute([':mid' => $m['id'], ':d' => $date, ':slot' => $s]);
    $count = (int)$stmt->fetchColumn();
    if ($count == 0) {
      $freeSlots[] = $s; // slot is free
    }
  }
  $m['free_slots'] = $freeSlots;
}

json(['mechanics' => $mechanics]);
