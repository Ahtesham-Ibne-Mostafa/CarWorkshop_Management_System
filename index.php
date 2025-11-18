<?php require_once 'config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Car Workshop – Book Appointment</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --primary:#0d6efd; --bg:#f7f9fc; --card:#fff; --text:#222; --muted:#666; }
    body { margin:0; font-family:system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:var(--bg); color:var(--text); }
    header { background:var(--primary); color:#fff; padding:16px 20px; }
    .wrap { max-width:860px; margin:24px auto; padding:0 16px; }
    .card { background:var(--card); border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,.06); padding:20px; }
    .grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    label { font-size:14px; color:var(--muted); display:block; margin-bottom:6px; }
    input, select { width:100%; padding:10px; border:1px solid #dfe3eb; border-radius:8px; font-size:14px; }
    .full { grid-column:1 / -1; }
    .row { display:flex; gap:10px; align-items:center; }
    button { background:var(--primary); color:#fff; border:none; padding:12px 16px; border-radius:8px; cursor:pointer; }
    .notice { margin-top:12px; padding:10px; border-radius:8px; display:none; }
    .error { background:#ffe7e7; color:#b00020; }
    .ok { background:#e7fff0; color:#065f46; }
    .small { font-size:12px; color:var(--muted); }
  </style>
</head>
<body>
  <header><h1>Car Workshop – Online Appointment</h1></header>
  <div class="wrap">
    <div class="card">
      <div class="grid">
        <div class="full">
          <label for="appointment_date">Appointment date</label>
          <input type="date" id="appointment_date" min="">
          <div class="small">Select a date to see available mechanics.</div>
        </div>

        <div class="full">
          <label for="mechanic">Mechanic</label>
          <select id="mechanic">
            <option value="">Select a mechanic</option>
          </select>
          <div class="small" id="slots_hint"></div>
        </div>

        <div class="full">
          <label for="slot">Time Slot</label>
          <select id="slot">
            <option value="">Select a slot</option>
            <option value="9-11">9:00 – 11:00</option>
            <option value="11.30-1.30">11:30 – 1:30</option>
            <option value="2-4">2:00 – 4:00</option>
            <option value="4.30-6.30">4:30 – 6:30</option>
          </select>
        </div>

        <div>
          <label for="client_name">Full name</label>
          <input type="text" id="client_name" placeholder="Your name">
        </div>
        <div>
          <label for="phone">Phone</label>
          <input type="tel" id="phone" placeholder="+8801XXXXXXXXX">
        </div>

        <div class="full">
          <label for="address">Address</label>
          <input type="text" id="address" placeholder="Street, city">
        </div>

        <div>
          <label for="car_license">Car license number</label>
          <input type="text" id="car_license" placeholder="e.g., Dhaka-XX-1234">
        </div>
        <div>
          <label for="car_engine">Car engine number</label>
          <input type="text" id="car_engine" placeholder="Engine ID">
        </div>

        <div class="full row">
          <button id="submitBtn">Book appointment</button>
          <span class="small">You cannot book more than one appointment on the same date.</span>
        </div>
      </div>
      <div id="notice" class="notice"></div>
    </div>
  </div>

  <script>
    const dateInput = document.getElementById('appointment_date');
    const mechanicSelect = document.getElementById('mechanic');
    const slotSelect = document.getElementById('slot');
    const slotsHint = document.getElementById('slots_hint');
    const notice = document.getElementById('notice');

    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;

    function showError(msg) {
      notice.className = 'notice error';
      notice.textContent = msg;
      notice.style.display = 'block';
    }
    function showOk(msg) {
      notice.className = 'notice ok';
      notice.textContent = msg;
      notice.style.display = 'block';
    }

    dateInput.addEventListener('change', async () => {
      mechanicSelect.innerHTML = '<option value="">Select a mechanic</option>';
      slotsHint.textContent = '';
      const d = dateInput.value;
      if (!d) return;
      try {
        const res = await fetch(`get_mechanics.php?date=${encodeURIComponent(d)}`);
        const data = await res.json();
        if (data.error) { showError(data.error); return; }
        data.mechanics.forEach(m => {
          const opt = document.createElement('option');
          opt.value = m.id;
          opt.textContent = `${m.name}`;
          mechanicSelect.appendChild(opt);
        });
      } catch (e) {
        showError('Failed to load mechanics.');
      }
    });

    document.getElementById('submitBtn').addEventListener('click', async () => {
      const payload = {
        client_name: document.getElementById('client_name').value.trim(),
        address: document.getElementById('address').value.trim(),
        phone: document.getElementById('phone').value.trim(),
        car_license: document.getElementById('car_license').value.trim(),
        car_engine: document.getElementById('car_engine').value.trim(),
        appointment_date: dateInput.value,
        mechanic_id: mechanicSelect.value,
        slot: slotSelect.value
      };

      if (!payload.client_name || !payload.address || !payload.phone || !payload.car_license || !payload.car_engine || !payload.appointment_date || !payload.mechanic_id || !payload.slot) {
        showError('Please fill all fields and select mechanic & slot.');
        return;
      }
      if (!/^\+?\d{6,15}$/.test(payload.phone)) {
        showError('Invalid phone number format.');
        return;
      }

      try {
        const res = await fetch('create_appointment.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: new URLSearchParams(payload).toString()
        });
        const data = await res.json();
        if (res.ok) {
          showOk(`Appointment confirmed. ID: ${data.appointment_id}`);
        } else {
          showError(data.error || 'Unable to create appointment.');
        }
      } catch (e) {
        showError('Network error. Please try again.');
      }
    });
  </script>
</body>
</html>
