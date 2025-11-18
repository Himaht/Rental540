<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('db_connect.php');

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manager Dashboard - Rental540</title>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        margin: 0; padding: 0;
        background-color: #f8f9fb;
        color: #1A1A1A;
    }
    header {
        background: #073334;
        color: white;
        padding: 20px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    header h1 { color: #66B2FF; margin: 0; }
    header a { color: #66B2FF; text-decoration: none; }
    .container {
        max-width: 900px;
        margin: 50px auto;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.3);
        background-color: #ffffff;
    }
    button {
        background: #004080;
        color: white;
        border: none;
        padding: 10px 16px;
        margin: 5px;
        border-radius: 6px;
        font-size: 15px;
        cursor: pointer;
        transition: background 0.2s;
    }
    button:hover { background: #0059B3; }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: #1A2E4A;
        border-radius: 6px;
        overflow: hidden;
    }
    th, td {
        border-bottom: 1px solid #2C4C6E;
        padding: 12px;
        text-align: left;
        color: #EAEAEA;
    }
    th {
        background-color: #004080;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    tr:nth-child(even) { background: #203A5C; }
    tr:hover { background: #2C4C6E; }
    select {
        padding: 10px 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        margin-right: 10px;
    }
    h2 { color: #073334; text-align: center; margin-bottom: 10px; }
    .hidden { display: none; }
    .btn-group { text-align: center; margin-bottom: 20px; }
    .flash-message {
        text-align: center;
        padding: 12px;
        border-radius: 6px;
        margin-top: 10px;
    }
    .error { background: #f8d7da; color: #721c24; }
    .success { background: #d4edda; color: #155724; }
</style>
</head>
<body>

<header>
    <h1>Rental540 Manager Dashboard</h1>
    <div>
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['UserName']); ?> (<?php echo $_SESSION['UserRole']; ?>)</span> |
        <a href="logout.php">Logout</a>
    </div>
</header>

<div class="container">
    <h2>Revenue & Fleet Overview</h2>

    <div class="btn-group">
        <form id="filterForm">
            <select name="location" id="location">
                <option value="">-- All Locations --</option>
                <?php
                $locSql = "SELECT LocationID, LocationName FROM LOCATION ORDER BY LocationName";
                $locResult = $conn->query($locSql);
                if ($locResult->num_rows > 0) {
                    while ($loc = $locResult->fetch_assoc()) {
                        echo "<option value='{$loc['LocationID']}'>{$loc['LocationName']}</option>";
                    }
                }
                ?>
            </select>
        </form>
        <button onclick="showSection('revenueSection')">Revenue per Location</button>
        <button onclick="showSection('totalRevenueSection')">Total Revenue</button>
        <button onclick="showSection('availableCarsSection')">Cars Available per Location</button>
    </div>

    <!-- Revenue Section -->
    <div id="revenueSection" class="hidden">
        <?php
        $stmt = $conn->prepare("SELECT l.LocationID, l.LocationName, SUM(p.Amount) AS TotalRevenue 
                                FROM RENTAL r 
                                INNER JOIN PAYMENT p ON r.RentalID = p.RentalID 
                                INNER JOIN PICKUP_LOCATION pl ON r.RentalID = pl.RentalID 
                                INNER JOIN LOCATION l ON pl.LocationID = l.LocationID 
                                WHERE p.Status = 'Completed'
                                GROUP BY l.LocationID, l.LocationName");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "<table><tr><th>Location ID</th><th>Location Name</th><th>Total Revenue ($)</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>{$row['LocationID']}</td><td>{$row['LocationName']}</td><td>$" . number_format($row['TotalRevenue'], 2) . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='flash-message error'>No revenue data found.</div>";
        }
        $stmt->close();
        ?>
    </div>

    <!-- Total Revenue Section -->
    <div id="totalRevenueSection" class="hidden">
        <?php
        $sql = "SELECT SUM(p.Amount) AS TotalRevenue FROM PAYMENT p WHERE p.Status = 'Completed'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        if ($row['TotalRevenue'] !== null) {
            echo "<h3 style='text-align:center;'>Total Revenue: $" . number_format($row['TotalRevenue'], 2) . "</h3>";
        } else {
            echo "<div class='flash-message error'>No completed payments found.</div>";
        }
        ?>
    </div>

    <!-- Available Cars Section -->
    <div id="availableCarsSection" class="hidden">
        <?php
        $sql = "SELECT l.LocationName, COUNT(c.VIN) AS AvailableCars
                FROM CAR c
                JOIN LOCATION l ON c.HomeLocationID = l.LocationID
                WHERE c.Status = 'Available'
                GROUP BY l.LocationName
                ORDER BY AvailableCars DESC";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            echo "<table><tr><th>Location</th><th>Cars Available</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>{$row['LocationName']}</td><td>{$row['AvailableCars']}</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='flash-message error'>No available cars found.</div>";
        }
        ?>
    </div>
</div>

<script>
function showSection(sectionId) {
    const sections = document.querySelectorAll('.container > div');
    sections.forEach(sec => {
        if (sec.id && sec.id !== 'filterForm') {
            sec.classList.add('hidden');
        }
    });
    document.getElementById(sectionId).classList.remove('hidden');
}
</script>

</body>
</html>
