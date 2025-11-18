<?php
require_once 'config.php';

$appointment_id   = (int)($_POST['appointment_id'] ?? 0);
$new_date         = sanitize($_POST['appointment_date'] ?? '');
$new_mechanic_id  = isset($_POST['mechanic_id']) ? (int)$_POST['mechanic_id'] : null;
$new_slot         = sanitize($_POST['slot'] ?? '');

if ($appointment_id <= 0) json(['error' => 'Invalid appointment id'], 400);
if ($new_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $new_date)) {
  json(['error' => 'Invalid date format (YYYY-MM-DD)'], 400);
}

$validSlots = ['9-11','11.30-1.30','2-4','4.30-6.30'];
if ($new_slot && !in_array($new_slot, $validSlots)) {
  json(['error' => 'Invalid slot selected'], 400);
}

$pdo = db();
$pdo->beginTransaction();

try {
  // fetch existing appointment
  $rowStmt = $pdo->prepare("SELECT phone, appointment_date, mechanic_id, slot, status FROM appointments WHERE id = :id LIMIT 1");
  $rowStmt->execute([':id' => $appointment_id]);
  $row = $rowStmt->fetch();
  if (!$row) { $pdo->rollBack(); json(['error' => 'Appointment not found'], 404); }

  $target_date     = $new_date ?: $row['appointment_date'];
  $target_mechanic = $new_mechanic_id ?: (int)$row['mechanic_id'];
  $target_slot     = $new_slot ?: $row['slot'];

  // duplicate check (same client, same date, exclude this appointment)
  $dup = $pdo->prepare("SELECT id FROM appointments WHERE phone = :p AND appointment_date = :d AND status = 'approved' AND id <> :id LIMIT 1");
  $dup->execute([':p' => $row['phone'], ':d' => $target_date, ':id' => $appointment_id]);
  if ($dup->fetch()) { $pdo->rollBack(); json(['error' => 'Client already has an appointment on that date'], 409); }

  // mechanic availability
  $mStmt = $pdo->prepare("SELECT id, is_active FROM mechanics WHERE id = :id LIMIT 1");
  $mStmt->execute([':id' => $target_mechanic]);
  $m = $mStmt->fetch();
  if (!$m || !$m['is_active']) { $pdo->rollBack(); json(['error' => 'Mechanic not available'], 400); }

  // slot availability (exclude this appointment)
  $slotStmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE mechanic_id = :mid AND appointment_date = :d AND slot = :slot AND status = 'approved' AND id <> :id
  ");
  $slotStmt->execute([':mid' => $target_mechanic, ':d' => $target_date, ':slot' => $target_slot, ':id' => $appointment_id]);
  if ((int)$slotStmt->fetchColumn() > 0) {
    $pdo->rollBack();
    json(['error' => 'Mechanic already booked in this slot'], 409);
  }

  // update appointment
  $up = $pdo->prepare("UPDATE appointments SET appointment_date = :d, mechanic_id = :mid, slot = :slot WHERE id = :id");
  $up->execute([':d' => $target_date, ':mid' => $target_mechanic, ':slot' => $target_slot, ':id' => $appointment_id]);

  $pdo->commit();
  json(['message' => 'Appointment updated']);
} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  json(['error' => 'Server error'], 500);
}
