<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

// Manager session check
$isManagerLoggedIn = !empty($_SESSION['manager_logged_in']);

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Rental540 - Car Rental System</title>
<style>
/* --- BASE --- */
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    margin: 0;
    color: #1A1A1A;
    background: #fff url('dashboard-bg.png') no-repeat center top / cover;
}
h1, h2, h3 { margin: 0 0 12px 0; }

/* --- HEADER --- */
header {
    background: #073334;
    color: #fff;
    padding: 20px 40px;
    display: flex; align-items: center; justify-content: space-between;
}
header img { height: 60px; border-radius: 8px; }
.header-left { display: flex; gap: 15px; align-items: center; }
header h1 { color: #66B2FF; }
nav a {
    color: #66B2FF; text-decoration: none; margin: 0 10px; font-weight: 500;
}
nav a:hover { text-decoration: underline; }

.dark-mode-btn {
    background: #004080; color: #fff; border: none;
    padding: 10px 14px; border-radius: 6px; cursor: pointer;
}
.dark-mode-btn:hover { background: #0059B3; }

/* --- CONTAINERS --- */
.container, .reservation-section {
    max-width: 900px; margin: 40px auto; padding: 30px;
    background: #fff; border-radius: 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.3);
}

/* --- TABLE --- */
table { width: 100%; border-collapse: collapse; margin-top: 16px; background: #1A2E4A; border-radius: 8px; overflow: hidden; }
th, td { padding: 12px; border-bottom: 1px solid #2C4C6E; color: #EAEAEA; text-align: left; }
th { background: #004080; color: #fff; text-transform: uppercase; letter-spacing: 0.05em; }
tr:nth-child(even) { background: #203A5C; }
tr:hover { background: #2C4C6E; }

/* --- VEHICLE GALLERY --- */
.vehicle-gallery { display: none; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
.vehicle-cards { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
.vehicle-card {
    width: 200px; background: #fff; border-radius: 10px; overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: 0.3s;
}
.vehicle-card:hover { transform: translateY(-3px); }
.vehicle-image { height: 150px; background-size: cover; background-position: center; }
.vehicle-info { padding: 12px; text-align: center; }
.vehicle-info h3 { color: #073334; margin-bottom: 6px; }

/* --- RESERVATION SECTION --- */
.reservation-section { display: none; }
.reservation-choice { display: flex; justify-content: center; gap: 20px; margin: 20px 0 10px; }
.reservation-choice-btn {
    padding: 15px 22px; background: #004080; color: #fff;
    border: none; border-radius: 8px; cursor: pointer;
}
.reservation-choice-btn:hover { background: #0059B3; }
label { display: block; margin: 10px 0 6px; font-weight: 500; }
input, select {
    width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box;
    margin-bottom: 8px;
}
.submit-btn {
    background: #004080; color: #fff; border: none; padding: 12px 22px; border-radius: 6px; cursor: pointer;
}
.submit-btn:hover { background: #0059B3; }

/* --- MODAL (Shared) --- */
.modal {
    display: block; position: fixed; top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 9999;
}
.modal-content {
    background: #fff; color: #1a1a1a;
    border-radius: 10px;
    padding: 25px 30px;
    width: 400px; max-width: 90%;
    margin: 10% auto; text-align: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    animation: fadeIn 0.4s ease-in-out;
    position: relative;
}
.modal-content h3 { margin-bottom: 12px; }
.modal-content p { margin: 6px 0; font-size: 15px; }
.modal-actions button {
    background: #004080; color: #fff; border: none;
    padding: 10px 16px; border-radius: 6px; cursor: pointer;
    margin-top: 12px;
}
.modal-actions button:hover { background: #0059b3; }
.close-btn {
    position: absolute; right: 15px; top: 10px;
    font-size: 22px; font-weight: bold; color: #666; cursor: pointer;
}
.close-btn:hover { color: #000; }
@keyframes fadeIn {
    from {opacity: 0; transform: scale(0.9);}
    to {opacity: 1; transform: scale(1);}
}

/* --- DARK MODE --- */
.dark-mode { background: #1b1b1b; color: #f1f1f1; }
.dark-mode .container, .dark-mode .reservation-section, .dark-mode .vehicle-card { background: #2C2C2C; color: #f1f1f1; }
.dark-mode table { background: #333; }
.dark-mode th { background: #222; }
.dark-mode td { color: #eaeaea; }
</style>
</head>
<body>

<header>
    <div class="header-left">
        <img src="logo.png" alt="Company Logo" />
        <div>
            <h1>RENTAL540</h1>
            <nav>
                <a href="#" id="vehicles-link">Vehicles</a>
                <a href="#" id="reservations-link">Reservations</a>
                <a href="locations.php">Locations</a>
            </nav>
        </div>
    </div>
    <div>
        <?php if ($isManagerLoggedIn): ?>
            <span style="color:#66B2FF; margin-right: 12px;">Manager: <?= htmlspecialchars($_SESSION['manager_name'] ?? ''); ?></span>
            <a href="?logout=1" style="color:#66B2FF; margin-right: 12px;">Logout</a>
        <?php else: ?>
            <a href="login.php" style="color:#66B2FF; margin-right: 12px;">Manager Login</a>
        <?php endif; ?>
        <button class="dark-mode-btn" onclick="document.body.classList.toggle('dark-mode')">Dark Mode</button>
    </div>
</header>

<!-- VEHICLE GALLERY -->
<section class="vehicle-gallery" id="vehicle-gallery">
    <h2 style="text-align:center;">Our Vehicle Fleet</h2>
    <div class="vehicle-cards">
        <div class="vehicle-card"><div class="vehicle-image" style="background-image:url('compact-car.png')"></div><div class="vehicle-info"><h3>Compact</h3><p>Small fuel-efficient cars for city driving</p></div></div>
        <div class="vehicle-card"><div class="vehicle-image" style="background-image:url('standard-suv.png')"></div><div class="vehicle-info"><h3>SUV</h3><p>Extra space and capability</p></div></div>
        <div class="vehicle-card"><div class="vehicle-image" style="background-image:url('luxury-car.png')"></div><div class="vehicle-info"><h3>Luxury</h3><p>Premium comfort and features</p></div></div>
        <div class="vehicle-card"><div class="vehicle-image" style="background-image:url('minivan.png')"></div><div class="vehicle-info"><h3>Minivan</h3><p>Ample seating and storage</p></div></div>
        <div class="vehicle-card"><div class="vehicle-image" style="background-image:url('pickup-truck.png')"></div><div class="vehicle-info"><h3>Truck</h3><p>Hauling and towing power</p></div></div>
    </div>
</section>

<!-- RESERVATION SECTION -->
<section class="reservation-section" id="reservation-section">
    <h2>Reservation Management</h2>

    <div class="reservation-choice">
        <button class="reservation-choice-btn" id="make-res-btn">Make a Reservation</button>
        <button class="reservation-choice-btn" id="modify-res-btn">Modify a Reservation</button>
    </div>

    <!-- MAKE RESERVATION FORM -->
    <form class="reservation-form" id="make-reservation-form" method="POST">
        <input type="hidden" name="makeReservation" value="1">
        <h3>Make a Reservation</h3>
        <label>First Name</label><input type="text" name="FirstName" required />
        <label>Last Name</label><input type="text" name="LastName" required />
        <label>Date of Birth</label><input type="date" name="DateOfBirth" required />
        <label>Driver License Number</label><input type="text" name="DriverLicenseNumber" required />
        <label>Email</label><input type="email" name="Email" required />
        <label>Phone (10-digit)</label><input type="tel" name="Phone" required />

        <label>Pickup Location</label>
        <select name="PickupLocationID" required>
            <option value="">Select Pickup Location</option>
            <?php
            $locs = $conn->query("SELECT LocationID, LocationName FROM LOCATION ORDER BY LocationName");
            while ($l = $locs->fetch_assoc()) {
                echo "<option value='{$l['LocationID']}'>{$l['LocationName']}</option>";
            }
            ?>
        </select>

        <label>Return Location</label>
        <select name="ReturnLocationID" required>
            <option value="">Select Return Location</option>
            <?php
            $locs->data_seek(0);
            while ($l = $locs->fetch_assoc()) {
                echo "<option value='{$l['LocationID']}'>{$l['LocationName']}</option>";
            }
            ?>
        </select>

        <label>Car Type</label>
        <select name="CarType" required>
            <option value="">Select Car Type</option>
            <option>Compact Car</option><option>Midsize Car</option>
            <option>Full Size Car</option><option>Standard SUV</option>
            <option>Luxury Car</option><option>Minivan</option>
            <option>Pickup Truck</option>
        </select>

        <label>Start Date</label><input type="date" name="StartDate" required />
        <label>End Date</label><input type="date" name="EndDate" required />
        <button type="submit" class="submit-btn">Submit Reservation</button>
    </form>

    <!-- MODIFY RESERVATION FORM -->
    <form class="reservation-form" id="modify-reservation-form" method="POST">
        <input type="hidden" name="findReservation" value="1">
        <h3>Modify Reservation</h3>
        <label>Reservation ID</label><input type="text" name="ReservationID" required />
        <label>Email Address</label><input type="email" name="EmailVerify" required />
        <button type="submit" class="submit-btn">Find Reservation</button>
    </form>

    <div id="reservation-result"></div>
</section>


<!-- ✨ AGE VALIDATION MODAL (NEW) -->
<div id="ageErrorModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-btn" onclick="closeAgeModal()">&times;</span>
        <h3 style="color:#b30000;">❌ Age Restriction</h3>
        <p>You must be <strong>21 years or older</strong> to make a reservation.</p>
        <div class="modal-actions">
            <button onclick="closeAgeModal()">OK</button>
        </div>
    </div>
</div>

<script>
// --- Toggle Sections ---
document.getElementById('vehicles-link').addEventListener('click', e => {
    e.preventDefault();
    document.getElementById('vehicle-gallery').style.display = 'block';
    document.getElementById('reservation-section').style.display = 'none';
});
document.getElementById('reservations-link').addEventListener('click', e => {
    e.preventDefault();
    document.getElementById('vehicle-gallery').style.display = 'none';
    document.getElementById('reservation-section').style.display = 'block';
    document.querySelector('.reservation-choice').style.display = 'flex';
    document.getElementById('make-reservation-form').style.display = 'none';
    document.getElementById('modify-reservation-form').style.display = 'none';
});
document.getElementById('make-res-btn').addEventListener('click', () => {
    document.querySelector('.reservation-choice').style.display = 'none';
    document.getElementById('make-reservation-form').style.display = 'block';
});
document.getElementById('modify-res-btn').addEventListener('click', () => {
    document.querySelector('.reservation-choice').style.display = 'none';
    document.getElementById('modify-reservation-form').style.display = 'block';
});

// --- AJAX: Make Reservation ---
document.getElementById('make-reservation-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);

    const res = await fetch('reservation_logic.php', { method: 'POST', body: data });
    const html = await res.text();
    document.getElementById('reservation-result').innerHTML = html;

    const modal = document.getElementById('reservationSuccessModal');
    if (modal) {
        const closeBtn = modal.querySelector('.close-btn');
        const homeBtn = modal.querySelector('#homeBtn');
        if (closeBtn) closeBtn.addEventListener('click', () => modal.remove());
        if (homeBtn) homeBtn.addEventListener('click', () => window.location.href = 'index.php');
    }
});

// --- AJAX: Modify Reservation ---
document.getElementById('modify-reservation-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = e.target;
    const data = new FormData(form);

    const res = await fetch('reservation_logic.php', {
        method: 'POST',
        body: data
    });

    const html = await res.text();
    document.getElementById('reservation-result').innerHTML = html;

    const modal = document.getElementById('reservationLookupModal');
    if (modal) {
        const closeBtn = modal.querySelector('.close-btn');
        if (closeBtn) closeBtn.addEventListener('click', () => modal.remove());
    }
});


/* --------------------------------
   AGE VALIDATION LOGIC (NEW)
--------------------------------- */

function showAgeModal() {
    document.getElementById("ageErrorModal").style.display = "block";
}

function closeAgeModal() {
    document.getElementById("ageErrorModal").style.display = "none";
}

document.querySelector("input[name='DateOfBirth']").addEventListener("change", function () {
    const dob = new Date(this.value);
    const today = new Date();

    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();

    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
        age--;
    }

    if (age < 21) {
        showAgeModal();
        this.value = "";
    }
});
</script>

</body>
</html>
