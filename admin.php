<?php require_once 'config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin – Appointments</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family:system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:#f8fafc; color:#222; margin:0; }
    header { background:#111827; color:#fff; padding:14px 16px; }
    .wrap { max-width:1000px; margin:20px auto; padding:0 16px; }
    table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 4px 16px rgba(0,0,0,.06); }
    th, td { padding:10px 12px; border-bottom:1px solid #e5e7eb; text-align:left; }
    th { background:#f3f4f6; font-weight:600; }
    select, input[type="date"] { padding:6px 8px; border:1px solid #d1d5db; border-radius:6px; }
    button { padding:6px 10px; border:none; background:#2563eb; color:#fff; border-radius:6px; cursor:pointer; }
    .row { display:flex; gap:10px; margin:12px 0; }
    .notice { margin-top:12px; padding:10px; border-radius:8px; display:none; }
    .error { background:#ffe7e7; color:#b00020; }
    .ok { background:#e7fff0; color:#065f46; }
  </style>
</head>
<body>
  <header><h2>Admin – Appointment List</h2></header>
  <div class="wrap">
    <div class="row">
      <input type="date" id="filter_date">
      <button id="reload">Reload</button>
    </div>
    <div id="notice" class="notice"></div>
    <table id="appt_table">
      <thead>
        <tr>
          <th>ID</th><th>Client</th><th>Phone</th><th>Car Reg</th>
          <th>Date</th><th>Slot</th><th>Mechanic</th><th>Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <script>
    const tbody = document.querySelector('#appt_table tbody');
    const notice = document.getElementById('notice');

    function showError(msg){ notice.className='notice error'; notice.textContent=msg; notice.style.display='block'; }
    function showOk(msg){ notice.className='notice ok'; notice.textContent=msg; notice.style.display='block'; }

    async function load(dateFilter='') {
      tbody.innerHTML = '';
      try {
        const res = await fetch('admin_list.php');
        const data = await res.json();
        const rows = data.appointments.filter(a => !dateFilter || a.appointment_date === dateFilter);
        for (const a of rows) {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${a.id}</td>
            <td>${a.client_name}</td>
            <td>${a.phone}</td>
            <td>${a.car_license}</td>
            <td><input type="date" value="${a.appointment_date}" data-id="${a.id}" class="date"></td>
            <td>
              <select data-id="${a.id}" class="slot">
                <option value="9-11">9:00 – 11:00</option>
                <option value="11.30-1.30">11:30 – 1:30</option>
                <option value="2-4">2:00 – 4:00</option>
                <option value="4.30-6.30">4:30 – 6:30</option>
              </select>
            </td>
            <td><select data-id="${a.id}" class="mech"></select></td>
            <td><button data-id="${a.id}" class="save">Save</button></td>
          `;
          tbody.appendChild(tr);

          // set current slot
          tr.querySelector('.slot').value = a.slot;

          // load mechanics for that date
          const selDate = tr.querySelector('.date').value;
          const mechSel = tr.querySelector('.mech');
          const mRes = await fetch(`get_mechanics.php?date=${encodeURIComponent(selDate)}`);
          const mData = await mRes.json();
          mData.mechanics.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = `${m.name}`;
            mechSel.appendChild(opt);
          });
          mechSel.value = a.mechanic_id; // set current mechanic
        }

        tbody.addEventListener('click', async (e) => {
          if (e.target.classList.contains('save')) {
            const id = e.target.dataset.id;
            const row = e.target.closest('tr');
            const newDate = row.querySelector('.date').value;
            const newMech = row.querySelector('.mech').value;
            const newSlot = row.querySelector('.slot').value;
            try {
              const res = await fetch('admin_update.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ appointment_id: id, appointment_date: newDate, mechanic_id: newMech, slot: newSlot }).toString()
              });
              const j = await res.json();
              if (res.ok) showOk('Appointment updated'); else showError(j.error || 'Update failed');
              load(document.getElementById('filter_date').value);
            } catch (err) { showError('Network error'); }
          }
        });

      } catch (err) {
        showError('Failed to load appointments.');
      }
    }

    document.getElementById('reload').addEventListener('click', () => {
      load(document.getElementById('filter_date').value);
    });

    load();
  </script>
</body>
</html>
