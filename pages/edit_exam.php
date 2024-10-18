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
    $_SESSION['error'] = "Exam not found or you don't have permission to edit it.";
    redirect('index.php?page=dashboard');
}

// Fetch questions for this exam
$stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ?");
$stmt->execute([$exam_id]);
$questions = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_question'])) {
        $question_type = sanitize_input($_POST['question_type']);
        $question = sanitize_input($_POST['question']);
        $options = isset($_POST['options']) ? $_POST['options'] : [];
        $correct_answer = sanitize_input($_POST['correct_answer']);
        $points = intval($_POST['points']);

        // Prepare options based on question type
        if ($question_type === 'true_false') {
            $options = ['True', 'False'];
        }

        $stmt = $pdo->prepare("INSERT INTO questions (exam_id, question_type, question, options, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$exam_id, $question_type, $question, json_encode($options), $correct_answer, $points])) {
            $_SESSION['success'] = "Question added successfully.";
        } else {
            $_SESSION['error'] = "Failed to add question. Please try again.";
        }
        redirect("index.php?page=edit_exam&id=$exam_id");
    }
}
?>

<h2>Edit Exam: <?php echo htmlspecialchars($exam['title']); ?></h2>

<?php
if (isset($_SESSION['success'])) {
    echo "<p class='success'>" . $_SESSION['success'] . "</p>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<p class='error'>" . $_SESSION['error'] . "</p>";
    unset($_SESSION['error']);
}
?>

<h3>Exam Details</h3>
<p>Duration: <?php echo $exam['duration']; ?> minutes</p>

<h3>Questions</h3>
<?php if (empty($questions)): ?>
    <p>No questions added yet.</p>
<?php else: ?>
    <ul>
    <?php foreach ($questions as $question): ?>
        <li>
            <?php echo htmlspecialchars($question['question']); ?>
            <a href="index.php?page=edit_question&id=<?php echo $question['id']; ?>">Edit</a>
            <a href="index.php?page=delete_question&id=<?php echo $question['id']; ?>" onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h3>Add New Question</h3>
<form method="POST" action="" id="add-question-form">
    <div>
        <label for="question_type">Question Type:</label>
        <select id="question_type" name="question_type" required>
            <option value="mcq">Multiple Choice</option>
            <option value="true_false">True/False</option>
            <!-- Add more question types as needed -->
        </select>
    </div>
    <div>
        <label for="question">Question:</label>
        <textarea id="question" name="question" required></textarea>
    </div>
    <div id="options-container">
        <label>Options:</label>
        <div id="mcq-options">
            <input type="text" name="options[]" placeholder="Option A" required>
            <input type="text" name="options[]" placeholder="Option B" required>
            <input type="text" name="options[]" placeholder="Option C" required>
            <input type="text" name="options[]" placeholder="Option D" required>
        </div>
        <div id="true-false-options" style="display: none;">
            <input type="text" name="options[]" value="True" readonly>
            <input type="text" name="options[]" value="False" readonly>
        </div>
    </div>
    <div>
        <label for="correct_answer">Correct Answer:</label>
        <input type="text" id="correct_answer" name="correct_answer" required>
    </div>
    <div>
        <label for="points">Points:</label>
        <input type="number" id="points" name="points" value="1" min="1" required>
    </div>
    <div>
        <input type="submit" name="add_question" value="Add Question">
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const questionTypeSelect = document.getElementById('question_type');
    const optionsContainer = document.getElementById('options-container');
    const mcqOptions = document.getElementById('mcq-options');
    const trueFalseOptions = document.getElementById('true-false-options');
    const correctAnswerInput = document.getElementById('correct_answer');

    questionTypeSelect.addEventListener('change', function() {
        if (this.value === 'true_false') {
            mcqOptions.style.display = 'none';
            trueFalseOptions.style.display = 'block';
            correctAnswerInput.placeholder = 'Enter True or False';
        } else {
            mcqOptions.style.display = 'block';
            trueFalseOptions.style.display = 'none';
            correctAnswerInput.placeholder = 'Enter the correct option (A, B, C, or D)';
        }
    });
});
</script>

<a href="index.php?page=dashboard">Back to Dashboard</a>