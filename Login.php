<?php
session_start();

// Check if the admin is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Hardcoded admin credentials
    $adminEmail = "admin@gmail.com";
    $adminPassword = "12345678";

    // Retrieve form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate credentials
    if ($email === $adminEmail && $password === $adminPassword) {
        // Mark admin as logged in
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="./css/form.css">
</head>

<body>
    <h1 style="text-align:center; margin-top:100px;margin-bottom:30px;font-size:2.5rem">Admin Login</h1>
    <form method="post" style="width: 300px;">
        <?php if (isset($error)) {?>
        <p style="color: red;"><?=$error?></p>
        <?php }?>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Login">
    </form>
</body>

</html>