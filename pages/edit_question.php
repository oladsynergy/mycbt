<?php
if (!is_logged_in() || $_SESSION['role'] !== 'teacher') {
    redirect('index.php?page=dashboard');
}

$question_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch question details
$stmt = $pdo->prepare("SELECT q.*, e.created_by FROM questions q JOIN exams e ON q.exam_id = e.id WHERE q.id = ?");
$stmt->execute([$question_id]);
$question = $stmt->fetch();

if (!$question || $question['created_by'] != $_SESSION['user_id']) {
    redirect('index.php?page=dashboard');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_text = sanitize_input($_POST['question']);
    $option_a = sanitize_input($_POST['option_a']);
    $option_b = sanitize_input($_POST['option_b']);
    $option_c = sanitize_input($_POST['option_c']);
    $option_d = sanitize_input($_POST['option_d']);
    $correct_answer = sanitize_input($_POST['correct_answer']);

    $stmt = $pdo->prepare("UPDATE questions SET question = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_answer = ? WHERE id = ?");
    if ($stmt->execute([$question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer, $question_id])) {
        $success = "Question updated successfully.";
    } else {
        $error = "Failed to update question. Please try again.";
    }
}
?>

<h2>Edit Question</h2>
<?php 
if (isset($success)) echo "<p class='success'>$success</p>";
if (isset($error)) echo "<p class='error'>$error</p>";
?>
<form method="POST" action="">
    <div>
        <label for="question">Question:</label>
        <textarea id="question" name="question" required><?php echo htmlspecialchars($question['question']); ?></textarea>
    </div>
    <div>
        <label for="option_a">Option A:</label>
        <input type="text" id="option_a" name="option_a" value="<?php echo htmlspecialchars($question['option_a']); ?>" required>
    </div>
    <div>
        <label for="option_b">Option B:</label>
        <input type="text" id="option_b" name="option_b" value="<?php echo htmlspecialchars($question['option_b']); ?>" required>
    </div>
    <div>
        <label for="option_c">Option C:</label>
        <input type="text" id="option_c" name="option_c" value="<?php echo htmlspecialchars($question['option_c']); ?>" required>
    </div>
    <div>
        <label for="option_d">Option D:</label>
        <input type="text" id="option_d" name="option_d" value="<?php echo htmlspecialchars($question['option_d']); ?>" required>
    </div>
    <div>
        <label for="correct_answer">Correct Answer:</label>
        <select id="correct_answer" name="correct_answer" required>
            <option value="A" <?php echo $question['correct_answer'] == 'A' ? 'selected' : ''; ?>>A</option>
            <option value="B" <?php echo $question['correct_answer'] == 'B' ? 'selected' : ''; ?>>B</option>
            <option value="C" <?php echo $question['correct_answer'] == 'C' ? 'selected' : ''; ?>>C</option>
            <option value="D" <?php echo $question['correct_answer'] == 'D' ? 'selected' : ''; ?>>D</option>
        </select>
    </div>
    <div>
        <input type="submit" value="Update Question">
    </div>
</form>

<a href="index.php?page=edit_exam&id=<?php echo $question['exam_id']; ?>">Back to Exam</a>