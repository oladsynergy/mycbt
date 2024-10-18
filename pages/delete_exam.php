<?php
if (!is_logged_in() || $_SESSION['role'] !== 'teacher') {
    redirect('index.php?page=dashboard');
}

$exam_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch exam details
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ? AND created_by = ?");
$stmt->execute([$exam_id, $_SESSION['user_id']]);
$exam = $stmt->fetch();

if (!$exam) {
    $_SESSION['error'] = "Exam not found or you don't have permission to delete it.";
    redirect('index.php?page=dashboard');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // Delete associated exam progress
        $stmt = $pdo->prepare("DELETE FROM exam_progress WHERE exam_id = ?");
        $stmt->execute([$exam_id]);

        // Delete associated questions
        $stmt = $pdo->prepare("DELETE FROM questions WHERE exam_id = ?");
        $stmt->execute([$exam_id]);

        // Delete exam results
        $stmt = $pdo->prepare("DELETE FROM exam_results WHERE exam_id = ?");
        $stmt->execute([$exam_id]);

        // Delete the exam
        $stmt = $pdo->prepare("DELETE FROM exams WHERE id = ?");
        $stmt->execute([$exam_id]);

        $pdo->commit();
        $_SESSION['success'] = "Exam deleted successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Failed to delete exam. Please try again. Error: " . $e->getMessage();
    }
    redirect('index.php?page=dashboard');
}
?>

<h2>Delete Exam</h2>
<p>Are you sure you want to delete the exam "<?php echo htmlspecialchars($exam['title']); ?>"?</p>
<p>This action cannot be undone and will delete all associated questions, results, and progress.</p>

<form method="POST" action="">
    <input type="submit" value="Delete Exam" onclick="return confirm('Are you sure you want to delete this exam? This action cannot be undone.');">
</form>

<a href="index.php?page=dashboard">Cancel and go back to Dashboard</a>