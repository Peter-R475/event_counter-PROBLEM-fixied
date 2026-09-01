<?php
session_start();
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, // Set expiration time to the past to delete it
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style\login_page.css">
</head>

<body>
    <div class="login-page">
        <span class="close-btn">&times;</span>
        <h1>Login Form</h1>
        <form action="verify.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <input type="submit" value="LOGIN">
        </form>
        <p class="signup-text">Not a member? <a href="register.php">Signup now</a></p>
    </div>
</body>

</html>