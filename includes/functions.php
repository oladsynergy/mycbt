<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect($location) {
    header("Location: $location");
    exit;
}

function get_question_options($question_type, $options) {
    if ($question_type === 'true_false') {
        return ['True', 'False'];
    }
    return $options;
}

function parse_question_file($file_path) {
    $questions = [];
    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $current_question = null;

    foreach ($lines as $line) {
        if (strpos($line, 'Type:') === 0) {
            if ($current_question) {
                $questions[] = $current_question;
            }
            $current_question = [
                'type' => trim(substr($line, 5)),
                'question' => '',
                'options' => [],
                'correct_answer' => '',
                'points' => 1
            ];
        } elseif (strpos($line, 'Question:') === 0) {
            $current_question['question'] = trim(substr($line, 9));
        } elseif (strpos($line, 'Options:') === 0) {
            $options_str = trim(substr($line, 8));
            $current_question['options'] = array_map('trim', explode('|', $options_str));
        } elseif (strpos($line, 'Answer:') === 0) {
            $current_question['correct_answer'] = trim(substr($line, 7));
        } elseif (strpos($line, 'Points:') === 0) {
            $current_question['points'] = intval(trim(substr($line, 7)));
        }
    }

    if ($current_question) {
        $questions[] = $current_question;
    }

    return $questions;
}

// Add more helper functions as needed