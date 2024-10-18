<?php
if (!is_logged_in()) {
    redirect('index.php?page=login');
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role == 'student') {
    // Fetch available exams for the student
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE id NOT IN (SELECT exam_id FROM exam_results WHERE user_id = ?)");
    $stmt->execute([$user_id]);
    $available_exams = $stmt->fetchAll();

    // Fetch past exam results
    $stmt = $pdo->prepare("SELECT er.*, e.title FROM exam_results er JOIN exams e ON er.exam_id = e.id WHERE er.user_id = ?");
    $stmt->execute([$user_id]);
    $past_results = $stmt->fetchAll();
} elseif ($role == 'teacher') {
    // Fetch exams created by the teacher
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE created_by = ?");
    $stmt->execute([$user_id]);
    $created_exams = $stmt->fetchAll();
}
?>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

    <?php if ($role == 'student'): ?>
        <div class="dashboard-section">
            <h3>Available Exams</h3>
            <?php if ($available_exams): ?>
                <ul class="exam-list">
                    <?php foreach ($available_exams as $exam): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($exam['title']); ?></strong>
                            <br>Duration: <?php echo $exam['duration']; ?> minutes
                            <div class="exam-actions">
                                <a href="index.php?page=exam&id=<?php echo $exam['id']; ?>" class="btn">Take Exam</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No available exams at the moment.</p>
            <?php endif; ?>
        </div>

        <div class="dashboard-section">
            <h3>Past Results</h3>
            <?php if ($past_results): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Score</th>
                            <th>Date Taken</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($past_results as $result): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result['title']); ?></td>
                                <td><?php echo number_format($result['score'], 2); ?>%</td>
                                <td><?php echo date('F j, Y, g:i a', strtotime($result['date_taken'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No past exam results.</p>
            <?php endif; ?>
        </div>

    <?php elseif ($role == 'teacher'): ?>
        <div class="dashboard-section">
            <h3>Manage Exams</h3>
            <p><a href="index.php?page=create_exam" class="btn">Create New Exam</a></p>
            <?php if ($created_exams): ?>
                <ul class="exam-list">
                    <?php foreach ($created_exams as $exam): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($exam['title']); ?></strong>
                            <br>Duration: <?php echo $exam['duration']; ?> minutes
                            <div class="exam-actions">
                                <a href="index.php?page=edit_exam&id=<?php echo $exam['id']; ?>">Edit</a>
                                <a href="index.php?page=view_results&id=<?php echo $exam['id']; ?>">View Results</a>
                                <a href="index.php?page=delete_exam&id=<?php echo $exam['id']; ?>" onclick="return confirm('Are you sure you want to delete this exam?');">Delete</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>You haven't created any exams yet.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>