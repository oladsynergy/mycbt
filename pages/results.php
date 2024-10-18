<?php
if (!is_logged_in()) {
    redirect('index.php?page=login');
}

$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;

// Fetch exam details
$stmt = $pdo->prepare("SELECT e.*, er.score, er.date_taken 
                       FROM exams e 
                       JOIN exam_results er ON e.id = er.exam_id 
                       WHERE e.id = ? AND er.user_id = ?");
$stmt->execute([$exam_id, $_SESSION['user_id']]);
$result = $stmt->fetch();

if (!$result) {
    redirect('index.php?page=dashboard');
}
?>

<h2>Exam Results: <?php echo $result['title']; ?></h2>
<p>Date Taken: <?php echo $result['date_taken']; ?></p>
<p>Score: <?php echo number_format($result['score'], 2); ?>%</p>

<a href="index.php?page=dashboard">Back to Dashboard</a>