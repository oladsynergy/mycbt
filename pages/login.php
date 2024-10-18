<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        redirect('index.php?page=dashboard');
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<h2>Login</h2>
<?php 
if (isset($_SESSION['success'])) {
    echo "<p class='success'>{$_SESSION['success']}</p>";
    unset($_SESSION['success']);
}
if (isset($error)) echo "<p class='error'>$error</p>"; 
?>
<form method="POST" action="">
    <div>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <input type="submit" value="Login">
    </div>
</form>