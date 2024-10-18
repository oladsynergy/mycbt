<?php
if (!is_logged_in() || $_SESSION['role'] !== 'teacher') {
    redirect('index.php?page=dashboard');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize_input($_POST['title']);
    $duration = intval($_POST['duration']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO exams (title, duration, created_by) VALUES (?, ?, ?)");
    if ($stmt->execute([$title, $duration, $user_id])) {
        $exam_id = $pdo->lastInsertId();
        $_SESSION['success'] = "Exam created successfully. You can now add questions.";
        redirect("index.php?page=edit_exam&id=$exam_id");
    } else {
        $error = "Failed to create exam. Please try again.";
    }
}
?>

<h2>Create New Exam</h2>
<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
<form method="POST" action="">
    <div>
        <label for="title">Exam Title:</label>
        <input type="text" id="title" name="title" required>
    </div>
    <div>
        <label for="duration">Duration (minutes):</label>
        <input type="number" id="duration" name="duration" min="1" required>
    </div>
    <div>
        <input type="submit" value="Create Exam">
    </div>
</form>