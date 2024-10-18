<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'student'; // Default role

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$username, $email, $password, $role])) {
        $_SESSION['success'] = "Registration successful. Please log in.";
        redirect('index.php?page=login');
    } else {
        $error = "Registration failed. Please try again.";
    }
}
?>

<h2>Register</h2>
<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
<form method="POST" action="">
    <div>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <input type="submit" value="Register">
    </div>
</form>