<?php
include('db_connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$success = '';
$error = '';

if (isset($_POST['register'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = $_POST['role'];

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT * FROM MANAGEMENT_USERS WHERE Email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "This email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO MANAGEMENT_USERS (FullName, Email, Password, Role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fullname, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                $success = "Account created successfully! You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Account</title>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: linear-gradient(120deg, #004080, #66B2FF);
        height: 100vh;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .register-container {
        background-color: white;
        padding: 40px 50px;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        width: 400px;
        text-align: center;
    }
    h2 { color: #004080; margin-bottom: 20px; }
    input, select {
        width: 90%; padding: 10px; margin: 10px 0;
        border-radius: 6px; border: 1px solid #ccc; font-size: 16px;
    }
    button {
        background-color: #004080; color: white; border: none;
        padding: 10px 20px; border-radius: 6px; cursor: pointer;
        font-size: 16px; transition: background 0.3s;
    }
    button:hover { background-color: #0059B3; }
    .error { color: #e74c3c; margin-top: 10px; }
    .success { color: #2ecc71; margin-top: 10px; }
    .login-link { margin-top: 15px; font-size: 14px; }
    .login-link a { color: #004080; text-decoration: none; }
    .login-link a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="register-container">
    <h2>Create Account</h2>
    <form method="POST" action="">
        <input type="text" name="fullname" placeholder="Full Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="Manager">Manager</option>
            <option value="Finance Officer">Finance Officer</option>
        </select><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
        <button type="submit" name="register">Register</button>
    </form>
    <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
    <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>
</body>
</html>
