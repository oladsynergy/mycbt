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
    redirect('index.php?page=dashboard');
}

// Fetch exam results
$stmt = $pdo->prepare("SELECT er.*, u.username FROM exam_results er JOIN users u ON er.user_id = u.id WHERE er.exam_id = ? ORDER BY er.date_taken DESC");
$stmt->execute([$exam_id]);
$results = $stmt->fetchAll();
?>

<h2>Exam Results: <?php echo htmlspecialchars($exam['title']); ?></h2>

<?php if ($results): ?>
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Score</th>
                <th>Date Taken</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?php echo htmlspecialchars($result['username']); ?></td>
                    <td><?php echo number_format($result['score'], 2); ?>%</td>
                    <td><?php echo $result['date_taken']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No results available for this exam yet.</p>
<?php endif; ?>

<a href="index.php?page=dashboard">Back to Dashboard</a>