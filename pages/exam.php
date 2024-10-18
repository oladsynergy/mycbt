<?php
if (!is_logged_in() || $_SESSION['role'] !== 'student') {
    redirect('index.php?page=dashboard');
}

$exam_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch exam details
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();

if (!$exam) {
    redirect('index.php?page=dashboard');
}

// Check if the student has an ongoing exam
$stmt = $pdo->prepare("SELECT * FROM exam_progress WHERE user_id = ? AND exam_id = ?");
$stmt->execute([$_SESSION['user_id'], $exam_id]);
$exam_progress = $stmt->fetch();

if (!$exam_progress) {
    // Fetch questions and randomize their order
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY RAND()");
    $stmt->execute([$exam_id]);
    $questions = $stmt->fetchAll();

    // Save the randomized question order and create exam progress
    $question_order = array_column($questions, 'id');
    $stmt = $pdo->prepare("INSERT INTO exam_progress (user_id, exam_id, question_order, current_question, start_time) VALUES (?, ?, ?, 0, NOW())");
    $stmt->execute([$_SESSION['user_id'], $exam_id, json_encode($question_order)]);
    $progress_id = $pdo->lastInsertId();
} else {
    $progress_id = $exam_progress['id'];
    $question_order = json_decode($exam_progress['question_order']);
    $current_question = $exam_progress['current_question'];

    // Fetch questions in the saved order
    $placeholders = implode(',', array_fill(0, count($question_order), '?'));
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id IN ($placeholders) ORDER BY FIELD(id, $placeholders)");
    $stmt->execute(array_merge($question_order, $question_order));
    $questions = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_progress'])) {
        // Save current progress
        $answers = isset($_POST['answers']) ? $_POST['answers'] : [];
        $reviews = isset($_POST['review']) ? $_POST['review'] : [];
        
        $stmt = $pdo->prepare("UPDATE exam_progress SET answers = ?, reviews = ?, current_question = ? WHERE id = ?");
        $stmt->execute([json_encode($answers), json_encode($reviews), $_POST['current_question'], $progress_id]);
        
        redirect("index.php?page=dashboard");
    } elseif (isset($_POST['submit_exam'])) {
        // Calculate score and submit exam
        $score = 0;
        $total_questions = count($questions);
        $answers = $_POST['answers'];

        foreach ($questions as $question) {
            if (isset($answers[$question['id']]) && $answers[$question['id']] === $question['correct_answer']) {
                $score++;
            }
        }

        $percentage_score = ($score / $total_questions) * 100;

        // Save exam result
        $stmt = $pdo->prepare("INSERT INTO exam_results (user_id, exam_id, score, date_taken) VALUES (?, ?, ?, NOW())");
        if ($stmt->execute([$_SESSION['user_id'], $exam_id, $percentage_score])) {
            // Delete exam progress
            $stmt = $pdo->prepare("DELETE FROM exam_progress WHERE id = ?");
            $stmt->execute([$progress_id]);
            
            redirect("index.php?page=results&exam_id=$exam_id");
        } else {
            $error = "Failed to save exam results. Please try again.";
        }
    }
}

// Load saved answers and reviews
$saved_answers = isset($exam_progress['answers']) ? json_decode($exam_progress['answers'], true) : [];
$saved_reviews = isset($exam_progress['reviews']) ? json_decode($exam_progress['reviews'], true) : [];
?>

<h2><?php echo htmlspecialchars($exam['title']); ?></h2>
<p>Duration: <?php echo htmlspecialchars($exam['duration']); ?> minutes</p>

<?php if (isset($error)) echo "<p class='error'>".htmlspecialchars($error)."</p>"; ?>

<form method="POST" action="" id="exam-form">
    <div id="progress-bar">
        <div id="progress-indicator"></div>
    </div>
    <?php foreach ($questions as $index => $question): ?>
        <div class="question" id="question-<?php echo $index; ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
            <h3>Question <?php echo $index + 1; ?> of <?php echo count($questions); ?></h3>
            <p><?php echo htmlspecialchars($question['question']); ?></p>
            <div class="options">
                <?php
                $options = ['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'];
                foreach ($options as $letter => $option):
                ?>
                    <label>
                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="<?php echo $letter; ?>" <?php echo (isset($saved_answers[$question['id']]) && $saved_answers[$question['id']] === $letter) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($question[$option]); ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="question-footer">
                <label>
                    <input type="checkbox" name="review[<?php echo $question['id']; ?>]" value="1" <?php echo (isset($saved_reviews[$question['id']]) && $saved_reviews[$question['id']] === "1") ? 'checked' : ''; ?>>
                    Mark for review
                </label>
                <?php if ($index > 0): ?>
                    <button type="button" class="nav-btn" onclick="showQuestion(<?php echo $index - 1; ?>)">Previous</button>
                <?php endif; ?>
                <?php if ($index < count($questions) - 1): ?>
                    <button type="button" class="nav-btn" onclick="showQuestion(<?php echo $index + 1; ?>)">Next</button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <input type="hidden" name="current_question" id="current-question" value="0">
    <div class="form-actions">
        <input type="submit" name="save_progress" value="Save and Exit" class="save-btn">
        <input type="submit" name="submit_exam" value="Submit Exam" class="submit-btn">
    </div>
</form>

<div id="timer"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var examForm = document.getElementById('exam-form');
    var duration = <?php echo $exam['duration']; ?> * 60; // Convert minutes to seconds
    var timer = duration;
    var timerDisplay = document.getElementById('timer');
    var currentQuestion = <?php echo isset($current_question) ? $current_question : 0; ?>;
    var totalQuestions = <?php echo count($questions); ?>;
    var progressIndicator = document.getElementById('progress-indicator');

    function updateTimer() {
        var minutes = Math.floor(timer / 60);
        var seconds = timer % 60;
        timerDisplay.textContent = 'Time remaining: ' + 
            (minutes < 10 ? '0' : '') + minutes + ':' + 
            (seconds < 10 ? '0' : '') + seconds;

        if (timer > 0) {
            timer--;
            setTimeout(updateTimer, 1000);
        } else {
            alert('Time is up! Your exam will be submitted automatically.');
            document.querySelector('input[name="submit_exam"]').click();
        }
    }

    updateTimer();

    // Prevent accidental navigation away from the exam
    window.onbeforeunload = function() {
        return "Are you sure you want to leave the exam? Your progress will be lost if you haven't saved.";
    };

    // Remove the warning when the form is submitted
    examForm.onsubmit = function() {
        window.onbeforeunload = null;
    };

    // Update progress bar
    function updateProgress() {
        var progress = (currentQuestion + 1) / totalQuestions * 100;
        progressIndicator.style.width = progress + '%';
    }

    // Show question function
    window.showQuestion = function(index) {
        document.getElementById('question-' + currentQuestion).style.display = 'none';
        document.getElementById('question-' + index).style.display = 'block';
        currentQuestion = index;
        document.getElementById('current-question').value = currentQuestion;
        updateProgress();
    };

    // Initialize progress bar
    updateProgress();
});
</script>