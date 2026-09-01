<?php
include 'connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $hashed_password = md5($password);

        try {
            $stmt = $db_staff_pdo->prepare('INSERT INTO staff_info (staff_id, staff_pass) VALUES (:user, :pass)');
            $stmt->execute([
                ':user' => $username,
                ':pass' => $hashed_password
            ]);

            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            $message = "alert('Error: Username might already exist.'); window.location.href='register.php';";
            echo $message;
        }
    } else {
        $message = "alert('Please fill in all fields.'); window.location.href='register.php';";
        echo $message;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style/register.css">
</head>

<body>
    <div class="login-page">
        <h1>Admin Registration</h1>
        <form action="register.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Register">
        </form>
        <p><a href="login.php">Back to Login</a></p>
    </div>
</body>

</html>