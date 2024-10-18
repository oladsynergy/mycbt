<?php
if (!is_logged_in() || $_SESSION['role'] !== 'teacher') {
    redirect('index.php?page=dashboard');
}

$question_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch question details
$stmt = $pdo->prepare("SELECT q.*, e.created_by, e.id AS exam_id FROM questions q JOIN exams e ON q.exam_id = e.id WHERE q.id = ?");
$stmt->execute([$question_id]);
$question = $stmt->fetch();

if (!$question || $question['created_by'] != $_SESSION['user_id']) {
    redirect('index.php?page=dashboard');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    if ($stmt->execute([$question_id])) {
        $_SESSION['success'] = "Question deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete question. Please try again.";
    }
    redirect("index.php?page=edit_exam&id=" . $question['exam_id']);
}
?>

<h2>Delete Question</h2>
<p>Are you sure you want to delete this question?</p>
<p><strong>Question:</strong> <?php echo htmlspecialchars($question['question']); ?></p>

<form method="POST" action="">
    <input type="submit" value="Delete Question" onclick="return confirm('Are you sure you want to delete this question? This action cannot be undone.');">
</form>

<a href="index.php?page=edit_exam&id=<?php echo $question['exam_id']; ?>">Cancel and go back to Exam</a>