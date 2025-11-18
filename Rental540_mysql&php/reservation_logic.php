<?php
include('db_connect.php');


/****************************************************
 *  MAKE A RESERVATION
 ****************************************************/
if (isset($_POST['makeReservation'])) {

    $f = trim($_POST['FirstName']);
    $l = trim($_POST['LastName']);
    $dob = $_POST['DateOfBirth'];
    $dl = trim($_POST['DriverLicenseNumber']);
    $em = trim($_POST['Email']);
    $ph = preg_replace('/[^0-9]/', '', $_POST['Phone']);
    $pick = $_POST['PickupLocationID'];
    $ret = $_POST['ReturnLocationID'];
    $car = $_POST['CarType'];
    $start = $_POST['StartDate'];
    $end = $_POST['EndDate'];

    // Age validation (must be 21+)
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    if ($age < 21) {
        echo "<div class='flash-message error'>❌ You must be 21 or older to make a reservation.</div>";
        exit;
    }

    // Format phone number
    if (strlen($ph) == 10) {
        $ph = substr($ph, 0, 3) . '-' . substr($ph, 3, 3) . '-' . substr($ph, 6);
    } else {
        echo "<div class='flash-message error'>❌ Invalid phone number format. Use 10 digits.</div>";
        exit;
    }

    // Car mapping
    $carMap = [
        'Compact Car' => 'Compact',
        'Midsize Car' => 'Midsize',
        'Full Size Car' => 'Full Size',
        'Standard SUV' => 'SUV',
        'Luxury Car' => 'Luxury',
        'Minivan' => 'Minivan',
        'Pickup Truck' => 'Truck'
    ];
    $lookupName = $carMap[$car] ?? $car;

    // Customer check
    $c = $conn->prepare("SELECT CustomerID FROM CUSTOMER WHERE Email=?");
    $c->bind_param("s", $em);
    $c->execute();
    $c->store_result();
    if ($c->num_rows > 0) {
        $c->bind_result($cid);
        $c->fetch();
    } else {
        $add = $conn->prepare("INSERT INTO CUSTOMER (FirstName, LastName, DateOfBirth, DriverLicenseNumber, Email, Phone)
                               VALUES (?, ?, ?, ?, ?, ?)");
        $add->bind_param("ssssss", $f, $l, $dob, $dl, $em, $ph);
        $add->execute();
        $cid = $add->insert_id;
        $add->close();
    }
    $c->close();

    // Vehicle type ID
    $t = $conn->prepare("SELECT TypeID FROM VEHICLE_TYPE WHERE TypeName=?");
    $t->bind_param("s", $lookupName);
    $t->execute();
    $t->bind_result($tid);
    $t->fetch();
    $t->close();

    if (empty($tid)) {
        echo "<div class='flash-message error'>❌ Invalid vehicle type selected.</div>";
        exit;
    }

    // Insert reservation
    $r = $conn->prepare("INSERT INTO RESERVATION (CustomerID, PickupLocationID, ReturnLocationID, TypeID, StartDate, EndDate)
                         VALUES (?, ?, ?, ?, ?, ?)");
    $r->bind_param("iiiiss", $cid, $pick, $ret, $tid, $start, $end);

    if ($r->execute()) {
        $reservationID = $r->insert_id;

        echo "
        <div id='reservationSuccessModal' class='modal'>
            <div class='modal-content'>
                <span class='close-btn'>&times;</span>
                <h3>✅ Reservation Created Successfully!</h3>

                <p><strong>Reservation ID:</strong> $reservationID</p>
                <p><strong>Name:</strong> $f $l</p>
                <p><strong>Age:</strong> $age</p>
                <p><strong>Phone:</strong> $ph</p>
                <p><strong>Vehicle Type:</strong> $lookupName</p>
                <p><strong>Rental Period:</strong> $start to $end</p>

                <div class='modal-actions'>
                    <button id='homeBtn'>🏠 Go Home</button>
                </div>
            </div>
        </div>";
    } else {
        echo "<div class='flash-message error'>❌ Error saving reservation: " . htmlspecialchars($r->error) . "</div>";
    }

    $r->close();
    exit;
}



/****************************************************
 *  FIND RESERVATION (MODIFY MODE)
 ****************************************************/
if (isset($_POST['findReservation'])) {

    $reservationID = trim($_POST['ReservationID']);
    $email = trim($_POST['EmailVerify']);

    // Look up reservation & confirm email
    $sql = "
        SELECT 
            R.ReservationID,
            R.StartDate,
            R.EndDate,
            C.FirstName,
            C.LastName,
            C.Email,
            VT.TypeName,
            L1.LocationName AS Pickup,
            L2.LocationName AS ReturnLocation
        FROM RESERVATION R
        JOIN CUSTOMER C ON C.CustomerID = R.CustomerID
        JOIN VEHICLE_TYPE VT ON VT.TypeID = R.TypeID
        JOIN LOCATION L1 ON L1.LocationID = R.PickupLocationID
        JOIN LOCATION L2 ON L2.LocationID = R.ReturnLocationID
        WHERE R.ReservationID = ?
          AND C.Email = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $reservationID, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<div class='flash-message error'>❌ No reservation found. Make sure the Reservation ID and Email match.</div>";
        exit;
    }

    $row = $result->fetch_assoc();

    echo "
    <div id='reservationLookupModal' class='modal'>
        <div class='modal-content'>
            <span class='close-btn'>&times;</span>
            <h3>🔍 Reservation Details Found</h3>

            <p><strong>Reservation ID:</strong> {$row['ReservationID']}</p>
            <p><strong>Name:</strong> {$row['FirstName']} {$row['LastName']}</p>
            <p><strong>Email:</strong> {$row['Email']}</p>
            <p><strong>Vehicle Type:</strong> {$row['TypeName']}</p>
            <p><strong>Pickup Location:</strong> {$row['Pickup']}</p>
            <p><strong>Return Location:</strong> {$row['ReturnLocation']}</p>
            <p><strong>Rental Period:</strong> {$row['StartDate']} to {$row['EndDate']}</p>

            <div class='modal-actions'>
                <button onclick='window.location=\"index.php\"'>Close</button>
            </div>
        </div>
    </div>";

    exit;
}
?>
