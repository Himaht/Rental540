<?php
include('db_connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM MANAGEMENT_USERS WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['Password'])) {
                $_SESSION['UserID'] = $user['UserID'];
                $_SESSION['UserName'] = $user['FullName'];
                $_SESSION['UserRole'] = $user['Role'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "No account found with that email.";
        }
        $stmt->close();
    } else {
        $error = "Please enter both email and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manager Login</title>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: linear-gradient(120deg, #004080, #66B2FF);
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-container {
        background-color: white;
        padding: 40px 50px;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        width: 350px;
        text-align: center;
    }
    h2 { color: #004080; margin-bottom: 20px; }
    input {
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
    .register-link { margin-top: 15px; font-size: 14px; }
    .register-link a { color: #004080; text-decoration: none; }
    .register-link a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="login-container">
    <h2>Manager Login</h2>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" name="login">Login</button>
    </form>
    <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
    <div class="register-link">
        Don’t have an account? <a href="register.php">Create one here</a>
    </div>
</div>
</body>
</html>
