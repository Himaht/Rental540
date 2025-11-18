<?php
include('db_connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Rental540 - Locations</title>
<style>
body {font-family:'Segoe UI',Arial,sans-serif;background:#f4f7fb;margin:0;padding:0;}
header {background:#073334;color:white;padding:20px 40px;display:flex;justify-content:space-between;align-items:center;}
header a {color:#66B2FF;text-decoration:none;font-weight:500;}
header a:hover {text-decoration:underline;}
.container {max-width:800px;margin:40px auto;padding:20px;background:white;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.2);}
table {width:100%;border-collapse:collapse;}
th,td {padding:12px;border-bottom:1px solid #ddd;text-align:left;}
th {background:#004080;color:white;}
tr:hover {background:#eef4ff;}
</style>
</head>
<body>
<header>
    <h1>Rental540 Locations</h1>
    <a href="index.php">Home</a>
</header>
<div class="container">
    <h2 style="text-align:center;color:#073334;">Our Branch Locations</h2>
    <table>
        <tr><th>Location Name</th><th>Phone Number</th></tr>
        <?php
        $sql = "SELECT LocationName, Phone FROM LOCATION ORDER BY LocationName";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>{$row['LocationName']}</td><td>{$row['Phone']}</td></tr>";
            }
        } else echo "<tr><td colspan='2'>No locations found.</td></tr>";
        ?>
    </table>
</div>
</body>
</html>

