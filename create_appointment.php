<?php
require_once 'config.php';

$data = $_POST;
$required = ['client_name','address','phone','car_license','car_engine','appointment_date','mechanic_id','slot'];
foreach ($required as $r) {
  if (!isset($data[$r]) || trim($data[$r]) === '') {
    json(['error' => "Missing field: $r"], 400);
  }
}

$client_name       = sanitize($data['client_name']);
$address           = sanitize($data['address']);
$phone             = sanitize($data['phone']);
$car_license       = sanitize($data['car_license']);
$car_engine        = sanitize($data['car_engine']);
$appointment_date  = sanitize($data['appointment_date']);
$mechanic_id       = (int)$data['mechanic_id'];
$slot              = sanitize($data['slot']);

$validSlots = ['9-11','11.30-1.30','2-4','4.30-6.30'];
if (!in_array($slot, $validSlots)) {
  json(['error' => 'Invalid slot selected'], 400);
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointment_date)) {
  json(['error' => 'Invalid date format. Use YYYY-MM-DD.'], 400);
}
if (!preg_match('/^\+?\d{6,15}$/', $phone)) {
  json(['error' => 'Phone must be digits with optional + (6-15).'], 400);
}
if (!preg_match('/^[A-Za-z0-9\- ]{3,}$/', $car_license)) {
  json(['error' => 'Invalid car license (min 3, alphanumeric).'], 400);
}
if (!preg_match('/^[A-Za-z0-9\-]{3,}$/', $car_engine)) {
  json(['error' => 'Invalid engine number (min 3, alphanumeric).'], 400);
}

$pdo = db();
$pdo->beginTransaction();

try {
  // check duplicate booking by phone+date
  $dup = $pdo->prepare("SELECT id FROM appointments WHERE phone = :p AND appointment_date = :d AND status = 'approved' LIMIT 1");
  $dup->execute([':p' => $phone, ':d' => $appointment_date]);
  if ($dup->fetch()) {
    $pdo->rollBack();
    json(['error' => 'You already have an appointment on this date.'], 409);
  }

  // check mechanic exists and active
  $mStmt = $pdo->prepare("SELECT id, name, is_active FROM mechanics WHERE id = :id LIMIT 1");
  $mStmt->execute([':id' => $mechanic_id]);
  $m = $mStmt->fetch();
  if (!$m || !$m['is_active']) {
    $pdo->rollBack();
    json(['error' => 'Selected mechanic is not available.'], 400);
  }

  // check if mechanic already booked in this slot
  $slotStmt = $pdo->prepare("
    SELECT COUNT(*) FROM appointments
    WHERE mechanic_id = :mid AND appointment_date = :d AND slot = :slot AND status = 'approved'
  ");
  $slotStmt->execute([':mid' => $mechanic_id, ':d' => $appointment_date, ':slot' => $slot]);
  if ((int)$slotStmt->fetchColumn() > 0) {
    $pdo->rollBack();
    json(['error' => 'Mechanic already booked in this slot'], 409);
  }

  // insert appointment
  $ins = $pdo->prepare("
    INSERT INTO appointments (client_name, address, phone, car_license, car_engine, appointment_date, mechanic_id, slot, status)
    VALUES (:client_name, :address, :phone, :car_license, :car_engine, :appointment_date, :mechanic_id, :slot, 'approved')
  ");
  $ins->execute([
    ':client_name' => $client_name,
    ':address' => $address,
    ':phone' => $phone,
    ':car_license' => $car_license,
    ':car_engine' => $car_engine,
    ':appointment_date' => $appointment_date,
    ':mechanic_id' => $mechanic_id,
    ':slot' => $slot
  ]);

  // get last insert id safely
  $lastId = $pdo->lastInsertId();
  if ($lastId == 0) {
    $stmt = $pdo->query("SELECT LAST_INSERT_ID()");
    $lastId = $stmt->fetchColumn();
  }

  $pdo->commit();
  json(['message' => 'Appointment approved', 'appointment_id' => $lastId]);
} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  json(['error' => 'Server error. Please try again.'], 500);
}
