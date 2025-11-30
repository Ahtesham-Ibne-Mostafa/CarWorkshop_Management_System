<?php
require_once 'config.php';
session_start();

$pdo = db();
$message = "";

// --- Handle Login ---
if (isset($_POST['login'])) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT id,password_hash FROM admins WHERE username=:u LIMIT 1");
    $stmt->execute([':u'=>$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password,$admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
    } else {
        $message = "⚠️ Invalid credentials.";
    }
}

// --- Handle Logout ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// --- Handle Appointment Update ---
if (isset($_POST['update_appt']) && isset($_SESSION['admin_id'])) {
    $id = (int)$_POST['appointment_id'];
    $newDate = sanitize($_POST['appointment_date']);
    $newSlot = sanitize($_POST['slot']);
    $newMech = (int)$_POST['mechanic_id'];

    try {
        $stmt = $pdo->prepare("UPDATE appointments 
                               SET appointment_date=:d, slot=:s, mechanic_id=:m 
                               WHERE id=:id");
        $stmt->execute([':d'=>$newDate, ':s'=>$newSlot, ':m'=>$newMech, ':id'=>$id]);
        $message = "✅ Appointment updated successfully.";
    } catch (Exception $e) {
        $message = "⚠️ Update failed.";
    }
}

// --- Handle Appointment Delete ---
if (isset($_POST['delete_appt']) && isset($_SESSION['admin_id'])) {
    $id = (int)$_POST['appointment_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id=:id");
        $stmt->execute([':id'=>$id]);
        $message = "🗑️ Appointment deleted successfully.";
    } catch (Exception $e) {
        $message = "⚠️ Delete failed.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  <style>

      .background-blur {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('image/admin.png') no-repeat center center fixed;
        background-size: cover;
        filter: blur(3px); /* subtle blur */
        z-index: -2;
      }
    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
    }

    .wrap {
      flex: 1; /* take up remaining space */
      max-width: 1100px;
      margin: 100px auto 24px;
      padding: 20px;
      background: rgba(255,255,255,0.95);
      border-radius: 10px;
      box-shadow: 0 4px 16px rgba(0,0,0,.2);
    }

    footer {
      text-align: center;
      padding: 16px;
      font-size: 14px;
      color: #fff;
      background: rgba(0,0,0,0.7);
      width: 100%;
    }


      body::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.3); /* dark overlay for readability */
        z-index: -1;
      }

      body {
        margin: 0;
        font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
        background: var(--bg);
        color: var(--text);
      }

      header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 70px; /* define header height */
        background: rgba(17,24,39,0.9);
        color: #fff;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 0;
        box-sizing: border-box;
        z-index: 1000;
      }



      /* Fix login centering */
      .centered {
        display: flex;
        justify-content: center;
        align-items: center;
        height: calc(100vh - 70px); /* subtract header height */
        margin-top: 70px; /* push below header */
      }



    h2,h3 { margin-top:0; }
    input, select { padding:8px; margin:4px 0; border:1px solid #ccc; border-radius:6px; width:100%; }
    button { padding:6px 12px; border:none; background:#2563eb; color:#fff; border-radius:6px; cursor:pointer; }
    button:hover { background:#1e4ed8; }
    .link-btn { background:#fff; color:#0d6efd; padding:6px 12px; border-radius:6px; text-decoration:none; font-weight:600; border:1px solid #0d6efd; }
    .notice { margin:12px 0; padding:10px; border-radius:8px; background:#e7fff0; color:#065f46; }
    .error { background:#ffe7e7; color:#b00020; }
    table { width:100%; border-collapse:collapse; margin-top:20px; }
    th, td { padding:10px; border-bottom:1px solid #ddd; text-align:left; }
    th { background:#f3f4f6; }
    .actions { display:flex; gap:8px; }
    .delete-btn { background:#dc2626; }
  </style>
</head>
<body>
<header>
  <h1>Speed Garage - Admin Panel</h1>
  <div>
    <?php if(!isset($_SESSION['admin_id'])): ?>
      <a href="index.php" class="link-btn">⬅ Back to Booking Page</a>
    <?php else: ?>
      <a href="admin.php?logout=1" class="link-btn">Logout</a>
    <?php endif; ?>
  </div>
</header>
<div class="background-blur"></div>

<?php if (!isset($_SESSION['admin_id'])): ?>
  <!-- Centered Login -->
  <div class="centered">
    <div class="wrap" style="max-width:400px;">
      <?php if ($message): ?>
        <div class="notice"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <h3>Admin Login</h3>
    <form method="POST" autocomplete="off">
        <!-- hidden dummy fields to trick autofill -->
        <input type="text" style="display:none">
        <input type="password" style="display:none">

        <input type="text" name="username" placeholder="Username" required autocomplete="new-username" value="">
        <input type="password" name="password" placeholder="Password" required autocomplete="new-password" value="">
        <button type="submit" name="login">Login</button>
      </form>


    </div>
  </div>

<?php else: ?>
  <!-- Appointment List -->
  <div class="wrap">
    <?php if ($message): ?>
      <div class="notice"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <h3>Appointments</h3>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Client</th><th>Address</th><th>Phone</th>
          <th>Car Reg</th><th>Car Engine</th><th>Date</th>
          <th>Slot</th><th>Mechanic</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $q = "SELECT a.id,a.client_name,a.address,a.phone,a.car_license,a.car_engine,
                     a.appointment_date,a.slot,a.status,
                     m.name AS mechanic_name,m.id AS mechanic_id
              FROM appointments a
              JOIN mechanics m ON m.id=a.mechanic_id
              ORDER BY a.appointment_date DESC";
        foreach ($pdo->query($q) as $a): ?>
          <tr>
            <form method="POST">
              <td><?= $a['id'] ?><input type="hidden" name="appointment_id" value="<?= $a['id'] ?>"></td>
              <td><?= htmlspecialchars($a['client_name']) ?></td>
              <td><?= htmlspecialchars($a['address']) ?></td>
              <td><?= htmlspecialchars($a['phone']) ?></td>
              <td><?= htmlspecialchars($a['car_license']) ?></td>
              <td><?= htmlspecialchars($a['car_engine']) ?></td>
              <td><input type="date" name="appointment_date" value="<?= $a['appointment_date'] ?>"></td>
              <td>
                <select name="slot">
                  <option value="9-11" <?= $a['slot']=='9-11'?'selected':'' ?>>9:00 – 11:00</option>
                  <option value="11.30-1.30" <?= $a['slot']=='11.30-1.30'?'selected':'' ?>>11:30 – 1:30</option>
                  <option value="2-4" <?= $a['slot']=='2-4'?'selected':'' ?>>2:00 – 4:00</option>
                  <option value="4.30-6.30" <?= $a['slot']=='4.30-6.30'?'selected':'' ?>>4:30 – 6:30</option>
                </select>
              </td>
              <td>
                <select name="mechanic_id">
                  <?php
                  $mechs = $pdo->query("SELECT id,name FROM mechanics WHERE is_active=1")->fetchAll();
                  foreach ($mechs as $m) {
                      $sel = $m['id']==$a['mechanic_id'] ? "selected" : "";
                      echo "<option value='{$m['id']}' $sel>".htmlspecialchars($m['name'])."</option>";
                  }
                  ?>
                </select>
              </td>
              <td><?= htmlspecialchars($a['status']) ?></td>
              <td>
                <div class="actions">
                  <button type="submit" name="update_appt">Save</button>
                  <button type="submit" name="delete_appt" class="delete-btn">Delete</button>
                </div>
              </td>
            </form>
          </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('input[name="username"], input[name="password"]').forEach(el => {
      el.value = ""; // force empty
    });
  });
</script>
<footer>
  <p>&copy; <?php echo date("Y"); ?> Ahtesham. All rights reserved.</p>
  <p>Last modified: <?php echo date("l, d F Y h:i A"); ?></p>
</footer>

</body>
</html>
