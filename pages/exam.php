<?php
// ... (previous code remains unchanged)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_exam'])) {
        // Calculate score and submit exam
        $score = 0;
        $total_questions = count($questions);
        $answers = $_POST['answers'];

        foreach ($questions as $question) {
            $user_answer = isset($answers[$question['id']]) ? $answers[$question['id']] : '';
            $correct_answer = $question['correct_answer'];

            // For MCQ, compare the selected option (A, B, C, D) with the correct answer
            if ($question['question_type'] === 'mcq') {
                if ($user_answer === $correct_answer) {
                    $score += $question['points'];
                }
            } 
            // For True/False, compare the selected option (True or False) with the correct answer
            elseif ($question['question_type'] === 'true_false') {
                if (strtolower($user_answer) === strtolower($correct_answer)) {
                    $score += $question['points'];
                }
            }
            // Add more conditions for other question types as needed
        }

        $total_points = array_sum(array_column($questions, 'points'));
        $percentage_score = ($score / $total_points) * 100;

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

// ... (rest of the code remains unchanged)
?>

<!-- ... (previous HTML code remains unchanged) -->

<form method="POST" action="" id="exam-form">
    <!-- ... (previous form content remains unchanged) -->
    <?php foreach ($questions as $index => $question): ?>
        <div class="question" id="question-<?php echo $index; ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
            <h3>Question <?php echo $index + 1; ?> of <?php echo count($questions); ?></h3>
            <p><?php echo htmlspecialchars($question['question']); ?></p>
            <div class="options">
                <?php
                $options = json_decode($question['options'], true);
                if ($question['question_type'] === 'mcq'):
                    foreach ($options as $letter => $option):
                ?>
                    <label>
                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="<?php echo $letter; ?>" <?php echo (isset($saved_answers[$question['id']]) && $saved_answers[$question['id']] === $letter) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($option); ?>
                    </label>
                <?php 
                    endforeach;
                elseif ($question['question_type'] === 'true_false'):
                    foreach ($options as $option):
                ?>
                    <label>
                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="<?php echo $option; ?>" <?php echo (isset($saved_answers[$question['id']]) && $saved_answers[$question['id']] === $option) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($option); ?>
                    </label>
                <?php
                    endforeach;
                endif;
                ?>
            </div>
            <!-- ... (rest of the question HTML remains unchanged) -->
        </div>
    <?php endforeach; ?>
    <!-- ... (rest of the form HTML remains unchanged) -->
</form>

<!-- ... (rest of the HTML and JavaScript remains unchanged) -->